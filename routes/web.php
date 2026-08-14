<?php

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\MediaController;
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/csrf-token', function () {
    return response()->json(['token' => csrf_token()]);
})->name('csrf_token');

/**
 * Admin guest routes
 */
Route::prefix('admin')->controller(AuthController::class)->middleware('guest')->group(function () {
    Route::get('/login', 'login')->name('login');
    Route::get('/forgot-password', 'forgotPassword')->name('forgot.password');
    Route::get('/set-new-password', 'setNewPassword')->name('set.new.password');

    Route::post('/login', 'loginPost')->name('login.post');
    Route::post('/login/send-otp', 'sendLoginOtp')->name('login.otp.send');
    Route::post('/logout', 'logoutPost')->name('logout');

    Route::post('/forgot-password', 'sendResetLink')->name('password.email');
    Route::post('/reset-password', 'reset')->name('password.reset');
});

/**
 * Admin Routes
 */
Route::get('/', [DashboardController::class, 'index'])
    ->middleware('auth')
    ->name('admin.dashboard.index');

Route::prefix('admin')->middleware(['auth'])->group(function () {
    Route::redirect('/', '/');

    Route::delete('/media/{target}/{id?}', [MediaController::class, 'destroy'])
        ->name('admin.media.destroy');

    Route::controller(AuthController::class)->group(function () {
        Route::post('/logout', 'logout')->name('logout');
        Route::get('/profile', 'profile')->name('profile');
        Route::get('/profile/edit', 'editProfile')->name('profile.edit');
        Route::post('/profile/update', 'updateProfile')->name('profile.update');
    });

    /**
     * Settings
     */
    Route::controller(SettingController::class)->prefix('settings')->group(function () {
        Route::get('/', 'index')->name('admin.settings.index');
    });

    /**
     * Master Control
     */
    Route::prefix('master-control')->group(function () {
        Route::controller(UserController::class)->prefix('users')->group(function () {
            Route::get('/', 'index')->name('admin.users.index');
            Route::get('/create', 'create')->name('admin.users.create');
            Route::post('/store', 'store')->name('admin.users.store');
            Route::get('/edit/{user}', 'edit')->name('admin.users.edit');
            Route::post('/update/{user}', 'update')->name('admin.users.update');
            Route::post('/activate/{id}', 'activate')->name('admin.users.activate');
            Route::delete('/destroy/{user}', 'destroy')->name('admin.users.destroy');
        });

        Route::controller(RoleController::class)->prefix('roles')->group(function () {
            Route::get('/', 'index')->name('admin.roles.index');
            Route::get('/create', 'create')->name('admin.roles.create');
            Route::post('/store', 'store')->name('admin.roles.store');
            Route::get('/edit/{role}', 'edit')->name('admin.roles.edit');
            Route::post('/update/{role}', 'update')->name('admin.roles.update');
            Route::delete('/destroy/{role}', 'destroy')->name('admin.roles.destroy');
        });

        Route::controller(PermissionController::class)->prefix('permissions')->group(function () {
            Route::get('/', 'index')->name('admin.permissions.index');
            Route::get('/create', 'create')->name('admin.permissions.create');
            Route::post('/store', 'store')->name('admin.permissions.store');
            Route::get('/edit/{permission}', 'edit')->name('admin.permissions.edit');
            Route::post('/update/{permission}', 'update')->name('admin.permissions.update');
            Route::delete('/destroy/{permission}', 'destroy')->name('admin.permissions.destroy');
        });
    });
});
