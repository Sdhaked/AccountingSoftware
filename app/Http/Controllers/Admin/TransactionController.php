<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\TransactionInvoiceMail;
use App\Models\AccountingTransaction;
use App\Models\AccountingTransactionAttachment;
use App\Models\AppSetting;
use App\Models\Company;
use App\Models\Customer;
use App\Models\Label;
use App\Models\Product;
use App\Models\Service;
use App\Services\TransactionReportExporter;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
        $filters = $this->validatedFilters($request);
        $baseQuery = AccountingTransaction::query()->filtered($filters);
        $filteredTransactionCount = (clone $baseQuery)->count();
        $hasActiveFilters = $this->hasActiveFilters($filters);
        $income = (clone $baseQuery)->where('type', 'income')->sum('total');
        $expense = (clone $baseQuery)->where('type', 'expense')->sum('total');
        $summary = [
            'income' => $income,
            'expense' => $expense,
            'profit' => max($income - $expense, 0),
            'loss' => max($expense - $income, 0),
        ];
        $transactions = $baseQuery->with(['company', 'items'])->latest('occurred_at')
            ->paginate(config('constants.pagination.per_page', 10))->withQueryString();
        $companies = Company::orderBy('name')->get();

        return view('admin.transactions.index', compact(
            'transactions',
            'summary',
            'companies',
            'filters',
            'filteredTransactionCount',
            'hasActiveFilters'
        ));
    }

    public function create(string $type)
    {
        abort_unless(in_array($type, ['income', 'expense'], true), 404);

        return view('admin.transactions.create', $this->formViewData($type));
    }

    public function edit(AccountingTransaction $transaction)
    {
        $transaction->load(['items', 'attachments']);

        return view('admin.transactions.create', $this->formViewData($transaction->type) + [
            'transaction' => $transaction,
        ]);
    }

    private function formViewData(string $type): array
    {
        return [
            'type' => $type,
            'customers' => Customer::with('company')->orderBy('name')->get(),
            'companies' => Company::orderBy('name')->get(),
            'products' => Product::with(['company', 'taxClass'])->orderBy('name')->get(),
            'services' => Service::with(['company', 'taxClass'])->orderBy('name')->get(),
            'labels' => Label::where('type', $type)->orderBy('name')->get(),
        ];
    }

    public function store(Request $request, string $type)
    {
        abort_unless(in_array($type, ['income', 'expense'], true), 404);
        $this->validateRequest($request, $type);
        $storedPaths = [];

        try {
            $transaction = DB::transaction(function () use ($request, $type, &$storedPaths) {
                $user = $request->user();
                $customer = null;
                $company = null;
                $sourceType = $request->string('source_type')->toString();

                if ($type === 'income' && $sourceType === 'company') {
                    $company = Company::findOrFail($request->integer('company_id'));
                    $customer = Customer::findOrFail($request->integer('customer_id'));
                    if ($customer->company_id !== $company->id) {
                        throw ValidationException::withMessages([
                            'customer_id' => 'Selected customer does not belong to this company.',
                        ]);
                    }
                } elseif ($sourceType === 'company') {
                    $company = Company::findOrFail($request->integer('company_id'));
                }

                $issuerName = $company?->name ?? $user->name;
                $issuerEmail = $company?->email ?? $user->email;
                $issuerAddress = $company?->address ?? $user->address;
                $transaction = AccountingTransaction::create([
                    'type' => $type,
                    'occurred_at' => $request->date('occurred_at'),
                    'source_type' => $sourceType,
                    'customer_id' => $customer?->id,
                    'company_id' => $company?->id,
                    'customer_name' => $customer?->name,
                    'customer_email' => $customer?->email,
                    'customer_address' => $customer?->billing_address,
                    'issuer_name' => $issuerName,
                    'issuer_email' => $issuerEmail,
                    'issuer_address' => $issuerAddress,
                    'subtotal' => 0,
                    'tax_total' => 0,
                    'total' => 0,
                    'notes' => $request->input('notes'),
                    'created_by' => $user->id,
                ]);

                $items = $type === 'income' && $sourceType === 'company'
                    ? $this->companyItems($request, $company)
                    : $this->labelItems($request, $type);

                foreach ($items as $item) {
                    $transaction->items()->create($item);
                }

                $subtotal = collect($items)->sum('subtotal');
                $taxTotal = collect($items)->sum('tax_amount');
                $transaction->update([
                    'reference_number' => sprintf('%s-%s-%04d', $type === 'income' ? 'INC' : 'EXP',
                        $transaction->occurred_at->format('Ymd'), $transaction->id),
                    'subtotal' => $subtotal,
                    'tax_total' => $taxTotal,
                    'total' => $subtotal + $taxTotal,
                ]);

                foreach ($request->file('documents', []) as $document) {
                    $path = $document->store('expense-documents/'.now()->format('Y/m'), 'public');
                    $storedPaths[] = $path;
                    $transaction->attachments()->create([
                        'disk' => 'public',
                        'path' => $path,
                        'original_name' => $document->getClientOriginalName(),
                        'mime_type' => $document->getMimeType(),
                        'size' => $document->getSize(),
                    ]);
                }

                return $transaction->fresh(['items', 'attachments']);
            });
        } catch (\Throwable $exception) {
            foreach ($storedPaths as $path) {
                Storage::disk('public')->delete($path);
            }
            throw $exception;
        }

        $shouldEmailInvoice = $type === 'income'
            && $transaction->source_type === 'company'
            && $request->boolean('send_invoice_email');

        if ($shouldEmailInvoice && ! filled($transaction->customer_email)) {
            return redirect()->route('admin.transactions.show', $transaction)
                ->with('warning', 'Income saved, but the selected customer does not have an email address.');
        }

        if ($shouldEmailInvoice) {
            try {
                $this->emailInvoice($transaction);
            } catch (\Throwable $exception) {
                report($exception);

                return redirect()->route('admin.transactions.show', $transaction)
                    ->with('warning', 'Income saved, but the invoice email could not be sent. You can download it here.');
            }
        }

        $message = ucfirst($type).' entry recorded successfully.';
        if ($shouldEmailInvoice) {
            $message .= ' The invoice was emailed to the customer.';
        }

        return redirect()->route('admin.transactions.show', $transaction)->with('success', $message);
    }

    public function update(Request $request, AccountingTransaction $transaction)
    {
        $type = $transaction->type;
        $this->validateRequest($request, $type);
        $storedPaths = [];
        $removedPaths = [];

        try {
            DB::transaction(function () use ($request, $transaction, $type, &$storedPaths, &$removedPaths) {
                $user = $request->user();
                $customer = null;
                $company = null;
                $sourceType = $request->string('source_type')->toString();

                if ($type === 'income' && $sourceType === 'company') {
                    $company = Company::findOrFail($request->integer('company_id'));
                    $customer = Customer::findOrFail($request->integer('customer_id'));
                    if ($customer->company_id !== $company->id) {
                        throw ValidationException::withMessages([
                            'customer_id' => 'Selected customer does not belong to this company.',
                        ]);
                    }
                } elseif ($sourceType === 'company') {
                    $company = Company::findOrFail($request->integer('company_id'));
                }

                $items = $type === 'income' && $sourceType === 'company'
                    ? $this->companyItems($request, $company)
                    : $this->labelItems($request, $type);
                $subtotal = collect($items)->sum('subtotal');
                $taxTotal = collect($items)->sum('tax_amount');

                $transaction->update([
                    'reference_number' => sprintf('%s-%s-%04d', $type === 'income' ? 'INC' : 'EXP',
                        $request->date('occurred_at')->format('Ymd'), $transaction->id),
                    'occurred_at' => $request->date('occurred_at'),
                    'source_type' => $sourceType,
                    'customer_id' => $customer?->id,
                    'company_id' => $company?->id,
                    'customer_name' => $customer?->name,
                    'customer_email' => $customer?->email,
                    'customer_address' => $customer?->billing_address,
                    'issuer_name' => $company?->name ?? $user->name,
                    'issuer_email' => $company?->email ?? $user->email,
                    'issuer_address' => $company?->address ?? $user->address,
                    'subtotal' => $subtotal,
                    'tax_total' => $taxTotal,
                    'total' => $subtotal + $taxTotal,
                    'notes' => $request->input('notes'),
                ]);

                $transaction->items()->delete();
                foreach ($items as $item) {
                    $transaction->items()->create($item);
                }

                $attachments = $transaction->attachments()
                    ->whereIn('id', $request->input('remove_attachments', []))->get();
                foreach ($attachments as $attachment) {
                    $removedPaths[] = [$attachment->disk, $attachment->path];
                    $attachment->delete();
                }

                foreach ($request->file('documents', []) as $document) {
                    $path = $document->store('expense-documents/'.now()->format('Y/m'), 'public');
                    $storedPaths[] = $path;
                    $transaction->attachments()->create([
                        'disk' => 'public',
                        'path' => $path,
                        'original_name' => $document->getClientOriginalName(),
                        'mime_type' => $document->getMimeType(),
                        'size' => $document->getSize(),
                    ]);
                }
            });
        } catch (\Throwable $exception) {
            foreach ($storedPaths as $path) {
                Storage::disk('public')->delete($path);
            }

            throw $exception;
        }

        foreach ($removedPaths as [$disk, $path]) {
            Storage::disk($disk)->delete($path);
        }

        return redirect()->route('admin.transactions.show', $transaction)
            ->with('success', ucfirst($type).' entry updated successfully.');
    }

    public function destroy(AccountingTransaction $transaction)
    {
        $paths = $transaction->attachments()->get(['disk', 'path']);
        $transaction->delete();

        foreach ($paths as $attachment) {
            Storage::disk($attachment->disk)->delete($attachment->path);
        }

        return redirect()->route('admin.transactions.index')
            ->with('success', 'Transaction deleted successfully.');
    }

    public function bulkDestroy(Request $request)
    {
        $filters = $this->validatedFilters($request);
        if (! $this->hasActiveFilters($filters)) {
            return redirect()->route('admin.transactions.index')
                ->with('error', 'Apply at least one filter before using bulk delete.');
        }

        $deleted = 0;
        AccountingTransaction::query()->filtered($filters)->with('attachments')
            ->chunkById(100, function ($transactions) use (&$deleted) {
                $paths = [];

                DB::transaction(function () use ($transactions, &$deleted, &$paths) {
                    foreach ($transactions as $transaction) {
                        foreach ($transaction->attachments as $attachment) {
                            $paths[] = [$attachment->disk, $attachment->path];
                        }

                        $transaction->delete();
                        $deleted++;
                    }
                });

                foreach ($paths as [$disk, $path]) {
                    Storage::disk($disk)->delete($path);
                }
            });

        return redirect()->route('admin.transactions.index', array_filter($filters, fn ($value) => filled($value)))
            ->with('success', "{$deleted} filtered transaction(s) permanently deleted.");
    }

    public function show(AccountingTransaction $transaction)
    {
        $transaction->load(['items', 'attachments', 'company']);

        return view('admin.transactions.show', compact('transaction'));
    }

    public function invoice(AccountingTransaction $transaction)
    {
        abort_unless($transaction->type === 'income', 404);
        $transaction->load(['items', 'company']);
        $brandLogo = $transaction->company?->logoDataUri()
            ?? AppSetting::query()->first()?->sponsorImageDataUri();

        return Pdf::loadView('admin.transactions.invoice', compact('transaction', 'brandLogo'))
            ->download("invoice-{$transaction->reference_number}.pdf");
    }

    public function sendInvoiceEmail(AccountingTransaction $transaction)
    {
        abort_unless($transaction->type === 'income' && $transaction->source_type === 'company', 404);

        if (! filled($transaction->customer_email)) {
            return back()->with('error', 'The selected customer does not have an email address.');
        }

        try {
            $transaction->load(['items', 'company']);
            $this->emailInvoice($transaction);
        } catch (\Throwable $exception) {
            report($exception);

            return back()->with('error', 'The invoice email could not be sent. Please check the SMTP settings and try again.');
        }

        return back()->with('success', "Invoice emailed successfully to {$transaction->customer_email}.");
    }

    public function attachment(AccountingTransactionAttachment $attachment)
    {
        abort_unless(Storage::disk($attachment->disk)->exists($attachment->path), 404);

        return Storage::disk($attachment->disk)->download($attachment->path, $attachment->original_name);
    }

    public function export(Request $request, TransactionReportExporter $exporter)
    {
        $filters = $this->validatedFilters($request);

        Log::info('Transaction Excel export requested', [
            'user_id' => $request->user()?->id,
            'filters' => array_filter($filters, fn ($value) => filled($value)),
            'php_version' => PHP_VERSION,
            'currency_symbol' => config('santrains.currency_symbol'),
        ]);

        try {
            $path = $exporter->export($filters);
        } catch (\Throwable $exception) {
            Log::error('Transaction Excel export failed', [
                'user_id' => $request->user()?->id,
                'filters' => array_filter($filters, fn ($value) => filled($value)),
                'exception' => $exception::class,
                'message' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
            ]);

            throw $exception;
        }

        Log::info('Transaction Excel export generated', [
            'user_id' => $request->user()?->id,
            'file' => basename($path),
            'bytes' => is_file($path) ? filesize($path) : null,
        ]);

        return response()->download($path, 'income-expenditure-'.now()->format('Y-m-d-His').'.xlsx')
            ->deleteFileAfterSend(true);
    }

    private function validateRequest(Request $request, string $type): void
    {
        $base = [
            'occurred_at' => ['required', 'date'],
            'notes' => ['nullable', 'string', 'max:5000'],
            'source_type' => ['required', 'in:company,personal'],
            'company_id' => ['nullable', 'required_if:source_type,company', 'integer', 'exists:companies,id'],
        ];

        if ($type === 'income') {
            $base += [
                'customer_id' => ['nullable', 'required_if:source_type,company', 'integer', 'exists:customers,id'],
                'send_invoice_email' => ['nullable', 'boolean'],
            ];
        } else {
            $base += [
                'documents' => ['nullable', 'array', 'max:10'],
                'documents.*' => ['file', 'max:10240', 'mimes:pdf,jpg,jpeg,png,webp,doc,docx,xls,xlsx'],
                'remove_attachments' => ['nullable', 'array'],
                'remove_attachments.*' => ['integer', 'exists:accounting_transaction_attachments,id'],
            ];
        }

        if ($type === 'income' && $request->input('source_type') === 'company') {
            $base += [
                'company_items' => ['required', 'array', 'min:1'],
                'company_items.*.kind' => ['required', 'in:product,service'],
                'company_items.*.source_id' => ['required', 'integer'],
                'company_items.*.quantity' => ['required', 'integer', 'min:1', 'max:999999999'],
            ];
        } else {
            $base += [
                'items' => ['required', 'array', 'min:1'],
                'items.*.label_id' => [
                    'required',
                    function (string $attribute, mixed $value, \Closure $fail) use ($type) {
                        if ((string) $value === 'other') {
                            return;
                        }

                        if (! Label::whereKey($value)->where('type', $type)->exists()) {
                            $fail('Select a valid account for this transaction type.');
                        }
                    },
                ],
                'items.*.other_label' => ['nullable', 'string', 'max:255'],
                'items.*.price' => ['required', 'numeric', 'min:0', 'max:999999999999.99'],
            ];
            if ($type === 'income') {
                $base['items.*.quantity'] = ['required', 'integer', 'min:1', 'max:999999999'];
            }
        }

        $request->validate($base);
    }

    private function validatedFilters(Request $request): array
    {
        $filters = $request->validate([
            'type' => ['nullable', 'in:income,expense'],
            'source' => ['nullable', 'in:company,personal'],
            'company_id' => ['nullable', 'integer', 'exists:companies,id'],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date'],
        ]);

        if (filled($filters['from'] ?? null)
            && filled($filters['to'] ?? null)
            && $filters['to'] < $filters['from']) {
            throw ValidationException::withMessages([
                'to' => 'The To Date must be on or after the From Date.',
            ]);
        }

        return $filters;
    }

    private function hasActiveFilters(array $filters): bool
    {
        return collect($filters)->contains(fn ($value) => filled($value));
    }

    private function companyItems(Request $request, Company $company): array
    {
        return collect($request->input('company_items'))->map(function (array $row) use ($company) {
            if ($row['kind'] === 'product') {
                $source = Product::with('taxClass')->where('company_id', $company->id)->findOrFail($row['source_id']);
                $price = (float) $source->price;
                $taxRate = (float) ($source->taxClass?->percentage ?? 0);
            } else {
                $source = Service::with('taxClass')->where('company_id', $company->id)->findOrFail($row['source_id']);
                $price = (float) $source->default_rate;
                $taxRate = (float) ($source->taxClass?->percentage ?? 0);
            }

            return $this->calculatedItem(
                $row['kind'], $source->name, (int) $row['quantity'], $price, $taxRate, $source->id
            );
        })->all();
    }

    private function labelItems(Request $request, string $type): array
    {
        return collect($request->input('items'))->map(function (array $row) use ($type) {
            if ((string) $row['label_id'] === 'other') {
                $name = trim((string) ($row['other_label'] ?? ''));
                if ($name === '') {
                    throw ValidationException::withMessages(['items' => 'Enter a name when Other is selected.']);
                }
                $label = Label::firstOrCreate(['type' => $type, 'name' => $name]);
                $itemType = 'other';
            } else {
                $label = Label::findOrFail((int) $row['label_id']);
                $itemType = 'label';
            }

            return $this->calculatedItem(
                $itemType,
                $label->name,
                $type === 'income' ? (int) $row['quantity'] : 1,
                (float) $row['price'],
                0,
                null,
                $label->id
            );
        })->all();
    }

    private function calculatedItem(
        string $type,
        string $label,
        int $quantity,
        float $price,
        float $taxRate,
        ?int $sourceId = null,
        ?int $labelId = null
    ): array {
        $subtotal = round($quantity * $price, 2);
        $taxAmount = round($subtotal * $taxRate / 100, 2);

        return [
            'item_type' => $type,
            'source_id' => $sourceId,
            'label_id' => $labelId,
            'label' => $label,
            'quantity' => $quantity,
            'unit_price' => $price,
            'tax_rate' => $taxRate,
            'subtotal' => $subtotal,
            'tax_amount' => $taxAmount,
            'total' => $subtotal + $taxAmount,
        ];
    }

    private function emailInvoice(AccountingTransaction $transaction): void
    {
        $setting = AppSetting::query()->first();
        $setting?->applyMailConfiguration();
        $transaction->loadMissing(['items', 'company']);
        $brandLogo = $transaction->company?->logoDataUri() ?? $setting?->sponsorImageDataUri();
        $pdf = Pdf::loadView('admin.transactions.invoice', compact('transaction', 'brandLogo'));
        Mail::to($transaction->customer_email)->send(
            new TransactionInvoiceMail($transaction, $pdf->output())
        );
    }
}
