<?php

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

it('blocks direct URLs when the role lacks permission', function () {
    $role = Role::create(['name' => 'viewer', 'slug' => 'viewer']);
    $user = User::factory()->create(['role' => $role->id]);

    $this->actingAs($user)->get(route('admin.settings.index'))->assertForbidden();
    $this->actingAs($user)->get(route('admin.transactions.index'))->assertForbidden();
    $this->actingAs($user)->get(route('admin.master-data.index', 'companies'))->assertForbidden();
});

it('allows a route after its permission is assigned', function () {
    $role = Role::create(['name' => 'settings viewer', 'slug' => 'settings-viewer']);
    $role->permissions()->attach(
        Permission::where('slug', 'settings-view-settings')->firstOrFail()
    );
    $user = User::factory()->create(['role' => $role->id]);

    $this->actingAs($user)->get(route('admin.settings.index'))->assertOk();
    $this->actingAs($user)->put(route('admin.settings.update'), [
        'company_name' => 'Blocked Update',
    ])->assertForbidden();
});
