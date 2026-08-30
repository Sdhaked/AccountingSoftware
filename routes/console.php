<?php

use App\Http\Controllers\Admin\MasterDataController;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Inspiring;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\Console\Command\Command;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('app:smoke-master-data {entity=services}', function () {
    $entity = (string) $this->argument('entity');
    $role = Role::where('slug', 'developer-admin')->first();
    $user = $role ? User::where('role', $role->id)->first() : null;
    $user ??= User::query()->first();

    if (! $user) {
        $this->error('No user exists for the master data smoke test.');

        return Command::FAILURE;
    }

    Auth::setUser($user);

    $request = Request::create("/admin/master-data/{$entity}", 'GET');
    $request->setUserResolver(fn () => $user);

    try {
        $response = app(MasterDataController::class)->index($request, $entity);
        $response->render();
    } catch (Throwable $exception) {
        report($exception);
        $this->error($exception::class.': '.$exception->getMessage());

        throw $exception;
    }

    $this->info("Master data page rendered successfully: {$entity}");

    return Command::SUCCESS;
})->purpose('Render a master data page after deployment and fail with details if it is broken');
