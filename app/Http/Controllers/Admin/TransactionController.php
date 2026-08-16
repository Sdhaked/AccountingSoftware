<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AccountingTransaction;
use App\Models\AccountingTransactionAttachment;
use App\Models\Company;
use App\Models\Customer;
use App\Models\Label;
use App\Models\Product;
use App\Models\Service;
use App\Services\TransactionReportExporter;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->only(['type', 'source', 'company_id', 'from', 'to']);
        $baseQuery = AccountingTransaction::query()->filtered($filters);
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

        return view('admin.transactions.index', compact('transactions', 'summary', 'companies'));
    }

    public function create(string $type)
    {
        abort_unless(in_array($type, ['income', 'expense'], true), 404);

        return view('admin.transactions.create', [
            'type' => $type,
            'customers' => Customer::with('company')->orderBy('name')->get(),
            'companies' => Company::orderBy('name')->get(),
            'products' => Product::with(['company', 'taxClass'])->orderBy('name')->get(),
            'services' => Service::with('company')->orderBy('name')->get(),
            'labels' => Label::orderBy('name')->get(),
        ]);
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
                $sourceType = 'personal';

                if ($type === 'income') {
                    $customer = Customer::findOrFail($request->integer('customer_id'));
                    $sourceType = $request->string('source_type')->toString();
                    if ($sourceType === 'company') {
                        $company = Company::findOrFail($request->integer('company_id'));
                        if ($customer->company_id !== $company->id) {
                            throw ValidationException::withMessages([
                                'company_id' => 'Selected customer does not belong to this company.',
                            ]);
                        }
                    }
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
                    $path = $document->store('expense-documents/' . now()->format('Y/m'), 'public');
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

        if ($type === 'income' && filled($transaction->customer_email)) {
            try {
                $this->emailInvoice($transaction);
            } catch (\Throwable $exception) {
                report($exception);

                return redirect()->route('admin.transactions.show', $transaction)
                    ->with('warning', 'Income saved, but the invoice email could not be sent. You can download it here.');
            }
        }

        return redirect()->route('admin.transactions.show', $transaction)
            ->with('success', ucfirst($type) . ' entry recorded successfully.');
    }

    public function show(AccountingTransaction $transaction)
    {
        $transaction->load(['items', 'attachments', 'company']);

        return view('admin.transactions.show', compact('transaction'));
    }

    public function invoice(AccountingTransaction $transaction)
    {
        abort_unless($transaction->type === 'income', 404);
        $transaction->load('items');

        return Pdf::loadView('admin.transactions.invoice', compact('transaction'))
            ->download("invoice-{$transaction->reference_number}.pdf");
    }

    public function attachment(AccountingTransactionAttachment $attachment)
    {
        abort_unless(Storage::disk($attachment->disk)->exists($attachment->path), 404);

        return Storage::disk($attachment->disk)->download($attachment->path, $attachment->original_name);
    }

    public function export(Request $request, TransactionReportExporter $exporter)
    {
        $path = $exporter->export($request->only(['type', 'source', 'company_id', 'from', 'to']));

        return response()->download($path, 'income-expenditure-' . now()->format('Y-m-d-His') . '.xlsx')
            ->deleteFileAfterSend(true);
    }

    private function validateRequest(Request $request, string $type): void
    {
        $base = [
            'occurred_at' => ['required', 'date'],
            'notes' => ['nullable', 'string', 'max:5000'],
        ];

        if ($type === 'income') {
            $base += [
                'customer_id' => ['required', 'integer', 'exists:customers,id'],
                'source_type' => ['required', 'in:company,personal'],
                'company_id' => ['nullable', 'required_if:source_type,company', 'integer', 'exists:companies,id'],
            ];
        } else {
            $base += [
                'documents' => ['nullable', 'array', 'max:10'],
                'documents.*' => ['file', 'max:10240', 'mimes:pdf,jpg,jpeg,png,webp,doc,docx,xls,xlsx'],
            ];
        }

        if ($type === 'income' && $request->input('source_type') === 'company') {
            $base += [
                'company_items' => ['required', 'array', 'min:1'],
                'company_items.*.kind' => ['required', 'in:product,service'],
                'company_items.*.source_id' => ['required', 'integer'],
                'company_items.*.quantity' => ['required', 'numeric', 'gt:0', 'max:999999999'],
            ];
        } else {
            $base += [
                'items' => ['required', 'array', 'min:1'],
                'items.*.label_id' => ['required'],
                'items.*.other_label' => ['nullable', 'string', 'max:255'],
                'items.*.price' => ['required', 'numeric', 'min:0', 'max:999999999999.99'],
            ];
            if ($type === 'income') {
                $base['items.*.quantity'] = ['required', 'numeric', 'gt:0', 'max:999999999'];
            }
        }

        $request->validate($base);
    }

    private function companyItems(Request $request, Company $company): array
    {
        return collect($request->input('company_items'))->map(function (array $row) use ($company) {
            if ($row['kind'] === 'product') {
                $source = Product::with('taxClass')->where('company_id', $company->id)->findOrFail($row['source_id']);
                $price = (float) $source->price;
                $taxRate = (float) $source->taxClass->percentage;
            } else {
                $source = Service::where('company_id', $company->id)->findOrFail($row['source_id']);
                $price = (float) $source->default_rate;
                $taxRate = 0;
            }

            return $this->calculatedItem($row['kind'], $source->name, (float) $row['quantity'], $price, $taxRate);
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
                $label = Label::firstOrCreate(['name' => $name]);
                $itemType = 'other';
            } else {
                $label = Label::findOrFail((int) $row['label_id']);
                $itemType = 'label';
            }

            return $this->calculatedItem(
                $itemType,
                $label->name,
                $type === 'income' ? (float) $row['quantity'] : 1,
                (float) $row['price'],
                0
            );
        })->all();
    }

    private function calculatedItem(string $type, string $label, float $quantity, float $price, float $taxRate): array
    {
        $subtotal = round($quantity * $price, 2);
        $taxAmount = round($subtotal * $taxRate / 100, 2);

        return [
            'item_type' => $type,
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
        $pdf = Pdf::loadView('admin.transactions.invoice', compact('transaction'));
        Mail::html(
            "<p>Dear " . e($transaction->customer_name) . ",</p><p>Please find invoice "
                . e($transaction->reference_number) . " attached.</p>",
            function ($message) use ($transaction, $pdf) {
                $message->to($transaction->customer_email)
                    ->subject("Invoice {$transaction->reference_number}")
                    ->attachData($pdf->output(), "invoice-{$transaction->reference_number}.pdf", [
                        'mime' => 'application/pdf',
                    ]);
            }
        );
    }
}
