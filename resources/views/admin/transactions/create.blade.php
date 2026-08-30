@extends('layouts.admin')

@php
    $isIncome = $type === 'income';
    $isEdit = isset($transaction);
    $sourceType = old('source_type', $transaction->source_type ?? 'company');
    $storedCompanyRows = $isEdit && $sourceType === 'company'
        ? $transaction->items->map(fn ($item) => [
            'kind' => $item->item_type,
            'source_id' => $item->source_id,
            'quantity' => $item->quantity,
        ])->all()
        : [];
    $storedLabelRows = $isEdit && $sourceType === 'personal'
        ? $transaction->items->map(fn ($item) => [
            'label_id' => $item->item_type === 'other' ? 'other' : $item->label_id,
            'other_label' => $item->item_type === 'other' ? $item->label : '',
            'quantity' => $item->quantity,
            'price' => $item->unit_price,
        ])->all()
        : [];
    $companyRows = old('company_items', $storedCompanyRows ?: [['kind' => 'product', 'source_id' => '', 'quantity' => 1]]);
    $labelRows = old('items', $storedLabelRows ?: [['label_id' => '', 'other_label' => '', 'quantity' => 1, 'price' => '']]);
    $currencySymbol = config('santrains.currency_symbol', '€');
@endphp

@section('head')
    <title>{{ $isEdit ? 'Edit' : 'Create' }} {{ ucfirst($type) }} Entry</title>
    @include('admin._partials.head.g-links')
    @include('admin._partials.head.g-css-files')
    @include('admin._partials.head.g-js-files')
    <style>
        .transaction-form-shell {
            position: relative;
        }

        .transaction-submit-loader {
            position: fixed;
            inset: 0;
            z-index: 1085;
            display: none;
            place-items: center;
            padding: 24px;
            background:
                radial-gradient(circle at 50% 45%, rgba(93, 148, 190, 0.16), transparent 32%),
                rgba(8, 12, 24, 0.38);
            backdrop-filter: blur(2px);
        }

        .transaction-form-shell.is-submitting .transaction-submit-loader {
            display: grid;
        }

        .transaction-loader-card {
            min-width: min(360px, 92vw);
            padding: 24px 28px;
            border: 1px solid rgba(128, 170, 205, 0.32);
            border-radius: 8px;
            background: rgba(13, 18, 32, 0.82);
            box-shadow: 0 24px 70px rgba(0, 0, 0, 0.36);
            text-align: center;
        }

        .transaction-loader-ring {
            width: 58px;
            height: 58px;
            margin: 0 auto 16px;
            border-radius: 50%;
            border: 4px solid rgba(135, 193, 238, 0.18);
            border-top-color: #87c1ee;
            animation: transactionLoaderSpin 0.8s linear infinite;
        }

        .transaction-loader-title {
            margin: 0;
            color: var(--color-hd-100);
            font-size: 1.05rem;
            font-weight: 700;
        }

        .transaction-loader-copy {
            margin: 7px 0 0;
            color: var(--color-text-300);
            font-size: 0.88rem;
        }

        .transaction-form-shell.is-submitting .btn-md[type="submit"] {
            pointer-events: none;
            opacity: 0.78;
        }

        @keyframes transactionLoaderSpin {
            to {
                transform: rotate(360deg);
            }
        }
    </style>
@endsection

