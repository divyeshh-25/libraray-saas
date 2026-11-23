@php
    $user = $tx->student->user;
    $tenant = $user->tenant;
    $branch = $user->branch;
    $balance = $tx->balanceAfter();
    $money = fn ($v) => '₹'.number_format((float) $v, 2);
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Receipt {{ $tx->receipt_number }}</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: 'DejaVu Sans', Arial, sans-serif; font-size: 12px; color: #1d2433; margin: 0; padding: {{ $forPdf ? '0' : '24px' }}; background: {{ $forPdf ? '#fff' : '#f3f4f6' }}; }
        .sheet { max-width: 560px; margin: 0 auto; background: #fff; padding: 24px; {{ $forPdf ? '' : 'border: 1px solid #e5e7eb; border-radius: 8px;' }} }
        h1 { font-size: 18px; margin: 0; }
        .muted { color: #667085; }
        table { width: 100%; border-collapse: collapse; }
        td, th { padding: 7px 0; text-align: left; vertical-align: top; }
        .kv td:first-child { color: #667085; width: 40%; }
        .kv td:last-child { text-align: right; font-weight: bold; }
        .head { border-bottom: 2px solid #3453c4; padding-bottom: 12px; margin-bottom: 14px; }
        .title { text-align: right; font-size: 15px; font-weight: bold; color: #3453c4; }
        .total { border-top: 1px solid #d0d5dd; border-bottom: 1px solid #d0d5dd; margin: 14px 0; }
        .total td { padding: 10px 0; font-size: 15px; font-weight: bold; }
        .total td:last-child { text-align: right; }
        .stamp { display: inline-block; border: 2px solid #12b76a; color: #067647; padding: 2px 10px; font-weight: bold; letter-spacing: 1px; }
        .bar { max-width: 560px; margin: 0 auto 12px; text-align: right; }
        .btn { display: inline-block; padding: 7px 14px; border-radius: 6px; border: 1px solid #d0d5dd; background: #fff; color: #1d2433; text-decoration: none; font-size: 13px; cursor: pointer; font-family: inherit; }
        .btn-primary { background: #3453c4; border-color: #3453c4; color: #fff; }
        @media print { .bar { display: none; } body { background: #fff; padding: 0; } .sheet { border: 0; } }
    </style>
</head>
<body>
@unless ($forPdf)
    <div class="bar">
        <a class="btn" href="{{ $pdfUrl }}">Download PDF</a>
        <button class="btn btn-primary" onclick="window.print()">Print</button>
    </div>
@endunless
<div class="sheet">
    <table class="head">
        <tr>
            <td>
                <h1>{{ $tenant?->library_name ?? 'LibNextGen' }}</h1>
                <div class="muted">{{ $branch?->name }}@if ($branch?->address) · {{ $branch->address }}@endif</div>
                @if ($branch?->phone)<div class="muted">{{ $branch->phone }}</div>@endif
            </td>
            <td class="title">PAYMENT RECEIPT<br><span class="muted" style="font-weight: normal; font-size: 12px;">{{ $tx->receipt_number }}</span></td>
        </tr>
    </table>

    <table class="kv">
        <tr><td>Received from</td><td>{{ $user->name }}</td></tr>
        <tr><td>Student ID</td><td>{{ $tx->student->student_code }}</td></tr>
        <tr><td>Invoice</td><td>{{ $tx->invoice->invoice_number }}</td></tr>
        @if ($tx->invoice->feeStructure)<tr><td>Fee plan</td><td>{{ $tx->invoice->feeStructure->name }}</td></tr>@endif
        <tr><td>Payment date</td><td>{{ $tx->transaction_date->format('d M Y, h:i A') }}</td></tr>
        <tr><td>Payment method</td><td>{{ ucwords(str_replace('_', ' ', $tx->payment_method)) }}</td></tr>
        @if ($tx->reference_number)<tr><td>Reference no.</td><td>{{ $tx->reference_number }}</td></tr>@endif
    </table>

    <table class="total">
        <tr><td>Amount paid</td><td>{{ $money($tx->amount) }}</td></tr>
    </table>

    <table class="kv">
        <tr><td>Invoice total</td><td>{{ $money($tx->invoice->final_amount) }}</td></tr>
        <tr><td>Balance after this payment</td><td>{{ $money($balance) }}</td></tr>
    </table>

    <p style="margin: 16px 0 0;"><span class="stamp">{{ bccomp($balance, '0', 2) === 0 ? 'PAID IN FULL' : 'PARTIAL PAYMENT' }}</span></p>
    <p class="muted" style="margin-top: 18px; font-size: 11px;">
        @if ($tx->receivedBy)Received by {{ $tx->receivedBy->name }}. @endif
        This is a computer-generated receipt.
    </p>
</div>
</body>
</html>
