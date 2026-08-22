<?php

use App\Models\Role;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
    $this->role = Role::where('slug', 'developer-admin')->firstOrFail();
    $this->user = User::factory()->create([
        'email' => 'owner@example.com',
        'password' => 'correct-password',
        'role' => $this->role->id,
    ]);
});

it('offers OTP login only and rejects the old password endpoint', function () {
    $this->get(route('login'))
        ->assertOk()
        ->assertSee('Send OTP')
        ->assertSee('class="style-box auth-box"', false)
        ->assertDontSee('needs-validation')
        ->assertDontSee('OTP Login')
        ->assertDontSee('Password Login')
        ->assertDontSee('Forgot password?');

    expect(Route::has('login.password'))->toBeFalse()
        ->and(Route::has('forgot.password'))->toBeFalse()
        ->and(Route::has('password.email'))->toBeFalse()
        ->and(Route::has('password.update'))->toBeFalse();

    $this->postJson('/admin/login/password', [
        'email' => $this->user->email,
        'password' => 'correct-password',
    ])->assertNotFound();

    $this->assertGuest();
});

it('sends and verifies a login OTP using the dedicated table', function () {
    Mail::fake();

    $this->postJson(route('login.otp.send'), ['email' => $this->user->email])
        ->assertOk()
        ->assertJsonPath('success', true);

    $this->assertDatabaseHas('login_otps', ['email' => $this->user->email]);
    $this->assertDatabaseMissing('password_resets', ['email' => $this->user->email]);

    DB::table('login_otps')->where('email', $this->user->email)->update([
        'token' => Hash::make('123456'),
        'created_at' => now(),
    ]);

    $this->postJson(route('login.post'), [
        'email' => $this->user->email,
        'otp' => '123456',
    ])->assertOk()->assertJsonPath('success', true);

    $this->assertAuthenticatedAs($this->user);
    $this->assertDatabaseMissing('login_otps', ['email' => $this->user->email]);
});

it('does not send an OTP to a deactivated user', function () {
    Mail::fake();
    $this->user->delete();

    $this->postJson(route('login.otp.send'), ['email' => $this->user->email])
        ->assertUnprocessable();

    Mail::assertNothingSent();
});

it('shows a clear message when the login email is not registered', function () {
    Mail::fake();

    $this->postJson(route('login.otp.send'), ['email' => 'missing@example.com'])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['email'])
        ->assertJsonPath('message', 'This email is not registered.');

    Mail::assertNothingSent();
});
