<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AppSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SettingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $setting = AppSetting::query()->firstOrNew();

        return view('admin.settings.index', compact('setting'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'company_name' => ['nullable', 'string', 'max:255'],
            'base_country' => ['nullable', 'string', 'max:255'],
            'allow_super_admin_permanent_delete' => ['nullable', 'boolean'],
            'pdf_sponsor_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'remove_pdf_sponsor_image' => ['nullable', 'boolean'],
            'mail_host' => ['nullable', 'string', 'max:255'],
            'mail_port' => ['nullable', 'integer', 'between:1,65535'],
            'mail_scheme' => ['nullable', 'in:smtp,smtps'],
            'mail_username' => ['nullable', 'string', 'max:255'],
            'mail_password' => ['nullable', 'string', 'max:1000'],
            'mail_from_address' => ['nullable', 'email', 'max:255'],
            'mail_from_name' => ['nullable', 'string', 'max:255'],
            'mail_cc' => ['nullable', 'email', 'max:255'],
        ]);

        $setting = AppSetting::query()->firstOrNew();
        $oldImage = $setting->pdf_sponsor_image;
        $newImage = null;

        if ($request->hasFile('pdf_sponsor_image')) {
            $newImage = $request->file('pdf_sponsor_image')->store('settings', 'public');
            $validated['pdf_sponsor_image'] = $newImage;
        } elseif ($request->boolean('remove_pdf_sponsor_image')) {
            $validated['pdf_sponsor_image'] = null;
        }

        unset($validated['remove_pdf_sponsor_image']);
        $validated['allow_super_admin_permanent_delete'] = $request->boolean('allow_super_admin_permanent_delete');

        if (! filled($validated['mail_password'] ?? null)) {
            unset($validated['mail_password']);
        }

        try {
            $setting->fill($validated)->save();
        } catch (\Throwable $exception) {
            if ($newImage) {
                Storage::disk('public')->delete($newImage);
            }

            throw $exception;
        }

        if ($oldImage && $oldImage !== $setting->pdf_sponsor_image) {
            Storage::disk('public')->delete($oldImage);
        }

        return back()->with('success', 'Settings updated successfully.');
    }
}
