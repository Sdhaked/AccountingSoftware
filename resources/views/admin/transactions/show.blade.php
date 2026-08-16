@extends('layouts.admin')

@section('head')
    <title>{{ $transaction->reference_number }}</title>
    @include('admin._partials.head.g-links')
    @include('admin._partials.head.g-css-files')
    @include('admin._partials.head.g-js-files')
@endsection

@section('body')
    @include('admin._partials.preloader')
    @include('admin._partials.sidebar')
    @include('admin._partials.header')
    <section class="wrapper"><main class="dash-content">
        @include('admin._partials.breadcrumb')
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h4 class="hd-lg">{{ $transaction->reference_number }}</h4>
            <div class="d-flex gap-2 flex-wrap">
                @if(auth()->user()->hasAnyPermission(['transactions-edit-transactions', 'transactions-manage-transactions']))
                    <a class="btn-sm btn-sec-outline" href="{{ route('admin.transactions.edit', $transaction) }}"><i class="fa-solid fa-pen i-mr"></i>Edit</a>
                @endif
                @if($transaction->type === 'income')
                    <a class="btn-sm btn-sec" href="{{ route('admin.transactions.invoice', $transaction) }}"><i class="fa-solid fa-file-pdf i-mr"></i>Download Invoice</a>
                @endif
                @if($transaction->type === 'income'
                    && $transaction->source_type === 'company'
                    && auth()->user()->hasAnyPermission(['transactions-send-invoice-email', 'transactions-manage-transactions']))
                    <form method="POST" action="{{ route('admin.transactions.send-invoice-email', $transaction) }}">
                        @csrf
                        <button class="btn-sm btn-sec" type="submit">
                            <i class="fa-solid fa-envelope i-mr"></i>Send Invoice Email
                        </button>
                    </form>
                @endif
                @if(auth()->user()->hasAnyPermission(['transactions-delete-transactions', 'transactions-manage-transactions']))
                    <form method="POST" action="{{ route('admin.transactions.destroy', $transaction) }}"
                          onsubmit="return confirm('Delete this transaction permanently?')">
                        @csrf
                        @method('DELETE')
                        <button class="btn-sm btn-sec-outline" type="submit"><i class="fa-solid fa-trash i-mr"></i>Delete</button>
                    </form>
                @endif
            </div>
        </div>
        <div class="table-responsive mb-4"><table class="table"><tbody>
            <tr><th>Type</th><td class="text-capitalize">{{ $transaction->type }}</td><th>Date & Time</th><td>{{ $transaction->occurred_at->format('d M Y, H:i') }}</td></tr>
            <tr><th>Source</th><td class="text-capitalize">{{ $transaction->source_type }}</td><th>Issuer</th><td>{{ $transaction->issuer_name }}</td></tr>
            @if($transaction->type === 'income')<tr><th>Customer</th><td>{{ $transaction->customer_name }}</td><th>Customer Email</th><td>{{ $transaction->customer_email }}</td></tr>@endif
            @if($transaction->notes)<tr><th>Notes</th><td colspan="3">{!! nl2br(e($transaction->notes)) !!}</td></tr>@endif
        </tbody></table></div>

        <h5 class="hd-sm">Items</h5>
        <div class="table-responsive"><table class="table">
            <thead><tr><th>Label</th><th>Quantity</th><th>Unit Price</th><th>Tax</th><th>Total</th></tr></thead>
            <tbody>
            @foreach($transaction->items as $item)<tr>
                <td>{{ $item->label }}</td><td>{{ rtrim(rtrim(number_format((float) $item->quantity, 3), '0'), '.') }}</td>
                <td>€{{ number_format((float) $item->unit_price, 2) }}</td><td>{{ rtrim(rtrim(number_format((float) $item->tax_rate, 3), '0'), '.') }}%</td>
                <td>€{{ number_format((float) $item->total, 2) }}</td>
            </tr>@endforeach
            </tbody>
            <tfoot>
                <tr><th colspan="4">Subtotal</th><th>€{{ number_format((float) $transaction->subtotal, 2) }}</th></tr>
                <tr><th colspan="4">Tax</th><th>€{{ number_format((float) $transaction->tax_total, 2) }}</th></tr>
                <tr><th colspan="4">Total</th><th>€{{ number_format((float) $transaction->total, 2) }}</th></tr>
            </tfoot>
        </table></div>

        @if($transaction->attachments->isNotEmpty())
            <h5 class="hd-sm mt-4">Expense Documents</h5>
            <div class="d-flex gap-2 flex-wrap">
                @foreach($transaction->attachments as $attachment)
                    <a class="btn-sm btn-sec-outline" href="{{ route('admin.transactions.attachment', $attachment) }}"><i class="fa-solid fa-paperclip i-mr"></i>{{ $attachment->original_name }}</a>
                @endforeach
            </div>
        @endif
        <div class="mt-4"><a class="btn-md btn-sec-outline" href="{{ route('admin.transactions.index') }}">Back</a></div>
    </main></section>
@endsection
