@php
    $asset = static function (string $filename): string {
        $path = public_path('images/pdf/santrains/'.$filename);

        return 'data:image/png;base64,'.base64_encode((string) file_get_contents($path));
    };
    $logo = $sponsorImage ?: $asset('logo.png');
    $formatNumber = static function (float $value): string {
        return rtrim(rtrim(number_format($value, 2, '.', ','), '0'), '.');
    };
    $invoiceNumber = '#'.ltrim((string) $transaction->reference_number, '#');
    $invoiceNumberClass = strlen($invoiceNumber) > 18 ? 'invoice-number invoice-number-small' : 'invoice-number';
    $emailClass = strlen((string) $transaction->customer_email) > 31 ? 'customer-email customer-email-small' : 'customer-email';
@endphp
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice {{ $transaction->reference_number }}</title>
    <style>
        @page { size: A4 portrait; margin: 0; }
        * { box-sizing: border-box; }
        html, body { margin: 0; padding: 0; width: 595pt; height: 842pt; }
        body { position: relative; overflow: hidden; font-family: DejaVu Sans, sans-serif; color: #080808; }
        .art { position: absolute; z-index: 0; }
        .wave-top { left: 0; top: -0.3pt; width: 595.02pt; height: 175.75pt; }
        .wave-bottom { left: -2.22pt; top: 696.05pt; width: 599.88pt; height: 144.85pt; }
        .brand-logo { position: absolute; z-index: 1; left: 21.42pt; top: 39.84pt; width: 228.18pt; height: 149.28pt; }
        .invoice-title { position: absolute; z-index: 2; left: 381pt; top: 120pt; width: 160pt; text-align: center; font-family: DejaVu Sans, sans-serif; font-size: 36pt; font-weight: normal; line-height: 1; }
        .customer { position: absolute; z-index: 2; left: 62.5pt; top: 168.8pt; width: 282pt; }
        .customer-label { font-size: 12pt; font-weight: bold; line-height: 1.2; }
        .customer-name { margin-top: -4pt; font-size: 15pt; line-height: 1.2; }
        .customer-address { margin-top: 0; font-size: 14pt; line-height: 1.25; }
        .customer-email { display: inline-block; margin-top: -4pt; color: #0756cf; border-bottom: 1.2pt solid #0756cf; font-size: 14.5pt; line-height: 1.05; }
        .customer-email-small { font-size: 12pt; }
        .invoice-meta { position: absolute; z-index: 2; left: 358pt; top: 160.2pt; width: 180pt; border-collapse: collapse; font-size: 12.5pt; }
        .invoice-meta td { padding: 2pt 0 2.3pt; white-space: nowrap; }
        .invoice-meta .label { width: 100pt; }
        .invoice-meta .value { text-align: right; }
        .invoice-number { letter-spacing: -0.45pt; }
        .invoice-number-small { font-size: 10pt; }
        .items { position: absolute; z-index: 2; left: 62pt; top: 316pt; width: 475pt; border-collapse: collapse; table-layout: fixed; }
        .items col.course { width: 32%; }
        .items col.fee { width: 20%; }
        .items col.numeric { width: 24%; }
        .items th { padding: 8pt 4pt; background: #c8232c; color: #fff; text-align: center; font-size: 12pt; font-weight: normal; line-height: 1.2; }
        .items th:first-child { border-radius: 2pt 0 0 2pt; }
        .items th:last-child { border-radius: 0 2pt 2pt 0; }
        .items td { padding: 17pt 3pt 7pt; border-bottom: 1.4pt solid #c8232c; text-align: center; vertical-align: top; font-size: 11pt; line-height: 1.3; }
        .items td:first-child { padding-left: 2pt; text-align: left; font-size: 11.5pt; }
        .item-label { width: 180pt; transform: scaleX(.92); transform-origin: 0 0; }
        .amount-received { position: absolute; z-index: 2; left: 73pt; top: 641.9pt; font-size: 12pt; font-weight: normal; }
        .total-bar { position: absolute; z-index: 2; left: 358.5pt; top: 641.5pt; width: 178.5pt; height: 22.5pt; padding-top: 4pt; border-radius: 4pt; background: #c8232c; color: #fff; font-size: 12pt; line-height: 1; }
        .total-label { position: absolute; left: 29pt; }
        .total-colon { position: absolute; left: 90pt; }
        .total-value { position: absolute; right: 12pt; }
        .contact { position: absolute; z-index: 2; left: 62.25pt; width: 200pt; font-size: 13pt; line-height: 17pt; }
        .contact img { position: absolute; left: 0; width: 19.7pt; height: 17.1pt; }
        .contact span { position: absolute; left: 27.35pt; white-space: nowrap; }
        .phone { top: 681.3pt; }
        .email { top: 701.9pt; }
        .web { top: 725.55pt; }
        .email-mark { left: 3.48pt !important; top: 3.48pt; width: 9.84pt !important; height: 9.9pt !important; }
        .instructor { position: absolute; z-index: 2; left: 382pt; top: 680.35pt; width: 146pt; text-align: center; }
        .instructor-name { font-size: 15pt; line-height: 1.2; }
        .instructor-role { margin-top: 1pt; font-size: 15pt; line-height: 1.2; }
    </style>
</head>
<body>
    <img class="art wave-top" src="{{ $asset('invoice-wave-top.png') }}" alt="">
    <img class="art wave-bottom" src="{{ $asset('invoice-wave-bottom.png') }}" alt="">
    <img class="brand-logo" src="{{ $logo }}" alt="San Trains">

    <div class="invoice-title">INVOICE</div>
    <div class="customer">
        <div class="customer-label">INVOICE TO:</div>
        <div class="customer-name">{{ $transaction->customer_name }}</div>
        @if(filled($transaction->customer_address))
            <div class="customer-address">{!! nl2br(e($transaction->customer_address)) !!}</div>
        @endif
        @if(filled($transaction->customer_email))
            <div class="{{ $emailClass }}">{{ $transaction->customer_email }}</div>
        @endif
    </div>

    <table class="invoice-meta">
        <tr><td class="label">Invoice No::</td><td class="value"><span class="{{ $invoiceNumberClass }}">{{ $invoiceNumber }}</span></td></tr>
        <tr><td class="label">Invoice Date:</td><td class="value">{{ $transaction->occurred_at->format('d M, Y') }}</td></tr>
    </table>

    <table class="items">
        <colgroup><col class="course" width="32%"><col class="fee" width="20%"><col class="numeric" width="24%"><col class="numeric" width="24%"></colgroup>
        <thead><tr><th>Particulars</th><th>Rate</th><th>QTY</th><th>Sub Total</th></tr></thead>
        <tbody>
        @foreach($transaction->items as $item)
            <tr>
                <td><div class="item-label">{{ $item->label }}</div></td>
                <td>{{ $formatNumber((float) $item->unit_price) }}</td>
                <td>{{ $formatNumber((float) $item->quantity) }}</td>
                <td>{{ $formatNumber((float) $item->total) }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>

    <div class="amount-received">Amount Received</div>
    <div class="total-bar">
        <span class="total-label">Total</span><span class="total-colon">:</span>
        <span class="total-value">{{ $formatNumber((float) $transaction->total) }}</span>
    </div>

    <div class="contact phone"><img src="{{ $asset('contact-phone.png') }}" alt=""><span>{{ config('santrains.phone') }}</span></div>
    <div class="contact email">
        <img src="{{ $asset('contact-email-base.png') }}" alt=""><img class="email-mark" src="{{ $asset('contact-email-mark.png') }}" alt="">
        <span>{{ config('santrains.email') }}</span>
    </div>
    <div class="contact web"><img src="{{ $asset('contact-web.png') }}" alt=""><span>{{ config('santrains.website') }}</span></div>

    <div class="instructor">
        <div class="instructor-name">{{ config('santrains.instructor_name') }}</div>
        <div class="instructor-role">Instructor</div>
    </div>
</body>
</html>
