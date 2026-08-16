<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $email = env('ADMIN_EMAIL', config('mail.from.address', 'admin@example.com'));
        $password = env('ADMIN_PASSWORD', 'password');
        $developerRoleId = Role::where('slug', 'developer-admin')->value('id');

        User::updateOrCreate(
            ['email' => $email],
            [
                'name' => 'Admin',
                'username' => 'admin',
                'email_verified_at' => now(),
                'password' => Hash::make($password),
                'role' => $developerRoleId,
                'mobile_number_prefix' => '+91',
                'mobile_number' => '5244524525',
            ]
        );
    }
}
