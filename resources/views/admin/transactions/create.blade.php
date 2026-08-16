@extends('layouts.admin')

@php
    $isIncome = $type === 'income';
    $sourceType = old('source_type', 'company');
    $companyRows = old('company_items', [['kind' => 'product', 'source_id' => '', 'quantity' => 1]]);
    $labelRows = old('items', [['label_id' => '', 'other_label' => '', 'quantity' => 1, 'price' => '']]);
@endphp

@section('head')
    <title>Create {{ ucfirst($type) }} Entry</title>
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
            <h4 class="hd-lg">Create {{ ucfirst($type) }} Entry</h4>

            <form action="{{ route('admin.transactions.store', $type) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="grid-2 grid-sm-1 gap-card">
                    <div class="form-floating">
                        <input class="form-control @error('occurred_at') is-invalid @enderror" type="datetime-local"
                               id="occurred_at" name="occurred_at" value="{{ old('occurred_at', now()->format('Y-m-d\TH:i')) }}" required>
                        <label for="occurred_at">Date & Time*</label>
                        @error('occurred_at')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    @if($isIncome)
                        <div class="form-floating">
                            <select class="form-select @error('customer_id') is-invalid @enderror" id="transaction_customer" name="customer_id" required>
                                <option value="">Select Customer</option>
                                @foreach($customers as $customer)
                                    <option value="{{ $customer->id }}" data-company="{{ $customer->company_id }}" @selected((int) old('customer_id') === $customer->id)>
                                        {{ $customer->name }} - {{ $customer->company->name }}
                                    </option>
                                @endforeach
                            </select>
                            <label for="transaction_customer">Customer*</label>
                            @error('customer_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="form-floating">
                            <select class="form-select" id="transaction_source" name="source_type" required>
                                <option value="company" @selected($sourceType === 'company')>Company</option>
                                <option value="personal" @selected($sourceType === 'personal')>Personal</option>
                            </select>
                            <label for="transaction_source">Company or Personal*</label>
                        </div>
                        <div class="form-floating" id="company-select-wrap">
                            <select class="form-select @error('company_id') is-invalid @enderror" id="transaction_company" name="company_id">
                                <option value="">Select Company</option>
                                @foreach($companies as $company)
                                    <option value="{{ $company->id }}" @selected((int) old('company_id') === $company->id)>{{ $company->name }}</option>
                                @endforeach
                            </select>
                            <label for="transaction_company">Company*</label>
                            @error('company_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    @endif
                </div>

                @if($isIncome)
                    <section id="company-entry" class="grid-1 gap-card {{ $sourceType === 'company' ? '' : 'd-none' }}">
                        <div class="d-flex align-items-center justify-content-between gap-2">
                            <h5 class="hd-sm mb-0">Products & Services</h5>
                            <button class="btn-sm btn-sec" type="button" id="add-company-item"><i class="fa-solid fa-plus i-mr"></i>Add Item</button>
                        </div>
                        <div id="company-items" class="grid-1 gap-card">
                            @foreach($companyRows as $index => $row)
                                @include('admin.transactions._partials.company-row', compact('index', 'row', 'products', 'services'))
                            @endforeach
                        </div>
                        @error('company_items')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </section>
                @endif

                <section id="label-entry" class="grid-1 gap-card {{ $isIncome && $sourceType !== 'personal' ? 'd-none' : '' }}">
                    <div class="d-flex align-items-center justify-content-between gap-2">
                        <div>
                            <h5 class="hd-sm mb-0">{{ $isIncome ? 'Personal Items' : 'Expense Items' }}</h5>
                            <span class="search-base">Choose a Label Master value or select Other to add a new label.</span>
                        </div>
                        <button class="btn-sm btn-sec" type="button" id="add-label-item"><i class="fa-solid fa-plus i-mr"></i>Add Item</button>
                    </div>
                    <div id="label-items" class="grid-1 gap-card">
                        @foreach($labelRows as $index => $row)
                            @include('admin.transactions._partials.label-row', compact('index', 'row', 'labels', 'type'))
                        @endforeach
                    </div>
                    @error('items')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </section>

                @unless($isIncome)
                    <div>
                        <label for="expense_documents" class="mb-2">Expense Documents</label>
                        <input class="form-control @error('documents.*') is-invalid @enderror" type="file"
                               id="expense_documents" name="documents[]" multiple
                               accept=".pdf,.jpg,.jpeg,.png,.webp,.doc,.docx,.xls,.xlsx">
                        <span class="search-base">Up to 10 files, maximum 10 MB each.</span>
                        @error('documents.*')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>
                @endunless

                <div class="form-floating">
                    <textarea class="form-control @error('notes') is-invalid @enderror" style="height:110px"
                              id="transaction_notes" name="notes">{{ old('notes') }}</textarea>
                    <label for="transaction_notes">Notes</label>
                    @error('notes')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div>
                    <button class="btn-md btn-sec" type="submit">Record {{ ucfirst($type) }}</button>
                    <a class="btn-md btn-sec-outline" href="{{ route('admin.transactions.index') }}">Cancel</a>
                </div>
            </form>

            @if($isIncome)
                <template id="company-row-template">
                    @include('admin.transactions._partials.company-row', ['index' => '__INDEX__', 'row' => [], 'products' => $products, 'services' => $services])
                </template>
            @endif
            <template id="label-row-template">
                @include('admin.transactions._partials.label-row', ['index' => '__INDEX__', 'row' => [], 'labels' => $labels, 'type' => $type])
            </template>
        </main>
    </section>
@endsection

@section('custom-script')
<script>
document.addEventListener('DOMContentLoaded', function () {
    let companyIndex = {{ count($companyRows) }};
    let labelIndex = {{ count($labelRows) }};
    const sourceSelect = document.getElementById('transaction_source');
    const companySelect = document.getElementById('transaction_company');
    const customerSelect = document.getElementById('transaction_customer');
    const companyEntry = document.getElementById('company-entry');
    const labelEntry = document.getElementById('label-entry');
    const companyWrap = document.getElementById('company-select-wrap');

    function refreshCompanyRow(row) {
        if (!row || !companySelect) return;
        const kind = row.querySelector('.item-kind').value;
        const item = row.querySelector('.item-source');
        const company = companySelect.value;
        Array.from(item.options).forEach(function (option, index) {
            if (index === 0) return;
            const allowed = option.dataset.kind === kind && option.dataset.company === company;
            option.disabled = !allowed;
            option.hidden = !allowed;
        });
        const selected = item.options[item.selectedIndex];
        if (selected && selected.value && selected.disabled) item.value = '';
        const current = item.options[item.selectedIndex];
        row.querySelector('.item-rate').textContent = current && current.value
            ? `Rate: €${Number(current.dataset.price).toFixed(2)} | Tax: ${Number(current.dataset.tax).toFixed(3)}%`
            : 'Select an item to view its rate.';
    }

    function refreshAllCompanyRows() {
        document.querySelectorAll('[data-company-row]').forEach(refreshCompanyRow);
        if (customerSelect && sourceSelect && sourceSelect.value === 'company') {
            Array.from(customerSelect.options).forEach(function (option, index) {
                if (index === 0) return;
                option.disabled = Boolean(companySelect.value) && option.dataset.company !== companySelect.value;
            });
            const selected = customerSelect.options[customerSelect.selectedIndex];
            if (selected && selected.disabled) customerSelect.value = '';
        }
    }

    function toggleSource() {
        if (!sourceSelect) return;
        const companyMode = sourceSelect.value === 'company';
        companyEntry.classList.toggle('d-none', !companyMode);
        labelEntry.classList.toggle('d-none', companyMode);
        companyWrap.classList.toggle('d-none', !companyMode);
        companySelect.required = companyMode;
        Array.from(customerSelect.options).forEach(option => option.disabled = false);
        if (companyMode) refreshAllCompanyRows();
    }

    document.getElementById('add-company-item')?.addEventListener('click', function () {
        const html = document.getElementById('company-row-template').innerHTML.replaceAll('__INDEX__', companyIndex++);
        document.getElementById('company-items').insertAdjacentHTML('beforeend', html);
        refreshCompanyRow(document.getElementById('company-items').lastElementChild);
    });

    document.getElementById('add-label-item').addEventListener('click', function () {
        const html = document.getElementById('label-row-template').innerHTML.replaceAll('__INDEX__', labelIndex++);
        document.getElementById('label-items').insertAdjacentHTML('beforeend', html);
    });

    document.addEventListener('change', function (event) {
        if (event.target.matches('.item-kind, .item-source')) refreshCompanyRow(event.target.closest('[data-company-row]'));
        if (event.target.matches('.label-select')) {
            const row = event.target.closest('[data-label-row]');
            const other = event.target.value === 'other';
            row.querySelector('.other-label-wrap').classList.toggle('d-none', !other);
            row.querySelector('.other-label').required = other;
        }
    });

    document.addEventListener('click', function (event) {
        const button = event.target.closest('.remove-entry');
        if (!button) return;
        const row = button.closest('[data-company-row], [data-label-row]');
        const container = row.parentElement;
        if (container.children.length > 1) row.remove();
    });

    sourceSelect?.addEventListener('change', toggleSource);
    companySelect?.addEventListener('change', refreshAllCompanyRows);
    customerSelect?.addEventListener('change', function () {
        if (sourceSelect?.value === 'company') {
            const selected = this.options[this.selectedIndex];
            if (selected.dataset.company) {
                companySelect.value = selected.dataset.company;
                refreshAllCompanyRows();
            }
        }
    });

    document.querySelectorAll('.label-select').forEach(function (select) {
        select.dispatchEvent(new Event('change', { bubbles: true }));
    });
    toggleSource();
});
</script>
@endsection
