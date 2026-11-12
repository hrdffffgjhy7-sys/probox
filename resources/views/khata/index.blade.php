@extends('layouts.app')

@section('title', __('Khata'))
@section('subtitle', __('Supplier ledger balances and period activity.'))

@section('content')
    @php $money = fn ($value) => \App\Support\Money::format($value); @endphp

    <div class="space-y-6">
        <div class="grid gap-4 sm:grid-cols-2">
            <div class="card card-body">
                <p class="muted">{{ __('Purchases in period') }}</p>
                <p class="stat-value mt-2">{{ $money($periodStats['purchases']) }}</p>
            </div>
            <div class="card card-body">
                <p class="muted">{{ __('Payments in period') }}</p>
                <p class="stat-value mt-2 text-emerald-600 dark:text-emerald-400">{{ $money($periodStats['payments']) }}</p>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <form method="GET" action="{{ route('khata.index') }}" class="flex flex-wrap items-end gap-3">
                    <div class="min-w-48 flex-1">
                        <label for="q" class="label">{{ __('Search') }}</label>
                        <input type="search" name="q" id="q" value="{{ request('q') }}" placeholder="{{ __('Name or phone') }}" class="input">
                    </div>
                    <div>
                        <label for="status" class="label">{{ __('Status') }}</label>
                        <select name="status" id="status" class="select">
                            <option value="active" @selected(! request('status') || request('status') === 'active')>{{ __('Active') }}</option>
                            <option value="archived" @selected(request('status') === 'archived')>{{ __('Archived') }}</option>
                        </select>
                    </div>
                    @include('partials.date-range')
                    <div class="flex gap-2">
                        <button type="submit" class="btn-primary">{{ __('Filter') }}</button>
                        <a href="{{ route('khata.index') }}" class="btn-secondary">{{ __('Reset') }}</a>
                    </div>
                    <div class="ms-auto flex gap-2">
                        @can('ledger.view')
                            <a href="{{ route('khata.adjustments') }}" class="btn-secondary btn-sm">{{ __('Adjustments') }}</a>
                        @endcan
                        @can('purchases.view')
                            <a href="{{ route('khata.returns') }}" class="btn-secondary btn-sm">{{ __('Returns') }}</a>
                        @endcan
                    </div>
                </form>
            </div>
        </div>

        <div class="card overflow-hidden">
            <div class="overflow-x-auto">
                <table class="table">
                    <thead class="bg-slate-50 dark:bg-slate-800/50">
                        <tr>
                            <th class="th">{{ __('Supplier') }}</th>
                            <th class="th">{{ __('Agency') }}</th>
                            <th class="th">{{ __('Salesman') }}</th>
                            <th class="th">{{ __('Phone') }}</th>
                            <th class="th">{{ __('Last Transaction') }}</th>
                            <th class="th text-end">{{ __('Balance') }}</th>
                            <th class="th text-end">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @forelse ($suppliers as $supplier)
                            <tr>
                                <td class="td">
                                    <a href="{{ route('khata.show', $supplier) }}" class="link font-medium">{{ $supplier->name }}</a>
                                    @if ($supplier->archived_at)
                                        <span class="badge-neutral ms-2">{{ __('Archived') }}</span>
                                    @endif
                                </td>
                                <td class="td">{{ $supplier->agency?->name ?? '—' }}</td>
                                <td class="td">{{ $supplier->salesman?->name ?? '—' }}</td>
                                <td class="td">{{ $supplier->phone ?? '—' }}</td>
                                <td class="td">{{ $supplier->last_transaction_at?->format('d M Y') ?? '—' }}</td>
                                <td class="td text-end font-semibold {{ (float) $supplier->current_balance > 0 ? 'text-rose-600 dark:text-rose-400' : ((float) $supplier->current_balance < 0 ? 'text-emerald-600 dark:text-emerald-400' : '') }}">
                                    {{ $money(abs((float) $supplier->current_balance)) }}
                                    <span class="block text-xs font-normal text-slate-400">
                                        {{ (float) $supplier->current_balance > 0 ? __('Payable') : ((float) $supplier->current_balance < 0 ? __('Receivable') : __('Settled')) }}
                                    </span>
                                </td>
                                <td class="td text-end">
                                    <a href="{{ route('khata.show', $supplier) }}" class="btn-ghost btn-sm">{{ __('Ledger') }}</a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="td py-12 text-center text-slate-500">{{ __('No suppliers found.') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($suppliers->hasPages())
                <div class="border-t border-slate-200 px-4 py-3 dark:border-slate-800">
                    {{ $suppliers->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
