@extends('layouts.app')

@section('title', __('Dashboard'))

@section('content')
    @php
        $currency = \App\Support\BusinessSettings::all()['currency'];
        $money = fn ($value) => \App\Support\Money::format($value);
        $chartMax = max(1, collect($chart['purchases'])->merge($chart['payments'])->max() ?? 1);
    @endphp

    <div class="space-y-6">
        <section class="flex flex-col gap-4 rounded-2xl bg-gradient-to-r from-emerald-600 to-teal-600 px-6 py-6 text-white shadow-sm sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-sm font-medium text-emerald-100">{{ __('Welcome back') }}, {{ auth()->user()->name }}</p>
                <h2 class="mt-1 text-2xl font-bold">{{ __('Here is what is happening in your store today.') }}</h2>
                <p class="mt-2 text-sm text-emerald-100">{{ __('Today') }}: {{ $cards['today_count'] }} {{ __('Transactions') }}</p>
            </div>
            <div class="flex flex-wrap gap-2">
                @can('purchases.manage')
                    <a href="{{ route('purchases.create') }}" class="btn bg-white text-emerald-700 hover:bg-emerald-50">{{ __('Add Purchase') }}</a>
                @endcan
                @can('payments.manage')
                    <a href="{{ route('payments.create') }}" class="btn border border-white/40 bg-white/10 text-white hover:bg-white/20">{{ __('Add Payment') }}</a>
                @endcan
                @can('suppliers.manage')
                    <a href="{{ route('suppliers.create') }}" class="btn border border-white/40 bg-white/10 text-white hover:bg-white/20">{{ __('Add Supplier') }}</a>
                @endcan
            </div>
        </section>

        @unless ($dbHealthy)
            <div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800 dark:border-rose-500/30 dark:bg-rose-500/10 dark:text-rose-200">
                {{ __('Database connection is not healthy. Please check your configuration.') }}
            </div>
        @endunless

        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <div class="card card-body">
                <div class="flex items-start justify-between">
                    <p class="muted">{{ __('Total Outstanding Payable') }}</p>
                    <span class="badge-danger">{{ __('Payable') }}</span>
                </div>
                <p class="stat-value mt-3 text-rose-600 dark:text-rose-400">{{ $money($cards['payable']) }}</p>
                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ __('we will pay') }}</p>
            </div>
            <div class="card card-body">
                <div class="flex items-start justify-between">
                    <p class="muted">{{ __('Total Outstanding Receivable') }}</p>
                    <span class="badge-success">{{ __('Receivable') }}</span>
                </div>
                <p class="stat-value mt-3 text-emerald-600 dark:text-emerald-400">{{ $money($cards['receivable']) }}</p>
                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ __('we will receive') }}</p>
            </div>
            <div class="card card-body">
                <div class="flex items-start justify-between">
                    <p class="muted">{{ __('Net Outstanding Position') }}</p>
                    <span class="badge-info">{{ __('Net') }}</span>
                </div>
                <p class="stat-value mt-3 {{ $cards['net'] >= 0 ? 'text-rose-600 dark:text-rose-400' : 'text-emerald-600 dark:text-emerald-400' }}">{{ $money(abs($cards['net'])) }}</p>
                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ $cards['net'] >= 0 ? __('Payable') : __('Receivable') }}</p>
            </div>
            <div class="card card-body">
                <div class="flex items-start justify-between">
                    <p class="muted">{{ __('Overdue Balances') }}</p>
                    <span class="badge-warning">{{ __('Overdue') }}</span>
                </div>
                <p class="stat-value mt-3 text-amber-600 dark:text-amber-400">{{ $money($cards['overdue']) }}</p>
                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ __('Pending Payments') }}: {{ $money($cards['pending']) }}</p>
            </div>
        </section>

        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <div class="card card-body">
                <p class="muted">{{ __('Total Suppliers') }}</p>
                <p class="stat-value mt-2">{{ number_format($cards['suppliers']) }}</p>
            </div>
            <div class="card card-body">
                <p class="muted">{{ __('Total Salesmen') }}</p>
                <p class="stat-value mt-2">{{ number_format($cards['salesmen']) }}</p>
            </div>
            <div class="card card-body">
                <p class="muted">{{ __('Agencies') }}</p>
                <p class="stat-value mt-2">{{ number_format($cards['agencies']) }}</p>
            </div>
            <div class="card card-body">
                <p class="muted">{{ __('Today\'s Transactions') }}</p>
                <p class="stat-value mt-2">{{ number_format($cards['today_count']) }}</p>
            </div>
        </section>

        <div class="grid gap-6 xl:grid-cols-3">
            <section class="card card-body xl:col-span-2">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="section-title">{{ __('Monthly Overview') }}</h2>
                        <p class="muted">{{ __('Purchases This Month') }}: {{ $money($cards['month_purchases']) }} &middot; {{ __('Payments This Month') }}: {{ $money($cards['month_payments']) }}</p>
                    </div>
                    <div class="flex items-center gap-4 text-xs">
                        <span class="flex items-center gap-1.5"><span class="h-2.5 w-2.5 rounded-full bg-emerald-500"></span>{{ __('Purchases') }}</span>
                        <span class="flex items-center gap-1.5"><span class="h-2.5 w-2.5 rounded-full bg-sky-500"></span>{{ __('Payments') }}</span>
                    </div>
                </div>

                <div class="mt-8 flex h-56 items-end gap-3 sm:gap-5">
                    @foreach ($chart['labels'] as $i => $label)
                        @php
                            $purchaseHeight = max(2, ($chart['purchases'][$i] / $chartMax) * 100);
                            $paymentHeight = max(2, ($chart['payments'][$i] / $chartMax) * 100);
                        @endphp
                        <div class="flex flex-1 flex-col items-center gap-2">
                            <div class="flex h-44 w-full items-end justify-center gap-1">
                                <div class="w-1/2 max-w-6 rounded-t-md bg-emerald-500/90" style="height: {{ $purchaseHeight }}%" title="{{ __('Purchases') }}: {{ $money($chart['purchases'][$i]) }}"></div>
                                <div class="w-1/2 max-w-6 rounded-t-md bg-sky-500/90" style="height: {{ $paymentHeight }}%" title="{{ __('Payments') }}: {{ $money($chart['payments'][$i]) }}"></div>
                            </div>
                            <span class="text-[0.65rem] font-medium text-slate-500 dark:text-slate-400">{{ $label }}</span>
                        </div>
                    @endforeach
                </div>
            </section>

            <section class="card card-body">
                <h2 class="section-title">{{ __('Quick actions') }}</h2>
                <div class="mt-4 grid gap-2">
                    @can('purchases.manage')
                        <a href="{{ route('purchases.create') }}" class="flex items-center justify-between rounded-xl border border-slate-200 px-4 py-3 text-sm font-medium text-slate-700 transition hover:border-emerald-300 hover:bg-emerald-50 dark:border-slate-700 dark:text-slate-200 dark:hover:border-emerald-500/40 dark:hover:bg-emerald-500/10">
                            {{ __('New Purchase') }} <span class="text-emerald-600 dark:text-emerald-400">&rarr;</span>
                        </a>
                    @endcan
                    @can('payments.manage')
                        <a href="{{ route('payments.create') }}" class="flex items-center justify-between rounded-xl border border-slate-200 px-4 py-3 text-sm font-medium text-slate-700 transition hover:border-emerald-300 hover:bg-emerald-50 dark:border-slate-700 dark:text-slate-200 dark:hover:border-emerald-500/40 dark:hover:bg-emerald-500/10">
                            {{ __('New Payment') }} <span class="text-emerald-600 dark:text-emerald-400">&rarr;</span>
                        </a>
                    @endcan
                    @can('expenses.manage')
                        <a href="{{ route('expenses.create') }}" class="flex items-center justify-between rounded-xl border border-slate-200 px-4 py-3 text-sm font-medium text-slate-700 transition hover:border-emerald-300 hover:bg-emerald-50 dark:border-slate-700 dark:text-slate-200 dark:hover:border-emerald-500/40 dark:hover:bg-emerald-500/10">
                            {{ __('New Expense') }} <span class="text-emerald-600 dark:text-emerald-400">&rarr;</span>
                        </a>
                    @endcan
                    @can('suppliers.manage')
                        <a href="{{ route('suppliers.create') }}" class="flex items-center justify-between rounded-xl border border-slate-200 px-4 py-3 text-sm font-medium text-slate-700 transition hover:border-emerald-300 hover:bg-emerald-50 dark:border-slate-700 dark:text-slate-200 dark:hover:border-emerald-500/40 dark:hover:bg-emerald-500/10">
                            {{ __('Add Supplier') }} <span class="text-emerald-600 dark:text-emerald-400">&rarr;</span>
                        </a>
                    @endcan
                    @can('reports.view')
                        <a href="{{ route('reports.index') }}" class="flex items-center justify-between rounded-xl border border-slate-200 px-4 py-3 text-sm font-medium text-slate-700 transition hover:border-emerald-300 hover:bg-emerald-50 dark:border-slate-700 dark:text-slate-200 dark:hover:border-emerald-500/40 dark:hover:bg-emerald-500/10">
                            {{ __('Reports') }} <span class="text-emerald-600 dark:text-emerald-400">&rarr;</span>
                        </a>
                    @endcan
                </div>
            </section>
        </div>

        <div class="grid gap-6 xl:grid-cols-2">
            <section class="card">
                <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4 dark:border-slate-800">
                    <h2 class="section-title">{{ __('Top Suppliers by Payable') }}</h2>
                    @can('payables.view')
                        <a href="{{ route('payables.index') }}" class="link text-sm">{{ __('View all') }}</a>
                    @endcan
                </div>
                <div class="overflow-x-auto">
                    <table class="table">
                        <thead class="bg-slate-50 dark:bg-slate-800/50">
                            <tr>
                                <th class="th">{{ __('Supplier') }}</th>
                                <th class="th">{{ __('Agency') }}</th>
                                <th class="th text-end">{{ __('Balance') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            @forelse ($topSuppliers as $supplier)
                                <tr>
                                    <td class="td">
                                        <a href="{{ route('suppliers.show', $supplier) }}" class="link">{{ $supplier->name }}</a>
                                    </td>
                                    <td class="td">{{ $supplier->agency?->name ?? '—' }}</td>
                                    <td class="td text-end font-semibold text-rose-600 dark:text-rose-400">{{ $money($supplier->current_balance) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="td py-8 text-center text-slate-500">{{ __('No records found') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>

            <section class="card">
                <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4 dark:border-slate-800">
                    <h2 class="section-title">{{ __('Overdue Balances') }}</h2>
                    @can('purchases.view')
                        <a href="{{ route('purchases.index', ['status' => 'overdue']) }}" class="link text-sm">{{ __('View all') }}</a>
                    @endcan
                </div>
                <div class="overflow-x-auto">
                    <table class="table">
                        <thead class="bg-slate-50 dark:bg-slate-800/50">
                            <tr>
                                <th class="th">{{ __('Invoice') }}</th>
                                <th class="th">{{ __('Supplier') }}</th>
                                <th class="th">{{ __('Due Date') }}</th>
                                <th class="th text-end">{{ __('Remaining') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            @forelse ($overduePurchases as $purchase)
                                <tr>
                                    <td class="td"><a href="{{ route('purchases.show', $purchase) }}" class="link">{{ $purchase->invoice_no }}</a></td>
                                    <td class="td">{{ $purchase->supplier?->name }}</td>
                                    <td class="td">{{ $purchase->due_date?->format('d M Y') }}</td>
                                    <td class="td text-end font-semibold text-amber-600 dark:text-amber-400">{{ $money($purchase->remaining_amount) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="td py-8 text-center text-slate-500">{{ __('No records found') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
        </div>

        <div class="grid gap-6 xl:grid-cols-3">
            <section class="card xl:col-span-2">
                <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4 dark:border-slate-800">
                    <h2 class="section-title">{{ __('Recent Purchases') }}</h2>
                    @can('purchases.view')
                        <a href="{{ route('purchases.index') }}" class="link text-sm">{{ __('View all') }}</a>
                    @endcan
                </div>
                <div class="overflow-x-auto">
                    <table class="table">
                        <thead class="bg-slate-50 dark:bg-slate-800/50">
                            <tr>
                                <th class="th">{{ __('Date') }}</th>
                                <th class="th">{{ __('Invoice') }}</th>
                                <th class="th">{{ __('Supplier') }}</th>
                                <th class="th text-end">{{ __('Total') }}</th>
                                <th class="th text-end">{{ __('Remaining') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            @forelse ($recentPurchases as $purchase)
                                <tr>
                                    <td class="td">{{ $purchase->purchase_date?->format('d M Y') }}</td>
                                    <td class="td"><a href="{{ route('purchases.show', $purchase) }}" class="link">{{ $purchase->invoice_no }}</a></td>
                                    <td class="td">{{ $purchase->supplier?->name }}</td>
                                    <td class="td text-end">{{ $money($purchase->total) }}</td>
                                    <td class="td text-end">{{ $money($purchase->remaining_amount) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="td py-8 text-center text-slate-500">{{ __('No purchases yet.') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>

            <section class="card">
                <div class="border-b border-slate-200 px-5 py-4 dark:border-slate-800">
                    <h2 class="section-title">{{ __('Recent Activities') }}</h2>
                </div>
                <div class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse ($recentActivities as $activity)
                        <div class="px-5 py-3">
                            <p class="text-sm font-medium text-slate-800 dark:text-slate-100">{{ $activity->description }}</p>
                            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                                {{ $activity->user?->name ?? __('System') }} &middot; {{ $activity->created_at?->diffForHumans() }}
                            </p>
                        </div>
                    @empty
                        <p class="px-5 py-8 text-center text-sm text-slate-500 dark:text-slate-400">{{ __('No activity yet') }}</p>
                    @endforelse
                </div>
            </section>
        </div>

        <div class="grid gap-6 xl:grid-cols-2">
            <section class="card">
                <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4 dark:border-slate-800">
                    <h2 class="section-title">{{ __('Recent Payments') }}</h2>
                    @can('payments.view')
                        <a href="{{ route('payments.index') }}" class="link text-sm">{{ __('View all') }}</a>
                    @endcan
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
                            @forelse ($recentPayments as $payment)
                                <tr>
                                    <td class="td">{{ $payment->payment_date?->format('d M Y') }}</td>
                                    <td class="td"><a href="{{ route('payments.show', $payment) }}" class="link">{{ $payment->supplier?->name }}</a></td>
                                    <td class="td">{{ __($payment->method) }}</td>
                                    <td class="td text-end font-semibold text-emerald-600 dark:text-emerald-400">{{ $money($payment->amount) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="td py-8 text-center text-slate-500">{{ __('No payments yet.') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>

            <section class="card">
                <div class="border-b border-slate-200 px-5 py-4 dark:border-slate-800">
                    <h2 class="section-title">{{ __('Notifications') }}</h2>
                </div>
                <div class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse ($notifications as $notification)
                        <div class="px-5 py-3">
                            <p class="text-sm font-medium text-slate-800 dark:text-slate-100">{{ $notification->title }}</p>
                            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ $notification->body }}</p>
                        </div>
                    @empty
                        <p class="px-5 py-8 text-center text-sm text-slate-500 dark:text-slate-400">{{ __('No notifications') }}</p>
                    @endforelse
                </div>
            </section>
        </div>
    </div>
@endsection
