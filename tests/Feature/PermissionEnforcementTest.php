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

it('reserves every master control screen for the developer admin role', function () {
    $allPermissionIds = Permission::pluck('id');
    $developerRole = Role::where('slug', 'developer-admin')->firstOrFail();
    $developer = User::factory()->create(['role' => $developerRole->id]);

    foreach (['admin', 'super-admin'] as $roleSlug) {
        $role = Role::where('slug', $roleSlug)->firstOrFail();
        $role->permissions()->sync($allPermissionIds);
        $user = User::factory()->create(['role' => $role->id]);

        $this->actingAs($user)->get(route('admin.dashboard.index'))
            ->assertOk()
            ->assertDontSee('Master Control');
        $this->actingAs($user)->get(route('admin.users.index'))->assertForbidden();
        $this->actingAs($user)->get(route('admin.roles.index'))->assertForbidden();
        $this->actingAs($user)->get(route('admin.permissions.index'))->assertForbidden();
        $this->actingAs($user)->post(route('admin.roles.store'), [])->assertForbidden();
    }

    $this->actingAs($developer)->get(route('admin.dashboard.index'))
        ->assertOk()
        ->assertSee('Master Control');
    $this->actingAs($developer)->get(route('admin.users.index'))->assertOk();
    $this->actingAs($developer)->get(route('admin.roles.index'))->assertOk();
    $this->actingAs($developer)->get(route('admin.permissions.index'))->assertOk();
});

it('keeps the create certificate action at compact button height', function () {
    $developerRole = Role::where('slug', 'developer-admin')->firstOrFail();
    $developer = User::factory()->create(['role' => $developerRole->id]);

    $this->actingAs($developer)
        ->get(route('admin.certificates.index'))
        ->assertOk()
        ->assertSee('class="btn-sm btn-sec align-self-start"', false);
});
