<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>{{ __('Payment Receipt') }} #{{ $payment->id }}</title>
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; padding: 28px; font-family: DejaVu Sans, Arial, sans-serif; color: #0f172a; font-size: 13px; }
        .header { display: flex; justify-content: space-between; gap: 24px; border-bottom: 2px solid #059669; padding-bottom: 14px; margin-bottom: 22px; }
        .business-name { font-size: 19px; font-weight: 700; margin: 0; }
        .muted { color: #64748b; }
        .invoice-title { text-align: right; }
        .invoice-title h1 { margin: 0; font-size: 20px; letter-spacing: 2px; }
        .amount-box { border: 2px solid #059669; border-radius: 8px; padding: 18px; text-align: center; margin-bottom: 22px; }
        .amount-box .amount { font-size: 30px; font-weight: 700; color: #059669; margin-top: 4px; }
        table { width: 100%; border-collapse: collapse; }
        td { padding: 8px 10px; border-bottom: 1px solid #e2e8f0; vertical-align: top; }
        td.key { width: 34%; color: #64748b; }
        .label { font-size: 10px; text-transform: uppercase; color: #64748b; margin-bottom: 2px; }
        .signature { margin-top: 48px; display: flex; justify-content: space-between; }
        .signature div { width: 40%; border-top: 1px solid #94a3b8; padding-top: 6px; text-align: center; color: #64748b; font-size: 11px; }
    </style>
</head>
<body>
    <div class="header">
        <div>
            <h1 class="business-name">{{ $settings['name'] }}</h1>
            @if ($settings['address'])<div class="muted">{{ $settings['address'] }}</div>@endif
            @if ($settings['phone'])<div class="muted">{{ $settings['phone'] }}</div>@endif
        </div>
        <div class="invoice-title">
            <h1>{{ __('RECEIPT') }}</h1>
            <div>#{{ $payment->id }}</div>
            <div class="muted">{{ $payment->payment_date?->format('d M Y') }}</div>
        </div>
    </div>

    <div class="amount-box">
        <div class="label">{{ __('Amount received / paid') }}</div>
        <div class="amount">{{ $settings['currency'] }} {{ number_format((float) $payment->amount, 2) }}</div>
    </div>

    <table>
        <tr><td class="key">{{ __('Supplier') }}</td><td><strong>{{ $payment->supplier?->name }}</strong></td></tr>
        @if ($payment->supplier?->phone)
            <tr><td class="key">{{ __('Phone') }}</td><td>{{ $payment->supplier->phone }}</td></tr>
        @endif
        <tr><td class="key">{{ __('Method') }}</td><td>{{ __($payment->method) }}</td></tr>
        <tr><td class="key">{{ __('Reference No') }}</td><td>{{ $payment->reference_no ?? '—' }}</td></tr>
        <tr><td class="key">{{ __('Purchase') }}</td><td>{{ $payment->purchase?->invoice_no ?? __('General payment') }}</td></tr>
        <tr><td class="key">{{ __('Salesman') }}</td><td>{{ $payment->salesman?->name ?? '—' }}</td></tr>
        @if ($payment->notes)
            <tr><td class="key">{{ __('Notes') }}</td><td>{{ $payment->notes }}</td></tr>
        @endif
    </table>

    <div class="signature">
        <div>{{ __('Supplier signature') }}</div>
        <div>{{ __('Authorized signature') }}</div>
    </div>
</body>
</html>
