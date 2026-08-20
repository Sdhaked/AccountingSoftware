<?php

use App\Models\Company;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);

    $role = Role::where('slug', 'developer-admin')->firstOrFail();
    $this->user = User::factory()->create(['role' => $role->id]);
});

it('rejects letters in company phone numbers', function () {
    $this->actingAs($this->user)
        ->post(route('admin.master-data.store', 'companies'), [
            'name' => 'Acme Finance',
            'address' => 'Main Street',
            'phone' => 'abc123',
            'email' => 'hello@example.com',
        ])
        ->assertSessionHasErrors('phone');

    $this->assertDatabaseMissing('companies', ['name' => 'Acme Finance']);
});

it('stores company phone numbers when they contain digits only', function () {
    $this->actingAs($this->user)
        ->post(route('admin.master-data.store', 'companies'), [
            'name' => 'Acme Finance',
            'address' => 'Main Street',
            'phone' => '9876543210',
            'email' => 'hello@example.com',
        ])
        ->assertRedirect(route('admin.master-data.index', 'companies'));

    $this->assertDatabaseHas('companies', [
        'name' => 'Acme Finance',
        'phone' => '9876543210',
    ]);
});

it('rejects letters in customer phone numbers', function () {
    $company = Company::create([
        'name' => 'Acme Finance',
        'address' => 'Main Street',
        'phone' => '9876543210',
        'email' => 'hello@example.com',
    ]);

    $this->actingAs($this->user)
        ->post(route('admin.master-data.store', 'customers'), [
            'name' => 'Rahul Sharma',
            'phone' => 'nine eight seven',
            'email' => 'rahul@example.com',
            'billing_address' => 'Billing Street',
            'company_id' => $company->id,
        ])
        ->assertSessionHasErrors('phone');
});
