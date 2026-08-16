<?php

use App\Models\AppSetting;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('only permits configured administrators to permanently delete trashed users', function () {
    $this->seed(RolePermissionSeeder::class);
    $developerRole = Role::where('slug', 'developer-admin')->firstOrFail();
    $admin = User::factory()->create(['role' => $developerRole->id]);
    $trashedUser = User::factory()->create(['role' => Role::where('slug', 'admin')->value('id')]);
    $trashedUser->delete();

    $this->actingAs($admin)
        ->delete(route('admin.users.force-destroy', $trashedUser->id))
        ->assertForbidden();

    AppSetting::create(['allow_super_admin_permanent_delete' => true]);

    $this->actingAs($admin)
        ->delete(route('admin.users.force-destroy', $trashedUser->id))
        ->assertRedirect();

    $this->assertDatabaseMissing('users', ['id' => $trashedUser->id]);
});
