<?php

use App\Mail\TransactionInvoiceMail;
use App\Models\AccountingTransaction;
use App\Models\Company;
use App\Models\Customer;
use App\Models\Label;
use App\Models\Product;
use App\Models\Role;
use App\Models\TaxClass;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
    $role = Role::where('slug', 'developer-admin')->firstOrFail();
    $this->user = User::factory()->create(['role' => $role->id]);
    $this->company = Company::create([
        'name' => 'Acme GmbH',
        'address' => 'Berlin',
        'email' => 'accounts@acme.test',
    ]);
    $this->customer = Customer::create([
        'company_id' => $this->company->id,
        'name' => 'Jane Customer',
        'email' => 'jane@example.com',
        'billing_address' => 'Munich',
    ]);
    $tax = TaxClass::create(['name' => 'VAT', 'percentage' => 10]);
    $this->product = Product::create([
        'company_id' => $this->company->id,
        'tax_class_id' => $tax->id,
        'name' => 'Ledger Package',
        'price' => 100,
    ]);
    $this->companyIncomePayload = [
        'occurred_at' => '2026-08-16 10:30:00',
        'customer_id' => $this->customer->id,
        'source_type' => 'company',
        'company_id' => $this->company->id,
        'company_items' => [[
            'kind' => 'product',
            'source_id' => $this->product->id,
            'quantity' => 2,
        ]],
        'notes' => 'Initial invoice',
    ];
    $this->makeTransaction = function (array $attributes = []) {
        return AccountingTransaction::create(array_merge([
            'reference_number' => 'TEST-'.fake()->unique()->numerify('####'),
            'type' => 'income',
            'occurred_at' => '2026-08-10 10:00:00',
            'source_type' => 'company',
            'customer_id' => $this->customer->id,
            'company_id' => $this->company->id,
            'customer_name' => $this->customer->name,
            'customer_email' => $this->customer->email,
            'issuer_name' => $this->company->name,
            'subtotal' => 100,
            'tax_total' => 0,
            'total' => 100,
            'created_by' => $this->user->id,
        ], $attributes));
    };
});

it('creates updates and deletes an income transaction', function () {
    Mail::fake();

    $response = $this->actingAs($this->user)
        ->post(route('admin.transactions.store', 'income'), $this->companyIncomePayload);

    $transaction = AccountingTransaction::firstOrFail();
    $response->assertRedirect(route('admin.transactions.show', $transaction));
    expect((float) $transaction->subtotal)->toBe(200.0)
        ->and((float) $transaction->tax_total)->toBe(20.0)
        ->and((float) $transaction->total)->toBe(220.0)
        ->and($transaction->items()->value('source_id'))->toBe($this->product->id);
    Mail::assertNothingSent();

    $label = Label::create(['name' => 'Consulting']);
    $this->actingAs($this->user)->put(route('admin.transactions.update', $transaction), [
        'occurred_at' => '2026-08-17 11:00:00',
        'customer_id' => $this->customer->id,
        'source_type' => 'personal',
        'items' => [[
            'label_id' => $label->id,
            'quantity' => 3,
            'price' => 25,
        ]],
        'notes' => 'Updated invoice',
    ])->assertRedirect(route('admin.transactions.show', $transaction));

    $transaction->refresh();
    expect($transaction->source_type)->toBe('personal')
        ->and((float) $transaction->total)->toBe(75.0)
        ->and($transaction->items()->count())->toBe(1)
        ->and($transaction->items()->value('label_id'))->toBe($label->id);

    $this->actingAs($this->user)->delete(route('admin.transactions.destroy', $transaction))
        ->assertRedirect(route('admin.transactions.index'));

    $this->assertDatabaseMissing('accounting_transactions', ['id' => $transaction->id]);
});

