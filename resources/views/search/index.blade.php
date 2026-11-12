@extends('layouts.app')

@section('title', __('Search'))
@section('subtitle', __('Search suppliers, purchases, payments and products.'))

@section('content')
    @php $money = fn ($value) => \App\Support\Money::format($value); @endphp

    <div class="space-y-6">
        <div class="card">
            <div class="card-body">
                <form method="GET" action="{{ route('search') }}" class="flex flex-wrap items-end gap-3">
                    <div class="min-w-64 flex-1">
                        <label for="q" class="label">{{ __('Search') }}</label>
                        <input type="search" name="q" id="q" value="{{ $q }}" placeholder="{{ __('Name, phone, invoice, reference, product...') }}" class="input" autofocus>
                    </div>
                    <button type="submit" class="btn-primary">{{ __('Search') }}</button>
                    @if ($q !== '')
                        <a href="{{ route('search') }}" class="btn-secondary">{{ __('Clear') }}</a>
                    @endif
                </form>
            </div>
        </div>

        @if ($q === '')
            <div class="card card-body text-center text-slate-500">{{ __('Enter a search term to begin.') }}</div>
        @else
            @php $hasResults = $results['suppliers']->isNotEmpty() || $results['purchases']->isNotEmpty() || $results['payments']->isNotEmpty() || $results['products']->isNotEmpty(); @endphp

            @if (! $hasResults)
                <div class="card card-body text-center text-slate-500">{{ __('No results for') }} "{{ $q }}".</div>
            @endif

            @if ($results['suppliers']->isNotEmpty())
                <div class="card overflow-hidden">
                    <div class="border-b border-slate-200 px-5 py-4 dark:border-slate-800">
                        <h2 class="section-title">{{ __('Suppliers') }}</h2>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="table">
                            <thead class="bg-slate-50 dark:bg-slate-800/50">
                                <tr>
                                    <th class="th">{{ __('Name') }}</th>
                                    <th class="th">{{ __('Agency') }}</th>
                                    <th class="th">{{ __('Phone') }}</th>
                                    <th class="th text-end">{{ __('Balance') }}</th>
                                    <th class="th text-end">{{ __('Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                                @foreach ($results['suppliers'] as $supplier)
                                    <tr>
                                        <td class="td font-medium">{{ $supplier->name }}</td>
                                        <td class="td">{{ $supplier->agency?->name ?? '—' }}</td>
                                        <td class="td">{{ $supplier->phone ?? '—' }}</td>
                                        <td class="td text-end">{{ $money($supplier->current_balance) }}</td>
                                        <td class="td text-end">
                                            <a href="{{ route('suppliers.show', $supplier) }}" class="btn-ghost btn-sm">{{ __('View') }}</a>
                                            <a href="{{ route('khata.show', $supplier) }}" class="btn-ghost btn-sm">{{ __('Ledger') }}</a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            @if ($results['purchases']->isNotEmpty())
                <div class="card overflow-hidden">
                    <div class="border-b border-slate-200 px-5 py-4 dark:border-slate-800">
                        <h2 class="section-title">{{ __('Purchases') }}</h2>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="table">
                            <thead class="bg-slate-50 dark:bg-slate-800/50">
                                <tr>
                                    <th class="th">{{ __('Invoice') }}</th>
                                    <th class="th">{{ __('Supplier') }}</th>
                                    <th class="th">{{ __('Date') }}</th>
                                    <th class="th text-end">{{ __('Total') }}</th>
                                    <th class="th text-end">{{ __('Remaining') }}</th>
                                    <th class="th text-end">{{ __('Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                                @foreach ($results['purchases'] as $purchase)
                                    <tr>
                                        <td class="td font-medium">{{ $purchase->invoice_no }}</td>
                                        <td class="td">{{ $purchase->supplier?->name ?? '—' }}</td>
                                        <td class="td">{{ $purchase->purchase_date?->format('d M Y') }}</td>
                                        <td class="td text-end">{{ $money($purchase->total) }}</td>
                                        <td class="td text-end">{{ $money($purchase->remaining_amount) }}</td>
                                        <td class="td text-end"><a href="{{ route('purchases.show', $purchase) }}" class="btn-ghost btn-sm">{{ __('View') }}</a></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            @if ($results['payments']->isNotEmpty())
                <div class="card overflow-hidden">
                    <div class="border-b border-slate-200 px-5 py-4 dark:border-slate-800">
                        <h2 class="section-title">{{ __('Payments') }}</h2>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="table">
                            <thead class="bg-slate-50 dark:bg-slate-800/50">
                                <tr>
                                    <th class="th">{{ __('Date') }}</th>
                                    <th class="th">{{ __('Supplier') }}</th>
                                    <th class="th">{{ __('Method') }}</th>
                                    <th class="th">{{ __('Reference') }}</th>
                                    <th class="th text-end">{{ __('Amount') }}</th>
                                    <th class="th text-end">{{ __('Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                                @foreach ($results['payments'] as $payment)
                                    <tr>
                                        <td class="td">{{ $payment->payment_date?->format('d M Y') }}</td>
                                        <td class="td">{{ $payment->supplier?->name ?? '—' }}</td>
                                        <td class="td">{{ __(ucfirst($payment->method)) }}</td>
                                        <td class="td">{{ $payment->reference_no ?? '—' }}</td>
                                        <td class="td text-end font-medium text-emerald-600 dark:text-emerald-400">{{ $money($payment->amount) }}</td>
                                        <td class="td text-end"><a href="{{ route('payments.show', $payment) }}" class="btn-ghost btn-sm">{{ __('View') }}</a></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            @if ($results['products']->isNotEmpty())
                <div class="card overflow-hidden">
                    <div class="border-b border-slate-200 px-5 py-4 dark:border-slate-800">
                        <h2 class="section-title">{{ __('Products') }}</h2>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="table">
                            <thead class="bg-slate-50 dark:bg-slate-800/50">
                                <tr>
                                    <th class="th">{{ __('Name') }}</th>
                                    <th class="th">{{ __('SKU') }}</th>
                                    <th class="th">{{ __('Unit') }}</th>
                                    <th class="th text-end">{{ __('Purchase Rate') }}</th>
                                    <th class="th text-end">{{ __('Stock') }}</th>
                                    <th class="th text-end">{{ __('Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                                @foreach ($results['products'] as $product)
                                    <tr>
                                        <td class="td font-medium">{{ $product->name }}</td>
                                        <td class="td">{{ $product->sku ?? '—' }}</td>
                                        <td class="td">{{ $product->unit }}</td>
                                        <td class="td text-end">{{ $money($product->purchase_rate) }}</td>
                                        <td class="td text-end">{{ (float) $product->stock_qty }}</td>
                                        <td class="td text-end">
                                            @can('products.manage')
                                                <a href="{{ route('products.edit', $product) }}" class="btn-ghost btn-sm">{{ __('Edit') }}</a>
                                            @endcan
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        @endif
    </div>
@endsection
