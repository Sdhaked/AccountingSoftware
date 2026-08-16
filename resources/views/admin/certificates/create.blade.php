@extends('layouts.admin')

@section('head')
    <title>Create Certificate</title>
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
            <h4 class="hd-lg">Create Certificate</h4>
            <form action="{{ route('admin.certificates.store') }}" method="POST">
                @csrf
                <div class="grid-2 grid-sm-1 gap-card">
                    <div class="form-floating">
                        <select class="form-select @error('customer_id') is-invalid @enderror" id="certificate_customer" name="customer_id" required>
                            <option value="">Select Customer</option>
                            @foreach($customers as $customer)
                                <option value="{{ $customer->id }}" data-company="{{ $customer->company_id }}" @selected((int) old('customer_id') === $customer->id)>
                                    {{ $customer->name }} - {{ $customer->company->name }}
                                </option>
                            @endforeach
                        </select>
                        <label for="certificate_customer">Customer*</label>
                        @error('customer_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="form-floating">
                        <select class="form-select @error('company_id') is-invalid @enderror" id="certificate_company" name="company_id" required>
                            <option value="">Select Company</option>
                            @foreach($companies as $company)
                                <option value="{{ $company->id }}" @selected((int) old('company_id') === $company->id)>{{ $company->name }}</option>
                            @endforeach
                        </select>
                        <label for="certificate_company">Company*</label>
                        @error('company_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="form-floating">
                        <input class="form-control @error('issued_at') is-invalid @enderror" type="date" id="issued_at" name="issued_at" value="{{ old('issued_at', today()->format('Y-m-d')) }}" required>
                        <label for="issued_at">Date of Issue*</label>
                        @error('issued_at')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="form-floating">
                        <input class="form-control @error('expires_at') is-invalid @enderror" type="date" id="expires_at" name="expires_at" value="{{ old('expires_at') }}" required>
                        <label for="expires_at">Expiry Date*</label>
                        @error('expires_at')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div><button class="btn-md btn-sec" type="submit">Create</button> <a class="btn-md btn-sec-outline" href="{{ route('admin.certificates.index') }}">Cancel</a></div>
            </form>
        </main>
    </section>
@endsection

@section('custom-script')
<script>
document.getElementById('certificate_customer').addEventListener('change', function () {
    const option = this.options[this.selectedIndex];
    if (option.dataset.company) document.getElementById('certificate_company').value = option.dataset.company;
});
</script>
@endsection
