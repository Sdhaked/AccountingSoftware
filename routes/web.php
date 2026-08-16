<?php

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\CertificateController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\MasterDataController;
use App\Http\Controllers\Admin\MediaController;
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\TransactionController;
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

    Route::post('/login', 'loginPost')->name('login.post');
    Route::post('/login/send-otp', 'sendLoginOtp')->name('login.otp.send');
});

/**
 * Admin Routes
 */
Route::get('/', [DashboardController::class, 'index'])
    ->middleware(['auth', 'permission:dashboard-view-dashboard'])
    ->name('admin.dashboard.index');

Route::prefix('admin')->middleware(['auth'])->group(function () {
    Route::redirect('/', '/');

    Route::delete('/media/{target}/{id?}', [MediaController::class, 'destroy'])
        ->middleware('permission:profile-edit-profile,profile-manage-profile')
        ->name('admin.media.destroy');

    Route::controller(AuthController::class)->group(function () {
        Route::post('/logout', 'logout')->name('logout');
        Route::get('/profile', 'profile')->middleware('permission:profile-view-profile,profile-manage-profile')->name('profile');
        Route::get('/profile/edit', 'editProfile')->middleware('permission:profile-edit-profile,profile-manage-profile')->name('profile.edit');
        Route::post('/profile/update', 'updateProfile')->middleware('permission:profile-edit-profile,profile-manage-profile')->name('profile.update');
    });

    Route::prefix('master-data/{entity}')->controller(MasterDataController::class)->group(function () {
        Route::get('/', 'index')->middleware('master-data.permission:view')->name('admin.master-data.index');
        Route::get('/create', 'create')->middleware('master-data.permission:create')->name('admin.master-data.create');
        Route::post('/', 'store')->middleware('master-data.permission:create')->name('admin.master-data.store');
        Route::get('/{record}', 'show')->whereNumber('record')->middleware('master-data.permission:view')->name('admin.master-data.show');
        Route::get('/{record}/edit', 'edit')->whereNumber('record')->middleware('master-data.permission:edit')->name('admin.master-data.edit');
        Route::put('/{record}', 'update')->whereNumber('record')->middleware('master-data.permission:edit')->name('admin.master-data.update');
        Route::delete('/{record}', 'destroy')->whereNumber('record')->middleware('master-data.permission:delete')->name('admin.master-data.destroy');
    });

    Route::controller(CertificateController::class)->prefix('certificates')->group(function () {
        Route::get('/', 'index')->middleware('permission:certificates-view-certificates,certificates-manage-certificates')->name('admin.certificates.index');
        Route::get('/create', 'create')->middleware('permission:certificates-create-certificates,certificates-manage-certificates')->name('admin.certificates.create');
        Route::post('/', 'store')->middleware('permission:certificates-create-certificates,certificates-manage-certificates')->name('admin.certificates.store');
        Route::delete('/expired', 'destroyExpired')->middleware('permission:certificates-delete-certificates,certificates-manage-certificates')->name('admin.certificates.destroy-expired');
        Route::get('/{certificate}', 'show')->middleware('permission:certificates-view-certificates,certificates-manage-certificates')->name('admin.certificates.show');
        Route::get('/{certificate}/download', 'download')->middleware('permission:certificates-download-certificates,certificates-manage-certificates')->name('admin.certificates.download');
        Route::delete('/{certificate}', 'destroy')->middleware('permission:certificates-delete-certificates,certificates-manage-certificates')->name('admin.certificates.destroy');
    });

    Route::controller(TransactionController::class)->prefix('transactions')->group(function () {
        Route::get('/', 'index')->middleware('permission:transactions-view-transactions,transactions-manage-transactions')->name('admin.transactions.index');
        Route::get('/export', 'export')->middleware('permission:transactions-export-transactions,transactions-manage-transactions')->name('admin.transactions.export');
        Route::delete('/bulk', 'bulkDestroy')->middleware('permission:transactions-bulk-delete-transactions,transactions-manage-transactions')->name('admin.transactions.bulk-destroy');
        Route::get('/create/{type}', 'create')->middleware('transaction.create.permission')->name('admin.transactions.create');
        Route::post('/{type}', 'store')->middleware('transaction.create.permission')->name('admin.transactions.store');
        Route::get('/attachments/{attachment}', 'attachment')->middleware('permission:transactions-view-transactions,transactions-manage-transactions')->name('admin.transactions.attachment');
        Route::get('/{transaction}/edit', 'edit')->middleware('permission:transactions-edit-transactions,transactions-manage-transactions')->name('admin.transactions.edit');
        Route::put('/{transaction}', 'update')->middleware('permission:transactions-edit-transactions,transactions-manage-transactions')->name('admin.transactions.update');
        Route::delete('/{transaction}', 'destroy')->middleware('permission:transactions-delete-transactions,transactions-manage-transactions')->name('admin.transactions.destroy');
        Route::post('/{transaction}/send-invoice-email', 'sendInvoiceEmail')->middleware('permission:transactions-send-invoice-email,transactions-manage-transactions')->name('admin.transactions.send-invoice-email');
        Route::get('/{transaction}/invoice', 'invoice')->middleware('permission:transactions-view-transactions,transactions-manage-transactions')->name('admin.transactions.invoice');
        Route::get('/{transaction}', 'show')->middleware('permission:transactions-view-transactions,transactions-manage-transactions')->name('admin.transactions.show');
    });

    /**
     * Settings
     */
    Route::controller(SettingController::class)->prefix('settings')->middleware('developer-admin')->group(function () {
        Route::get('/', 'index')->middleware('permission:settings-view-settings,settings-manage-settings')->name('admin.settings.index');
        Route::put('/', 'update')->middleware('permission:settings-update-settings,settings-manage-settings')->name('admin.settings.update');
    });

    /**
     * Master Control
     */
    Route::prefix('master-control')->middleware('developer-admin')->group(function () {
        Route::controller(UserController::class)->prefix('users')->group(function () {
            Route::get('/', 'index')->middleware('permission:users-view-users,users-manage-users')->name('admin.users.index');
            Route::get('/create', 'create')->middleware('permission:users-create-users,users-manage-users')->name('admin.users.create');
            Route::post('/store', 'store')->middleware('permission:users-create-users,users-manage-users')->name('admin.users.store');
            Route::get('/edit/{user}', 'edit')->middleware('permission:users-edit-users,users-manage-users')->name('admin.users.edit');
            Route::post('/update/{user}', 'update')->middleware('permission:users-edit-users,users-manage-users')->name('admin.users.update');
            Route::post('/activate/{id}', 'activate')->middleware('permission:users-edit-users,users-manage-users')->name('admin.users.activate');
            Route::delete('/trash', 'emptyTrash')->middleware('permission:users-delete-users,users-manage-users')->name('admin.users.empty-trash');
            Route::delete('/force/{id}', 'forceDestroy')->middleware('permission:users-delete-users,users-manage-users')->name('admin.users.force-destroy');
            Route::delete('/destroy/{user}', 'destroy')->middleware('permission:users-delete-users,users-manage-users')->name('admin.users.destroy');
        });

        Route::controller(RoleController::class)->prefix('roles')->group(function () {
            Route::get('/', 'index')->middleware('permission:roles-view-roles,roles-manage-roles')->name('admin.roles.index');
            Route::get('/create', 'create')->middleware('permission:roles-create-roles,roles-manage-roles')->name('admin.roles.create');
            Route::post('/store', 'store')->middleware('permission:roles-create-roles,roles-manage-roles')->name('admin.roles.store');
            Route::get('/edit/{role}', 'edit')->middleware('permission:roles-edit-roles,roles-manage-roles')->name('admin.roles.edit');
            Route::post('/update/{role}', 'update')->middleware('permission:roles-edit-roles,roles-manage-roles')->name('admin.roles.update');
            Route::delete('/destroy/{role}', 'destroy')->middleware('permission:roles-delete-roles,roles-manage-roles')->name('admin.roles.destroy');
        });

        Route::controller(PermissionController::class)->prefix('permissions')->group(function () {
            Route::get('/', 'index')->middleware('permission:permissions-view-permissions,permissions-manage-permissions')->name('admin.permissions.index');
            Route::get('/create', 'create')->middleware('permission:permissions-create-permissions,permissions-manage-permissions')->name('admin.permissions.create');
            Route::post('/store', 'store')->middleware('permission:permissions-create-permissions,permissions-manage-permissions')->name('admin.permissions.store');
            Route::get('/edit/{permission}', 'edit')->middleware('permission:permissions-edit-permissions,permissions-manage-permissions')->name('admin.permissions.edit');
            Route::post('/update/{permission}', 'update')->middleware('permission:permissions-edit-permissions,permissions-manage-permissions')->name('admin.permissions.update');
            Route::delete('/destroy/{permission}', 'destroy')->middleware('permission:permissions-delete-permissions,permissions-manage-permissions')->name('admin.permissions.destroy');
        });
    });
});
