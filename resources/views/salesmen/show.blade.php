@extends('layouts.app')

@section('title', $salesman->name)
@section('subtitle', __('Salesman details'))

@section('content')
    @php $money = fn ($value) => \App\Support\Money::format($value); @endphp

    <div class="space-y-6">
        <div class="card card-body">
            <div class="flex flex-col gap-5 lg:flex-row lg:items-start lg:justify-between">
                <div class="flex items-start gap-4">
                    <span class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-emerald-100 text-xl font-bold text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300">
                        {{ mb_strtoupper(mb_substr($salesman->name, 0, 1)) }}
                    </span>
                    <div>
                        <div class="flex flex-wrap items-center gap-2">
                            <h2 class="text-xl font-bold text-slate-900 dark:text-white">{{ $salesman->name }}</h2>
                            @if ($salesman->is_active)
                                <span class="badge-success">{{ __('Active') }}</span>
                            @else
                                <span class="badge-neutral">{{ __('Inactive') }}</span>
                            @endif
                        </div>
                        <div class="mt-2 flex flex-wrap gap-x-5 gap-y-1 text-sm text-slate-500 dark:text-slate-400">
                            <span>{{ __('Agency') }}: <span class="text-slate-700 dark:text-slate-200">{{ $salesman->agency?->name ?? '—' }}</span></span>
                            <span>{{ __('Phone') }}: <span class="text-slate-700 dark:text-slate-200">{{ $salesman->phone ?? '—' }}</span></span>
                            <span>{{ __('WhatsApp') }}: <span class="text-slate-700 dark:text-slate-200">{{ $salesman->whatsapp ?? '—' }}</span></span>
                        </div>
                        @if ($salesman->address)
                            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ $salesman->address }}</p>
                        @endif
                    </div>
                </div>

                <div class="flex flex-wrap gap-2">
                    @can('salesmen.manage')
                        <a href="{{ route('salesmen.edit', $salesman) }}" class="btn-secondary btn-sm">{{ __('Edit') }}</a>
                    @endcan
                    <a href="{{ route('salesmen.index') }}" class="btn-ghost btn-sm">{{ __('Back to salesmen') }}</a>
                </div>
            </div>
        </div>

        <div class="grid gap-4 sm:grid-cols-3">
            <div class="card card-body">
                <p class="muted">{{ __('Total Purchases') }}</p>
                <p class="stat-value mt-2">{{ $money($totals['purchases']) }}</p>
            </div>
            <div class="card card-body">
                <p class="muted">{{ __('Total Payments') }}</p>
                <p class="stat-value mt-2 text-emerald-600 dark:text-emerald-400">{{ $money($totals['payments']) }}</p>
            </div>
            <div class="card card-body">
                <p class="muted">{{ __('Outstanding') }}</p>
                <p class="stat-value mt-2 text-rose-600 dark:text-rose-400">{{ $money($totals['outstanding']) }}</p>
            </div>
        </div>

        <div class="card">
            <div class="border-b border-slate-200 px-5 py-4 dark:border-slate-800">
                <h2 class="section-title">{{ __('Linked Suppliers') }}</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="table">
                    <thead class="bg-slate-50 dark:bg-slate-800/50">
                        <tr>
                            <th class="th">{{ __('Supplier') }}</th>
                            <th class="th">{{ __('Agency') }}</th>
                            <th class="th">{{ __('Phone') }}</th>
                            <th class="th text-end">{{ __('Balance') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @forelse ($salesman->suppliers as $supplier)
                            <tr>
                                <td class="td"><a href="{{ route('suppliers.show', $supplier) }}" class="link">{{ $supplier->name }}</a></td>
                                <td class="td">{{ $supplier->agency?->name ?? '—' }}</td>
                                <td class="td">{{ $supplier->phone ?? '—' }}</td>
                                <td class="td text-end font-medium {{ (float) $supplier->current_balance > 0 ? 'text-rose-600 dark:text-rose-400' : 'text-emerald-600 dark:text-emerald-400' }}">{{ $money($supplier->current_balance) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="td py-8 text-center text-slate-500">{{ __('No suppliers linked.') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="grid gap-6 xl:grid-cols-2">
            <div class="card">
                <div class="border-b border-slate-200 px-5 py-4 dark:border-slate-800">
                    <h2 class="section-title">{{ __('Recent Purchases') }}</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="table">
                        <thead class="bg-slate-50 dark:bg-slate-800/50">
                            <tr>
                                <th class="th">{{ __('Date') }}</th>
                                <th class="th">{{ __('Invoice') }}</th>
                                <th class="th">{{ __('Supplier') }}</th>
                                <th class="th text-end">{{ __('Total') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            @forelse ($purchases as $purchase)
                                <tr>
                                    <td class="td">{{ $purchase->purchase_date?->format('d M Y') }}</td>
                                    <td class="td"><a href="{{ route('purchases.show', $purchase) }}" class="link">{{ $purchase->invoice_no }}</a></td>
                                    <td class="td">{{ $purchase->supplier?->name }}</td>
                                    <td class="td text-end">{{ $money($purchase->total) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="td py-8 text-center text-slate-500">{{ __('No purchases yet.') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card">
                <div class="border-b border-slate-200 px-5 py-4 dark:border-slate-800">
                    <h2 class="section-title">{{ __('Recent Payments') }}</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="table">
                        <thead class="bg-slate-50 dark:bg-slate-800/50">
                            <tr>
                                <th class="th">{{ __('Date') }}</th>
                                <th class="th">{{ __('Supplier') }}</th>
                                <th class="th">{{ __('Method') }}</th>
                                <th class="th text-end">{{ __('Amount') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            @forelse ($payments as $payment)
                                <tr>
                                    <td class="td">{{ $payment->payment_date?->format('d M Y') }}</td>
                                    <td class="td">{{ $payment->supplier?->name }}</td>
                                    <td class="td">{{ __($payment->method) }}</td>
                                    <td class="td text-end font-medium text-emerald-600 dark:text-emerald-400">{{ $money($payment->amount) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="td py-8 text-center text-slate-500">{{ __('No payments yet.') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
