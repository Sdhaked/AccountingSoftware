<!doctype html>
<html><head><meta charset="utf-8"><title>Invoice {{ $transaction->reference_number }}</title>
<style>
    @page { margin: 35px; }
    body { font-family: DejaVu Sans, sans-serif; color:#202735; font-size:12px; }
    h1 { margin:0; font-size:30px; color:#253d4e; }
    .header, .parties, .totals { width:100%; border-collapse:collapse; }
    .header td { vertical-align:top; padding-bottom:25px; }
    .parties td { width:50%; vertical-align:top; padding:14px; background:#f3f5f8; }
    .items { width:100%; border-collapse:collapse; margin-top:25px; }
    .items th { background:#253d4e; color:#fff; text-align:left; padding:9px; }
    .items td { padding:9px; border-bottom:1px solid #d8dde5; }
    .number { text-align:right; }
    .totals { margin-top:15px; }
    .totals td { padding:5px; }
    .muted { color:#697386; }
</style></head><body>
<table class="header"><tr><td><h1>INVOICE</h1><div class="muted">{{ $transaction->reference_number }}</div></td>
<td class="number"><strong>Date</strong><br>{{ $transaction->occurred_at->format('d M Y, H:i') }}</td></tr></table>
<table class="parties"><tr>
    <td><strong>From</strong><br><br>{{ $transaction->issuer_name }}<br>{!! nl2br(e($transaction->issuer_address ?: '')) !!}<br>{{ $transaction->issuer_email }}</td>
    <td><strong>Bill To</strong><br><br>{{ $transaction->customer_name }}<br>{!! nl2br(e($transaction->customer_address ?: '')) !!}<br>{{ $transaction->customer_email }}</td>
</tr></table>
<table class="items"><thead><tr><th>Item</th><th class="number">Qty</th><th class="number">Rate</th><th class="number">Tax</th><th class="number">Amount</th></tr></thead><tbody>
@foreach($transaction->items as $item)<tr><td>{{ $item->label }}</td><td class="number">{{ rtrim(rtrim(number_format((float) $item->quantity, 3), '0'), '.') }}</td>
<td class="number">€{{ number_format((float) $item->unit_price, 2) }}</td><td class="number">{{ rtrim(rtrim(number_format((float) $item->tax_rate, 3), '0'), '.') }}%</td><td class="number">€{{ number_format((float) $item->total, 2) }}</td></tr>@endforeach
</tbody></table>
<table class="totals"><tr><td style="width:70%"></td><td>Subtotal</td><td class="number">€{{ number_format((float) $transaction->subtotal, 2) }}</td></tr>
<tr><td></td><td>Tax</td><td class="number">€{{ number_format((float) $transaction->tax_total, 2) }}</td></tr>
<tr><td></td><td><strong>Total</strong></td><td class="number"><strong>€{{ number_format((float) $transaction->total, 2) }}</strong></td></tr></table>
@if($transaction->notes)<p><strong>Notes</strong><br>{!! nl2br(e($transaction->notes)) !!}</p>@endif
</body></html>