it('emails a company invoice only when requested and supports manual resend', function () {
    Mail::fake();

    $this->actingAs($this->user)->post(route('admin.transactions.store', 'income'),
        $this->companyIncomePayload + ['send_invoice_email' => '1'])
        ->assertSessionHas('success');

    $transaction = AccountingTransaction::firstOrFail();
    Mail::assertSent(TransactionInvoiceMail::class, function (TransactionInvoiceMail $mail) use ($transaction) {
        return $mail->hasTo($this->customer->email)
            && $mail->hasSubject("Invoice {$transaction->reference_number}")
            && collect($mail->rawAttachments)->contains(
                fn (array $attachment) => $attachment['name'] === "invoice-{$transaction->reference_number}.pdf"
                    && $attachment['options']['mime'] === 'application/pdf'
                    && str_starts_with($attachment['data'], '%PDF-')
            );
    });

    $this->actingAs($this->user)
        ->post(route('admin.transactions.send-invoice-email', $transaction))
        ->assertRedirect()
        ->assertSessionHas('success');

    Mail::assertSent(TransactionInvoiceMail::class, 2);
});

it('reports a missing saved customer email without attempting manual delivery', function () {
    Mail::fake();
    $transaction = ($this->makeTransaction)(['customer_email' => null]);
    $this->actingAs($this->user)
        ->post(route('admin.transactions.send-invoice-email', $transaction))
        ->assertRedirect()
        ->assertSessionHas('error', 'The selected customer does not have an email address.');

    Mail::assertNothingSent();
});

it('bulk deletes only entries matching every active filter and removes their files', function () {
    Storage::fake('public');
    $target = ($this->makeTransaction)();
    $personal = ($this->makeTransaction)([
        'reference_number' => 'TEST-PERSONAL',
        'source_type' => 'personal',
        'company_id' => null,
    ]);
    $outsideDate = ($this->makeTransaction)([
        'reference_number' => 'TEST-LATE',
        'occurred_at' => '2026-08-20 10:00:00',
    ]);
    Storage::disk('public')->put('expense-documents/test.pdf', 'test');
    $attachment = $target->attachments()->create([
        'disk' => 'public',
        'path' => 'expense-documents/test.pdf',
        'original_name' => 'test.pdf',
        'mime_type' => 'application/pdf',
        'size' => 4,
    ]);

    $filters = [
        'type' => 'income',
        'source' => 'company',
        'company_id' => $this->company->id,
        'from' => '2026-08-01',
        'to' => '2026-08-15',
    ];

    $this->actingAs($this->user)
        ->delete(route('admin.transactions.bulk-destroy'), $filters)
        ->assertRedirect(route('admin.transactions.index', $filters))
        ->assertSessionHas('success');

    $this->assertDatabaseMissing('accounting_transactions', ['id' => $target->id]);
    $this->assertDatabaseMissing('accounting_transaction_attachments', ['id' => $attachment->id]);
    $this->assertDatabaseHas('accounting_transactions', ['id' => $personal->id]);
    $this->assertDatabaseHas('accounting_transactions', ['id' => $outsideDate->id]);
    Storage::disk('public')->assertMissing('expense-documents/test.pdf');
});

it('refuses bulk delete without a filter and applies a standalone To Date filter', function () {
    $early = ($this->makeTransaction)();
    $late = ($this->makeTransaction)([
        'reference_number' => 'TEST-AFTER-TO',
        'occurred_at' => '2026-08-20 10:00:00',
    ]);

    $this->actingAs($this->user)
        ->delete(route('admin.transactions.bulk-destroy'))
        ->assertRedirect(route('admin.transactions.index'))
        ->assertSessionHas('error');

    $this->assertDatabaseHas('accounting_transactions', ['id' => $early->id]);

    $this->actingAs($this->user)
        ->delete(route('admin.transactions.bulk-destroy'), ['to' => '2026-08-15'])
        ->assertSessionHas('success');

    $this->assertDatabaseMissing('accounting_transactions', ['id' => $early->id]);
    $this->assertDatabaseHas('accounting_transactions', ['id' => $late->id]);
});
