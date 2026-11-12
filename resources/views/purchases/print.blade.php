@php
    $money = fn ($value) => \App\Support\Money::format($value);
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $purchase->invoice_no }}</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: DejaVu Sans, Arial, sans-serif; color: #0f172a; margin: 0; padding: 24px; font-size: 12px; }
        .wrap { max-width: 900px; margin: 0 auto; }
        .head { display: flex; justify-content: space-between; border-bottom: 2px solid #0f172a; padding-bottom: 12px; }
        .brand h1 { margin: 0; font-size: 22px; }
        .brand p, .meta p { margin: 3px 0; color: #475569; }
        .meta { text-align: right; }
        .meta h2 { margin: 0 0 6px; font-size: 18px; }
        .grid { display: flex; gap: 20px; margin: 18px 0; }
        .box { flex: 1; border: 1px solid #e2e8f0; border-radius: 8px; padding: 12px; }
        .box h3 { margin: 0 0 6px; font-size: 11px; text-transform: uppercase; color: #64748b; letter-spacing: .06em; }
        table { width: 100%; border-collapse: collapse; margin-top: 8px; }
        th, td { border-bottom: 1px solid #e2e8f0; padding: 8px 6px; text-align: left; }
        th { background: #f8fafc; font-size: 10px; text-transform: uppercase; color: #475569; }
        .text-end { text-align: right; }
        .totals { margin-left: auto; width: 280px; margin-top: 14px; }
        .totals div { display: flex; justify-content: space-between; padding: 5px 0; }
        .totals .grand { border-top: 2px solid #0f172a; font-size: 15px; font-weight: bold; margin-top: 4px; padding-top: 8px; }
        .footer { margin-top: 28px; text-align: center; color: #64748b; font-size: 10px; border-top: 1px solid #e2e8f0; padding-top: 10px; }
        .actions { max-width: 900px; margin: 0 auto 16px; text-align: right; }
        .actions button { background: #059669; color: #fff; border: 0; border-radius: 6px; padding: 8px 16px; cursor: pointer; font-weight: bold; }
        @media print { .actions { display: none; } body { padding: 0; } }
    </style>
</head>
<body>
<div class="actions"><button onclick="window.print()">{{ __('Print') }}</button></div>
<div class="wrap">
    <div class="head">
        <div class="brand">
            <h1>{{ $settings['name'] }}</h1>
            @if ($settings['phone'])<p>{{ $settings['phone'] }}</p>@endif
            @if ($settings['address'])<p>{{ $settings['address'] }}</p>@endif
        </div>
        <div class="meta">
            <h2>{{ __('Purchase Invoice') }}</h2>
            <p><strong>{{ $purchase->invoice_no }}</strong></p>
            <p>{{ $purchase->purchase_date?->format('d M Y') }}</p>
            @if ($purchase->due_date)<p>{{ __('Due') }}: {{ $purchase->due_date->format('d M Y') }}</p>@endif
        </div>
    </div>

    <div class="grid">
        <div class="box">
            <h3>{{ __('Supplier') }}</h3>
            <p><strong>{{ $purchase->supplier?->name }}</strong></p>
            @if ($purchase->supplier?->phone)<p>{{ $purchase->supplier->phone }}</p>@endif
            @if ($purchase->supplier?->address)<p>{{ $purchase->supplier->address }}</p>@endif
        </div>
        <div class="box">
            <h3>{{ __('Details') }}</h3>
            <p>{{ __('Salesman') }}: {{ $purchase->salesman?->name ?? '—' }}</p>
            <p>{{ __('Status') }}: {{ __(ucfirst($purchase->status)) }}</p>
            <p>{{ __('Remaining') }}: {{ $money($purchase->remaining_amount) }}</p>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>{{ __('Item') }}</th>
                <th class="text-end">{{ __('Qty') }}</th>
                <th>{{ __('Unit') }}</th>
                <th class="text-end">{{ __('Rate') }}</th>
                <th class="text-end">{{ __('Discount') }}</th>
                <th class="text-end">{{ __('Tax') }}</th>
                <th class="text-end">{{ __('Total') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($purchase->items as $item)
                <tr>
                    <td>{{ $item->product_name }}</td>
                    <td class="text-end">{{ (float) $item->quantity }}</td>
                    <td>{{ $item->unit }}</td>
                    <td class="text-end">{{ $money($item->rate) }}</td>
                    <td class="text-end">{{ $money($item->discount) }}</td>
                    <td class="text-end">{{ $money($item->tax) }}</td>
                    <td class="text-end">{{ $money($item->total) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="totals">
        <div><span>{{ __('Subtotal') }}</span><span>{{ $money($purchase->subtotal) }}</span></div>
        <div><span>{{ __('Discount') }}</span><span>{{ $money($purchase->discount) }}</span></div>
        <div><span>{{ __('Tax') }}</span><span>{{ $money($purchase->tax) }}</span></div>
        <div><span>{{ __('Additional Charges') }}</span><span>{{ $money($purchase->additional_charges) }}</span></div>
        <div class="grand"><span>{{ __('Grand Total') }}</span><span>{{ $money($purchase->total) }}</span></div>
        <div><span>{{ __('Paid') }}</span><span>{{ $money($purchase->paid_amount) }}</span></div>
        <div><span>{{ __('Remaining') }}</span><span>{{ $money($purchase->remaining_amount) }}</span></div>
    </div>

    @if ($purchase->notes)
        <div class="box" style="margin-top:18px">
            <h3>{{ __('Notes') }}</h3>
            <p>{{ $purchase->notes }}</p>
        </div>
    @endif

    <div class="footer">{{ $settings['name'] }} &middot; {{ __('Thank you for your business.') }}</div>
</div>
</body>
</html>
