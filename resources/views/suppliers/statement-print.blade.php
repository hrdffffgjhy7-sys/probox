<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('Statement') }} · {{ $supplier->name }}</title>
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; padding: 24px; font-family: DejaVu Sans, Arial, sans-serif; color: #0f172a; font-size: 12px; }
        .toolbar { margin-bottom: 16px; text-align: right; }
        .toolbar button { padding: 8px 16px; border: 0; border-radius: 6px; background: #059669; color: #fff; font-size: 13px; cursor: pointer; }
        .header { display: flex; justify-content: space-between; gap: 24px; border-bottom: 2px solid #059669; padding-bottom: 14px; margin-bottom: 18px; }
        .business-name { font-size: 19px; font-weight: 700; margin: 0; }
        .muted { color: #64748b; }
        h1 { margin: 0 0 4px; font-size: 18px; }
        .summary { display: flex; gap: 16px; margin-bottom: 16px; }
        .summary div { flex: 1; border: 1px solid #e2e8f0; border-radius: 6px; padding: 10px; }
        .summary .label { font-size: 10px; text-transform: uppercase; color: #64748b; }
        .summary .value { font-size: 16px; font-weight: 700; margin-top: 2px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 7px 9px; border-bottom: 1px solid #e2e8f0; text-align: left; }
        th { background: #f1f5f9; font-size: 10px; text-transform: uppercase; color: #475569; }
        td.num, th.num { text-align: right; }
        tfoot td { font-weight: 700; background: #f8fafc; }
        @media print { .toolbar { display: none; } body { padding: 0; } }
    </style>
</head>
<body>
    <div class="toolbar"><button type="button" onclick="window.print()">{{ __('Print') }}</button></div>

    <div class="header">
        <div>
            <h1 class="business-name">{{ $supplier->name }}</h1>
            @if ($supplier->phone)<div class="muted">{{ $supplier->phone }}</div>@endif
            @if ($supplier->address)<div class="muted">{{ $supplier->address }}</div>@endif
        </div>
        <div style="text-align:right">
            <h1>{{ __('ACCOUNT STATEMENT') }}</h1>
            <div class="muted">{{ $range[0]?->format('d M Y') ?? __('Beginning') }} &ndash; {{ $range[1]?->format('d M Y') ?? __('Today') }}</div>
            <div class="muted">{{ now()->format('d M Y H:i') }}</div>
        </div>
    </div>

    <div class="summary">
        <div><div class="label">{{ __('Opening balance') }}</div><div class="value">{{ number_format((float) $opening, 2) }}</div></div>
        <div><div class="label">{{ __('Closing balance') }}</div><div class="value">{{ number_format((float) $closing, 2) }}</div></div>
        <div><div class="label">{{ __('Entries') }}</div><div class="value">{{ $entries->count() }}</div></div>
    </div>

    <table>
        <thead>
            <tr>
                <th>{{ __('Date') }}</th>
                <th>{{ __('Type') }}</th>
                <th>{{ __('Description') }}</th>
                <th class="num">{{ __('Debit') }}</th>
                <th class="num">{{ __('Credit') }}</th>
                <th class="num">{{ __('Balance') }}</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>{{ $range[0]?->format('Y-m-d') ?? '' }}</td>
                <td colspan="2">{{ __('Opening balance') }}</td>
                <td class="num">—</td>
                <td class="num">—</td>
                <td class="num">{{ number_format((float) $opening, 2) }}</td>
            </tr>
            @forelse ($entries as $entry)
                <tr>
                    <td>{{ $entry->entry_date?->format('Y-m-d') }}</td>
                    <td>{{ __($entry->type) }}</td>
                    <td>{{ $entry->description ?: '—' }}</td>
                    <td class="num">{{ (float) $entry->debit > 0 ? number_format((float) $entry->debit, 2) : '—' }}</td>
                    <td class="num">{{ (float) $entry->credit > 0 ? number_format((float) $entry->credit, 2) : '—' }}</td>
                    <td class="num">{{ number_format((float) $entry->running_balance, 2) }}</td>
                </tr>
            @empty
                <tr><td colspan="6" style="text-align:center;color:#64748b">{{ __('No entries in this period.') }}</td></tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <td colspan="3">{{ __('Closing balance') }}</td>
                <td class="num">{{ number_format((float) $entries->sum('debit'), 2) }}</td>
                <td class="num">{{ number_format((float) $entries->sum('credit'), 2) }}</td>
                <td class="num">{{ number_format((float) $closing, 2) }}</td>
            </tr>
        </tfoot>
    </table>
</body>
</html>