@section('body')
    @include('admin._partials.preloader')
    @include('admin._partials.sidebar')
    @include('admin._partials.header')
    <section class="wrapper">
        <main class="dash-content">
            @include('admin._partials.breadcrumb')
            <h4 class="hd-lg">{{ $isEdit ? 'Edit' : 'Create' }} {{ ucfirst($type) }} Entry</h4>

            <form class="transaction-form-shell"
                  id="transaction-entry-form"
                  action="{{ $isEdit ? route('admin.transactions.update', $transaction) : route('admin.transactions.store', $type) }}"
                  method="POST" enctype="multipart/form-data">
                @csrf
                @if($isEdit) @method('PUT') @endif
                <div class="grid-2 grid-sm-1 gap-card">
                    <div class="form-floating">
                        <input class="form-control @error('occurred_at') is-invalid @enderror" type="datetime-local"
                               id="occurred_at" name="occurred_at"
                               value="{{ old('occurred_at', $isEdit ? $transaction->occurred_at->format('Y-m-d\TH:i') : now()->format('Y-m-d\TH:i')) }}" required>
                        <label for="occurred_at">Date & Time*</label>
                        @error('occurred_at')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    @if($isIncome)
                        <div class="form-floating">
                            <select class="form-select @error('customer_id') is-invalid @enderror" id="transaction_customer" name="customer_id" required>
                                <option value="">Select Customer</option>
                                @foreach($customers as $customer)
                                    <option value="{{ $customer->id }}" data-company="{{ $customer->company_id }}"
                                            @selected((int) old('customer_id', $transaction->customer_id ?? null) === $customer->id)>
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
                                    <option value="{{ $company->id }}" @selected((int) old('company_id', $transaction->company_id ?? null) === $company->id)>{{ $company->name }}</option>
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

                        @unless($isEdit)
                            <div class="check-btn" id="send-invoice-email-wrap">
                                <input class="form-check-input" type="checkbox" value="1"
                                       id="send_invoice_email" name="send_invoice_email"
                                       @checked(old('send_invoice_email'))>
                                <label for="send_invoice_email">Send invoice to the customer's email after saving</label>
                            </div>
                        @endunless
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
                    @if($isEdit && $transaction->attachments->isNotEmpty())
                        <div>
                            <h5 class="hd-sm">Current Documents</h5>
                            <div class="d-flex gap-2 flex-wrap">
                                @foreach($transaction->attachments as $attachment)
                                    <div class="check-btn">
                                        <input class="form-check-input" type="checkbox" name="remove_attachments[]"
                                               id="remove_attachment_{{ $attachment->id }}" value="{{ $attachment->id }}">
                                        <label for="remove_attachment_{{ $attachment->id }}">Remove {{ $attachment->original_name }}</label>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
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
                              id="transaction_notes" name="notes">{{ old('notes', $transaction->notes ?? '') }}</textarea>
                    <label for="transaction_notes">Notes</label>
                    @error('notes')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div>
                    <button class="btn-md btn-sec" type="submit" data-submit-text="{{ $isEdit ? 'Update' : 'Record' }} {{ ucfirst($type) }}">
                        {{ $isEdit ? 'Update' : 'Record' }} {{ ucfirst($type) }}
                    </button>
                    <a class="btn-md btn-sec-outline" href="{{ route('admin.transactions.index') }}">Cancel</a>
                </div>

                <div class="transaction-submit-loader" aria-live="polite" aria-hidden="true">
                    <div class="transaction-loader-card">
                        <div class="transaction-loader-ring" aria-hidden="true"></div>
                        <p class="transaction-loader-title">{{ $isEdit ? 'Updating entry...' : 'Saving entry...' }}</p>
                        <p class="transaction-loader-copy">Please wait, your {{ $type }} details are being processed.</p>
                    </div>
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
    const currencySymbol = @json($currencySymbol);
    const sourceSelect = document.getElementById('transaction_source');
    const companySelect = document.getElementById('transaction_company');
    const customerSelect = document.getElementById('transaction_customer');
    const companyEntry = document.getElementById('company-entry');
    const labelEntry = document.getElementById('label-entry');
    const companyWrap = document.getElementById('company-select-wrap');
    const sendInvoiceEmail = document.getElementById('send_invoice_email');
    const transactionForm = document.getElementById('transaction-entry-form');

    function setControlsDisabled(container, disabled) {
        if (!container) return;
        container.querySelectorAll('input, select, textarea, button').forEach(function (control) {
            if (control.classList.contains('remove-entry') || control.id?.startsWith('add-')) return;
            control.disabled = disabled;
        });
    }

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
            ? `Rate: ${currencySymbol}${Number(current.dataset.price).toFixed(2)} | Tax: ${Number(current.dataset.tax).toFixed(3)}%`
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
        companySelect.disabled = !companyMode;
        setControlsDisabled(companyEntry, !companyMode);
        setControlsDisabled(labelEntry, companyMode);
        if (!companyMode && sendInvoiceEmail) sendInvoiceEmail.checked = false;
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

    transactionForm?.addEventListener('submit', function (event) {
        if (this.dataset.submitting === 'true') {
            event.preventDefault();
            return;
        }

        this.dataset.submitting = 'true';
        this.classList.add('is-submitting');
        const loader = this.querySelector('.transaction-submit-loader');
        loader?.setAttribute('aria-hidden', 'false');
        const submitButton = this.querySelector('button[type="submit"]');
        if (submitButton) {
            submitButton.disabled = true;
            submitButton.textContent = 'Saving...';
        }
    });

    document.querySelectorAll('.label-select').forEach(function (select) {
        select.dispatchEvent(new Event('change', { bubbles: true }));
    });
    toggleSource();
});
</script>
@endsection
