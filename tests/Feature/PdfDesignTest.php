<?php

use App\Models\AccountingTransaction;
use App\Models\Certificate;
use App\Models\Company;
use App\Models\Customer;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
    $role = Role::where('slug', 'developer-admin')->firstOrFail();
    $this->user = User::factory()->create(['role' => $role->id]);
    $this->company = Company::create([
        'name' => 'San Trains Client',
        'address' => 'Dublin, Ireland',
        'phone' => '+353 89230 3761',
        'email' => 'info@santrains.com',
    ]);
    $this->customer = Customer::create([
        'company_id' => $this->company->id,
        'name' => 'Anto Jose',
        'phone' => '+353 800 0000',
        'email' => 'anto@example.com',
        'billing_address' => 'Staff Nurse',
    ]);
});

it('downloads the branded San Trains invoice as a PDF', function () {
    config(['santrains.currency_symbol' => '€']);
    Storage::fake('public');
    $logoContents = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGaWjR9awAAAABJRU5ErkJggg==');
    Storage::disk('public')->put('company-logos/client-logo.png', $logoContents);
    $this->company->update(['logo_path' => 'company-logos/client-logo.png']);

    $transaction = AccountingTransaction::create([
        'reference_number' => '010820261',
        'type' => 'income',
        'occurred_at' => '2026-08-01 10:00:00',
        'source_type' => 'company',
        'customer_id' => $this->customer->id,
        'company_id' => $this->company->id,
        'customer_name' => $this->customer->name,
        'customer_email' => $this->customer->email,
        'customer_address' => $this->customer->billing_address,
        'issuer_name' => $this->company->name,
        'issuer_email' => $this->company->email,
        'issuer_address' => $this->company->address,
        'subtotal' => 150,
        'tax_total' => 0,
        'total' => 150,
        'created_by' => $this->user->id,
    ]);
    $transaction->items()->create([
        'item_type' => 'service',
        'label' => 'Manual Handling & People Handling',
        'quantity' => 1,
        'unit_price' => 150,
        'tax_rate' => 0,
        'subtotal' => 150,
        'tax_amount' => 0,
        'total' => 150,
    ]);

    $html = view('admin.transactions.invoice', [
        'transaction' => $transaction->load('items'),
        'brandLogo' => $transaction->company->logoDataUri(),
    ])->render();

    expect($html)
        ->toContain('€150.00/-')
        ->toContain(base64_encode($logoContents));

    $response = $this->actingAs($this->user)->get(route('admin.transactions.invoice', $transaction));

    $response->assertOk()->assertDownload('invoice-010820261.pdf');
    expect(str_starts_with($response->getContent(), '%PDF-'))->toBeTrue();
});

it('stores certificate design fields and downloads the portrait PDF', function () {
    Storage::fake('public');
    $logoContents = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGaWjR9awAAAABJRU5ErkJggg==');
    Storage::disk('public')->put('company-logos/client-logo.png', $logoContents);
    $this->company->update(['logo_path' => 'company-logos/client-logo.png']);

    $this->actingAs($this->user)->post(route('admin.certificates.store'), [
        'customer_id' => $this->customer->id,
        'company_id' => $this->company->id,
        'course_name' => 'Manual Handling and People Handling',
        'instructor_name' => 'Santhosh Jacob',
        'issued_at' => '2026-08-10',
        'expires_at' => '2028-08-09',
    ])->assertRedirect(route('admin.certificates.index'));

    $certificate = Certificate::firstOrFail();
    expect($certificate->certificate_number)->toBe('100820261')
        ->and($certificate->course_name)->toBe('Manual Handling and People Handling')
        ->and($certificate->instructor_name)->toBe('Santhosh Jacob');

    $response = $this->actingAs($this->user)->get(route('admin.certificates.download', $certificate));

    $response->assertOk()->assertDownload('certificate-100820261.pdf');
    expect(str_starts_with($response->getContent(), '%PDF-'))->toBeTrue();

    $html = view('admin.certificates.pdf', [
        'certificate' => $certificate->load(['customer', 'company']),
        'brandLogo' => $certificate->company->logoDataUri(),
    ])->render();

    expect($html)->toContain(base64_encode($logoContents));
});
