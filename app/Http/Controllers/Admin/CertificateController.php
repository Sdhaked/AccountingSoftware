<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AppSetting;
use App\Models\Certificate;
use App\Models\Company;
use App\Models\Customer;
use App\Models\Service;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
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
        $courseNames = Service::query()->distinct()->orderBy('name')->pluck('name');

        return view('admin.certificates.create', compact('customers', 'companies', 'courseNames'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => ['required', 'integer', 'exists:customers,id'],
            'company_id' => ['required', 'integer', 'exists:companies,id'],
            'course_name' => ['nullable', 'string', 'max:255'],
            'instructor_name' => ['required', 'string', 'max:255'],
            'instructor_signature' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'issued_at' => ['required', 'date'],
            'expires_at' => ['required', 'date', 'after_or_equal:issued_at'],
        ]);

        $validated['course_name'] = trim((string) ($validated['course_name'] ?? ''))
            ?: config('santrains.default_course');
        $validated['instructor_name'] = trim((string) ($validated['instructor_name'] ?? ''))
            ?: config('santrains.instructor_name');

        $customer = Customer::findOrFail($validated['customer_id']);
        if ($customer->company_id !== (int) $validated['company_id']) {
            throw ValidationException::withMessages(['company_id' => 'Selected customer does not belong to this company.']);
        }

        $signaturePath = $request->file('instructor_signature')->store('certificate-signatures', 'public');
        unset($validated['instructor_signature']);
        $validated['instructor_signature_path'] = $signaturePath;

        try {
            DB::transaction(function () use ($validated) {
                $dailyNumber = Certificate::whereDate('issued_at', $validated['issued_at'])
                    ->lockForUpdate()->count() + 1;
                $certificateNumber = date('dmY', strtotime($validated['issued_at'])).$dailyNumber;

                Certificate::create($validated + ['certificate_number' => $certificateNumber]);
            });
        } catch (\Throwable $exception) {
            Storage::disk('public')->delete($signaturePath);

            throw $exception;
        }

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
        $brandLogo = $certificate->company?->logoDataUri()
            ?? AppSetting::query()->first()?->sponsorImageDataUri();
        $instructorSignature = $certificate->instructorSignatureDataUri();

        return Pdf::loadView('admin.certificates.pdf', compact('certificate', 'brandLogo', 'instructorSignature'))
            ->setPaper('a4', 'portrait')
            ->download("certificate-{$certificate->certificate_number}.pdf");
    }

    public function destroy(Certificate $certificate)
    {
        $signaturePath = $certificate->instructor_signature_path;
        $certificate->delete();
        if ($signaturePath) {
            Storage::disk('public')->delete($signaturePath);
        }

        return back()->with('success', 'Certificate deleted successfully.');
    }

    public function destroyExpired(Request $request)
    {
        $request->validate(['confirmation' => ['required', 'in:yes delete all exp certificates']]);
        $expiredCertificates = Certificate::whereDate('expires_at', '<', today())->get(['id', 'instructor_signature_path']);
        $deleted = $expiredCertificates->count();

        Certificate::whereKey($expiredCertificates->pluck('id'))->delete();
        $expiredCertificates
            ->pluck('instructor_signature_path')
            ->filter()
            ->each(fn (string $path) => Storage::disk('public')->delete($path));

        return back()->with('success', "{$deleted} expired certificate(s) permanently deleted.");
    }
}
