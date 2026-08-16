<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AppSetting;
use App\Models\Certificate;
use App\Models\Company;
use App\Models\Customer;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CertificateController extends Controller
{
    public function index(Request $request)
    {
        $certificates = Certificate::with(['customer', 'company'])
            ->when($request->filled('search'), fn ($query) => $query
                ->where(fn ($subQuery) => $subQuery
                    ->where('certificate_number', 'like', '%'.$request->search.'%')
                    ->orWhereHas('customer', fn ($customer) => $customer->where('name', 'like', '%'.$request->search.'%'))))
            ->when($request->boolean('expired'), fn ($query) => $query->whereDate('expires_at', '<', today()))
            ->latest('id')
            ->paginate(config('constants.pagination.per_page', 10));

        return view('admin.certificates.index', compact('certificates'));
    }

    public function create()
    {
        $customers = Customer::with('company')->orderBy('name')->get();
        $companies = Company::orderBy('name')->get();

        return view('admin.certificates.create', compact('customers', 'companies'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => ['required', 'integer', 'exists:customers,id'],
            'company_id' => ['required', 'integer', 'exists:companies,id'],
            'issued_at' => ['required', 'date'],
            'expires_at' => ['required', 'date', 'after_or_equal:issued_at'],
        ]);

        $customer = Customer::findOrFail($validated['customer_id']);
        if ($customer->company_id !== (int) $validated['company_id']) {
            throw ValidationException::withMessages(['company_id' => 'Selected customer does not belong to this company.']);
        }

        DB::transaction(function () use ($validated) {
            $dailyNumber = Certificate::whereDate('issued_at', $validated['issued_at'])
                ->lockForUpdate()->count() + 1;
            $certificateNumber = date('Y-m-d', strtotime($validated['issued_at'])).'-'.$dailyNumber;

            Certificate::create($validated + ['certificate_number' => $certificateNumber]);
        });

        return redirect()->route('admin.certificates.index')->with('success', 'Certificate created successfully.');
    }

    public function show(Certificate $certificate)
    {
        $certificate->load(['customer', 'company']);

        return view('admin.certificates.show', compact('certificate'));
    }

    public function download(Certificate $certificate)
    {
        $certificate->load(['customer', 'company']);
        $sponsorImage = AppSetting::query()->first()?->sponsorImageDataUri();

        return Pdf::loadView('admin.certificates.pdf', compact('certificate', 'sponsorImage'))
            ->setPaper('a4', 'landscape')
            ->download("certificate-{$certificate->certificate_number}.pdf");
    }

    public function destroy(Certificate $certificate)
    {
        $certificate->delete();

        return back()->with('success', 'Certificate deleted successfully.');
    }

    public function destroyExpired(Request $request)
    {
        $request->validate(['confirmation' => ['required', 'in:yes delete all exp certificates']]);
        $deleted = Certificate::whereDate('expires_at', '<', today())->delete();

        return back()->with('success', "{$deleted} expired certificate(s) permanently deleted.");
    }
}
