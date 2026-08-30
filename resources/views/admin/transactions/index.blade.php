@extends('layouts.admin')

@section('head')
    <title>Income & Expense List</title>
    @include('admin._partials.head.g-links')
    @include('admin._partials.head.g-css-files')
    @include('admin._partials.head.g-js-files')
@endsection

@section('body')
    @php
        $currencySymbol = config('santrains.currency_symbol', '€');
    @endphp
    @include('admin._partials.preloader')
    @include('admin._partials.sidebar')
    @include('admin._partials.header')
    <section class="wrapper">
        <main class="dash-content">
            @include('admin._partials.breadcrumb')
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                <h4 class="hd-lg">Income & Expense List</h4>
                <div class="d-flex gap-2 flex-wrap">
                    @if(auth()->user()->hasAnyPermission(['transactions-create-income', 'transactions-manage-transactions']))
                        <a class="btn-sm btn-sec" href="{{ route('admin.transactions.create', 'income') }}"><i class="fa-solid fa-plus i-mr"></i>Income</a>
                    @endif
                    @if(auth()->user()->hasAnyPermission(['transactions-create-expense', 'transactions-manage-transactions']))
                        <a class="btn-sm btn-sec" href="{{ route('admin.transactions.create', 'expense') }}"><i class="fa-solid fa-plus i-mr"></i>Expense</a>
                    @endif
                    @if(auth()->user()->hasAnyPermission(['transactions-export-transactions', 'transactions-manage-transactions']))
                        <a class="btn-sm btn-sec-outline" href="{{ route('admin.transactions.export', request()->query()) }}"><i class="fa-solid fa-file-excel i-mr"></i>Export Excel</a>
                    @endif
                </div>
            </div>

            <form method="GET" class="create-event-form-box my-4">
                <div class="grid-auto">
                    <div class="form-floating">
                        <select class="form-select" name="type" id="filter_type">
                            <option value="">All Transactions</option>
                            <option value="expense" @selected(request('type') === 'expense')>Expense</option>
                            <option value="income" @selected(request('type') === 'income')>Income</option>
                        </select><label for="filter_type">Transaction Type</label>
                    </div>
                    <div class="form-floating">
                        <select class="form-select" name="source" id="filter_source">
                            <option value="">All Sources</option>
                            <option value="personal" @selected(request('source') === 'personal')>Personal</option>
                            <option value="company" @selected(request('source') === 'company')>Company</option>
                        </select><label for="filter_source">Transaction Source</label>
                    </div>
                    <div class="form-floating">
                        <select class="form-select" name="company_id" id="filter_company">
                            <option value="">All Companies</option>
                            @foreach($companies as $company)<option value="{{ $company->id }}" @selected((int) request('company_id') === $company->id)>{{ $company->name }}</option>@endforeach
                        </select><label for="filter_company">Specific Company</label>
                    </div>
                    <div class="form-floating">
                        <input class="form-control" type="date" name="from" id="filter_from" value="{{ request('from') }}"><label for="filter_from">From Date</label>
                    </div>
                    <div class="form-floating">
                        <input class="form-control" type="date" name="to" id="filter_to" value="{{ request('to') }}"><label for="filter_to">To Date (Optional)</label>
                    </div>
                </div>
                <div><button class="btn-sm btn-sec" type="submit">Apply Filters</button> <a class="btn-sm btn-sec-outline" href="{{ route('admin.transactions.index') }}">Reset</a></div>
                @if($errors->any())<div class="invalid-feedback d-block mt-2">{{ $errors->first() }}</div>@endif
            </form>

            @if($hasActiveFilters
                && $filteredTransactionCount > 0
                && auth()->user()->hasAnyPermission(['transactions-bulk-delete-transactions', 'transactions-manage-transactions']))
                <div class="d-flex justify-content-end mb-4">
                    <form method="POST" action="{{ route('admin.transactions.bulk-destroy') }}"
                          onsubmit="return confirm('Permanently delete all {{ $filteredTransactionCount }} {{ $filteredTransactionCount === 1 ? 'entry' : 'entries' }} matching the active filters?')">
                        @csrf
                        @method('DELETE')
                        @foreach($filters as $name => $value)
                            @if(filled($value))<input type="hidden" name="{{ $name }}" value="{{ $value }}">@endif
                        @endforeach
                        <button class="btn-sm btn-sec-outline red" type="submit">
                            <i class="fa-solid fa-trash i-mr"></i>Delete {{ $filteredTransactionCount }} Filtered {{ $filteredTransactionCount === 1 ? 'Entry' : 'Entries' }}
                        </button>
                    </form>
                </div>
            @endif

            <div class="grid-auto gap-card mb-4">
                @foreach(['income' => 'Income', 'expense' => 'Expense', 'profit' => 'Profit', 'loss' => 'Loss'] as $key => $label)
                    <div class="create-event-form-box">
                        <div class="text-200">{{ $label }}</div>
                        <h3 class="{{ in_array($key, ['expense', 'loss']) ? 'red' : 'green' }}">{{ $currencySymbol }}{{ number_format((float) $summary[$key], 2) }}</h3>
                    </div>
                @endforeach
            </div>

            <div class="table-responsive"><table class="table mob-view">
                <thead><tr><th>Reference</th><th>Date</th><th>Type</th><th>Company / Personal</th><th>Customer</th><th>Total</th><th>Actions</th></tr></thead>
                <tbody>
                @forelse($transactions as $transaction)
                    <tr>
                        <td><div class="data-label">Reference</div>{{ $transaction->reference_number }}</td>
                        <td><div class="data-label">Date</div>{{ $transaction->occurred_at->format('d M Y, H:i') }}</td>
                        <td><div class="data-label">Type</div><span class="text-capitalize {{ $transaction->type === 'income' ? 'green' : 'red' }}">{{ $transaction->type }}</span></td>
                        <td><div class="data-label">Company / Personal</div>{{ $transaction->source_type === 'company' ? $transaction->issuer_name : 'Personal' }}</td>
                        <td><div class="data-label">Customer</div>{{ $transaction->customer_name ?: 'N/A' }}</td>
                        <td><div class="data-label">Total</div>{{ $currencySymbol }}{{ number_format((float) $transaction->total, 2) }}</td>
                        <td><div class="data-label">Actions</div><div class="action-row">
                            <a class="action-btn" href="{{ route('admin.transactions.show', $transaction) }}" title="View"><i class="fa-regular fa-eye"></i></a>
                            @if(auth()->user()->hasAnyPermission(['transactions-edit-transactions', 'transactions-manage-transactions']))
                                <a class="action-btn edit" href="{{ route('admin.transactions.edit', $transaction) }}" title="Edit"><i class="fa-solid fa-pen"></i></a>
                            @endif
                            @if($transaction->type === 'income')<a class="action-btn edit" href="{{ route('admin.transactions.invoice', $transaction) }}" title="Invoice PDF"><i class="fa-solid fa-file-pdf"></i></a>@endif
                        </div></td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center">No transactions found.</td></tr>
                @endforelse
                </tbody>
            </table></div>
            @if($transactions->hasPages())<div class="pagination"><ul>{{ $transactions->links() }}</ul></div>@endif
        </main>
    </section>
@endsection
