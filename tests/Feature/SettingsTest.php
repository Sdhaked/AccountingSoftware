<?php

use App\Models\AppSetting;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

it('saves settings and encrypts the SMTP password', function () {
    $this->seed(RolePermissionSeeder::class);
    $role = Role::where('slug', 'developer-admin')->firstOrFail();
    $user = User::factory()->create(['role' => $role->id]);

    $this->actingAs($user)->put(route('admin.settings.update'), [
        'company_name' => 'North Star Accounting',
        'base_country' => 'germany',
        'allow_super_admin_permanent_delete' => '1',
        'mail_host' => 'smtp.example.com',
        'mail_port' => 587,
        'mail_scheme' => 'smtp',
        'mail_username' => 'mailer@example.com',
        'mail_password' => 'smtp-secret',
        'mail_from_address' => 'billing@example.com',
        'mail_from_name' => 'North Star',
    ])->assertRedirect()->assertSessionHas('success');

    $setting = AppSetting::firstOrFail();
    expect($setting->mail_password)->toBe('smtp-secret')
        ->and(DB::table('app_settings')->value('mail_password'))->not->toBe('smtp-secret')
        ->and($setting->allow_super_admin_permanent_delete)->toBeTrue();

    $setting->applyMailConfiguration();
    expect(config('mail.mailers.smtp.host'))->toBe('smtp.example.com')
        ->and(config('mail.from.address'))->toBe('billing@example.com');
});
