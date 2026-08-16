<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Certificate;
use App\Models\Company;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Service;
use App\Models\TaxClass;
use Illuminate\Support\Facades\Storage;

class DashboardController extends Controller
{
    public function index()
    {
        $cards = [
            ['label' => 'Companies', 'value' => Company::count(), 'icon' => 'fa-building'],
            ['label' => 'Customers', 'value' => Customer::count(), 'icon' => 'fa-users'],
            ['label' => 'Products', 'value' => Product::count(), 'icon' => 'fa-box'],
            ['label' => 'Services', 'value' => Service::count(), 'icon' => 'fa-handshake'],
            ['label' => 'Tax Classes', 'value' => TaxClass::count(), 'icon' => 'fa-percent'],
            ['label' => 'Certificates', 'value' => Certificate::count(), 'icon' => 'fa-certificate'],
            ['label' => 'Expired Certificates', 'value' => Certificate::whereDate('expires_at', '<', today())->count(),
                'icon' => 'fa-triangle-exclamation'],
            ['label' => 'Storage Used', 'value' => $this->storageUsed(), 'icon' => 'fa-hard-drive'],
        ];

        return view('admin.dashboard.index', compact('cards'));
    }

    private function storageUsed(): string
    {
        $bytes = collect(Storage::disk('public')->allFiles())->sum(
            fn (string $file) => Storage::disk('public')->size($file)
        );
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $position = 0;

        while ($bytes >= 1024 && $position < count($units) - 1) {
            $bytes /= 1024;
            $position++;
        }

        return number_format($bytes, $position === 0 ? 0 : 2) . ' ' . $units[$position];
    }
}
