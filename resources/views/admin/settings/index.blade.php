@extends('layouts.admin')

@section('head')
    <title>Settings</title>
    @include('admin._partials.head.g-links')
    @include('admin._partials.head.g-css-files')
    @include('admin._partials.head.g-js-files')
@endsection

@section('body')
    @include('admin._partials.preloader')
    @include('admin._partials.sidebar')
    @include('admin._partials.header')

    <section class="wrapper">
        <main class="dash-content">
            @include('admin._partials.breadcrumb')
            <h4 class="hd-lg">Settings</h4>

            @if($errors->any())
                <div class="alert alert-danger mb-4">{{ $errors->first() }}</div>
            @endif

            <form action="{{ route('admin.settings.update') }}" method="POST" enctype="multipart/form-data"
                  class="needs-validation" novalidate>
                @csrf
                @method('PUT')

                <section class="style-box">
                    <h3 class="hd-sm">Business Details</h3>
                    <div class="grid-2 grid-sm-1 gap-card">
                        <div class="form-floating">
                            <input type="text" class="form-control @error('company_name') is-invalid @enderror"
                                   id="company_name" name="company_name" value="{{ old('company_name', $setting->company_name) }}">
                            <label for="company_name">Company Name</label>
                        </div>

                        <div class="form-floating">
                            <select class="form-select @error('base_country') is-invalid @enderror"
                                    id="base_country" name="base_country">
                                <option value="">Select Country</option>
                                @include('admin._partials.options.countries-options')
                            </select>
                            <label for="base_country">Base Country</label>
                        </div>

                        <div class="check-btn">
                            <input class="form-check-input" type="checkbox" value="1"
                                   id="allow_permanent_delete" name="allow_super_admin_permanent_delete"
                                   @checked(old('allow_super_admin_permanent_delete', $setting->allow_super_admin_permanent_delete))>
                            <label for="allow_permanent_delete">Allow Super Admin to permanently delete records</label>
                        </div>
                    </div>
                </section>

                <section class="style-box">
                    <h3 class="hd-sm">PDF Sponsor Image</h3>
                    <div class="label-spc upload-box">
                        <div class="previewBox mt-2">
                            <img src="{{ $setting->pdf_sponsor_image ? asset('storage/' . $setting->pdf_sponsor_image) : asset('images/uploadimg.svg') }}"
                                 class="preview thumb-img x3" alt="PDF sponsor preview">
                        </div>
                        <div class="mt-4">
                            <label for="pdf_sponsor_image">Upload Image</label>
                            <input type="file" class="form-control mt-1 @error('pdf_sponsor_image') is-invalid @enderror"
                                   id="pdf_sponsor_image" name="pdf_sponsor_image" accept="image/jpeg,image/png,image/webp">
                            <small class="search-base">JPG, PNG or WebP, maximum 4 MB.</small>
                        </div>
                        @if($setting->pdf_sponsor_image)
                            <div class="check-btn mt-3">
                                <input class="form-check-input" type="checkbox" value="1"
                                       id="remove_pdf_sponsor_image" name="remove_pdf_sponsor_image">
                                <label for="remove_pdf_sponsor_image">Remove current sponsor image</label>
                            </div>
                        @endif
                    </div>
                </section>

                <section class="style-box">
                    <h3 class="hd-sm">SMTP Email</h3>
                    <div class="grid-2 grid-sm-1 gap-card">
                        <div class="form-floating">
                            <input type="text" class="form-control" id="mail_host" name="mail_host"
                                   value="{{ old('mail_host', $setting->mail_host) }}">
                            <label for="mail_host">Host</label>
                        </div>
                        <div class="form-floating">
                            <input type="number" class="form-control" id="mail_port" name="mail_port" min="1" max="65535"
                                   value="{{ old('mail_port', $setting->mail_port) }}">
                            <label for="mail_port">Port</label>
                        </div>
                        <div class="form-floating">
                            <select class="form-select" id="mail_scheme" name="mail_scheme">
                                <option value="">Default</option>
                                <option value="smtp" @selected(old('mail_scheme', $setting->mail_scheme) === 'smtp')>SMTP / STARTTLS</option>
                                <option value="smtps" @selected(old('mail_scheme', $setting->mail_scheme) === 'smtps')>SMTPS</option>
                            </select>
                            <label for="mail_scheme">Connection</label>
                        </div>
                        <div class="form-floating">
                            <input type="text" class="form-control" id="mail_username" name="mail_username"
                                   value="{{ old('mail_username', $setting->mail_username) }}">
                            <label for="mail_username">Username</label>
                        </div>
                        <div class="passBox">
                            <div class="form-floating">
                                <input type="password" class="form-control" id="mail_password" name="mail_password"
                                       autocomplete="new-password">
                                <label for="mail_password">Password (leave blank to keep)</label>
                            </div>
                            <button type="button" class="input-group-text pass-eye" title="Show password">
                                <i class="fa-solid fa-eye-slash"></i>
                            </button>
                        </div>
                        <div class="form-floating">
                            <input type="email" class="form-control" id="mail_from_address" name="mail_from_address"
                                   value="{{ old('mail_from_address', $setting->mail_from_address) }}">
                            <label for="mail_from_address">From Address</label>
                        </div>
                        <div class="form-floating">
                            <input type="text" class="form-control" id="mail_from_name" name="mail_from_name"
                                   value="{{ old('mail_from_name', $setting->mail_from_name) }}">
                            <label for="mail_from_name">From Name</label>
                        </div>
                        <div class="form-floating">
                            <input type="email" class="form-control" id="mail_cc" name="mail_cc"
                                   value="{{ old('mail_cc', $setting->mail_cc) }}">
                            <label for="mail_cc">CC Address</label>
                        </div>
                    </div>
                </section>

                @if(auth()->user()->hasAnyPermission(['settings-update-settings', 'settings-manage-settings']))
                    <button type="submit" class="btn-md btn-sec btn-min-w">Save Settings</button>
                @endif
            </form>
        </main>
    </section>
@endsection

@section('custom-script')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const country = document.getElementById('base_country');
    if (country) country.value = @json(old('base_country', $setting->base_country));
});
</script>
@endsection
