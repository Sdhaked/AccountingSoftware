<?php

use App\Models\Company;
use App\Models\Role;
use App\Models\Service;
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

it('renders the services master data list', function () {
    $company = Company::create([
        'name' => 'San Trains',
        'address' => 'Dublin',
        'email' => 'accounts@santrains.test',
    ]);

    Service::create([
        'company_id' => $company->id,
        'name' => 'Manual Handling',
        'default_rate' => 100,
    ]);

    $this->actingAs($this->user)
        ->get(route('admin.master-data.index', 'services'))
        ->assertOk()
        ->assertSee('Service List')
        ->assertSee('Manual Handling');
});

it('renders a master data detail page', function () {
    $company = Company::create([
        'name' => 'San Trains',
        'address' => 'Dublin',
        'email' => 'accounts@santrains.test',
    ]);

    $this->actingAs($this->user)
        ->get(route('admin.master-data.show', ['companies', $company->id]))
        ->assertOk()
        ->assertSee('Company Details')
        ->assertSee('San Trains');
});
