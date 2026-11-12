@extends('layouts.app')

@section('title', __('Suppliers'))
@section('subtitle', __('Manage supplier accounts, khata and balances.'))

@section('content')
    @php $money = fn ($value) => \App\Support\Money::format($value); @endphp

    <div class="space-y-6">
        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <div class="card card-body">
                <p class="muted">{{ __('Total Payable') }}</p>
                <p class="stat-value mt-2 text-rose-600 dark:text-rose-400">{{ $money($totals['payable']) }}</p>
            </div>
            <div class="card card-body">
                <p class="muted">{{ __('Total Receivable') }}</p>
                <p class="stat-value mt-2 text-emerald-600 dark:text-emerald-400">{{ $money($totals['receivable']) }}</p>
            </div>
            <div class="card card-body">
                <p class="muted">{{ __('Active') }}</p>
                <p class="stat-value mt-2">{{ number_format($totals['active']) }}</p>
            </div>
            <div class="card card-body">
                <p class="muted">{{ __('Archived') }}</p>
                <p class="stat-value mt-2">{{ number_format($totals['archived']) }}</p>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <form method="GET" action="{{ route('suppliers.index') }}" class="flex flex-wrap items-end gap-3">
                    <div class="min-w-52 flex-1">
                        <label for="q" class="label">{{ __('Search') }}</label>
                        <input type="search" name="q" id="q" value="{{ request('q') }}" placeholder="{{ __('Search by name, agency or phone') }}" class="input">
                    </div>
                    <div>
                        <label for="agency_id" class="label">{{ __('Agency') }}</label>
                        <select name="agency_id" id="agency_id" class="select">
                            <option value="">{{ __('All agencies') }}</option>
                            @foreach ($agencies as $agency)
                                <option value="{{ $agency->id }}" @selected((string) request('agency_id') === (string) $agency->id)>{{ $agency->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="salesman_id" class="label">{{ __('Salesman') }}</label>
                        <select name="salesman_id" id="salesman_id" class="select">
                            <option value="">{{ __('All salesmen') }}</option>
                            @foreach ($salesmen as $salesman)
                                <option value="{{ $salesman->id }}" @selected((string) request('salesman_id') === (string) $salesman->id)>{{ $salesman->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="status" class="label">{{ __('Status') }}</label>
                        <select name="status" id="status" class="select">
                            <option value="" @selected(! request('status'))>{{ __('All Status') }}</option>
                            <option value="active" @selected(request('status') === 'active')>{{ __('Active') }}</option>
                            <option value="archived" @selected(request('status') === 'archived')>{{ __('Archived') }}</option>
                        </select>
                    </div>
                    <div class="flex gap-2">
                        <button type="submit" class="btn-primary">{{ __('Filter') }}</button>
                        <a href="{{ route('suppliers.index') }}" class="btn-secondary">{{ __('Reset') }}</a>
                    </div>
                    @can('suppliers.manage')
                        <a href="{{ route('suppliers.create') }}" class="btn-primary ms-auto">{{ __('Add Supplier') }}</a>
                    @endcan
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
                            <th class="th text-end">{{ __('Balance') }}</th>
                            <th class="th">{{ __('Status') }}</th>
                            <th class="th text-end">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @forelse ($suppliers as $supplier)
                            @php
                                $balance = (float) $supplier->current_balance;
                            @endphp
                            <tr>
                                <td class="td">
                                    <a href="{{ route('suppliers.show', $supplier) }}" class="link">{{ $supplier->name }}</a>
                                    @if ($supplier->address)
                                        <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">{{ $supplier->address }}</p>
                                    @endif
                                </td>
                                <td class="td">{{ $supplier->agency?->name ?? '—' }}</td>
                                <td class="td">{{ $supplier->salesman?->name ?? '—' }}</td>
                                <td class="td">{{ $supplier->phone ?? '—' }}</td>
                                <td class="td text-end">
                                    @if ($balance > 0)
                                        <span class="font-semibold text-rose-600 dark:text-rose-400">{{ $money($balance) }}</span>
                                        <p class="text-xs text-slate-500">{{ __('Payable') }}</p>
                                    @elseif ($balance < 0)
                                        <span class="font-semibold text-emerald-600 dark:text-emerald-400">{{ $money(abs($balance)) }}</span>
                                        <p class="text-xs text-slate-500">{{ __('Receivable') }}</p>
                                    @else
                                        <span class="badge-neutral">{{ __('Settled') }}</span>
                                    @endif
                                </td>
                                <td class="td">
                                    @if ($supplier->archived_at)
                                        <span class="badge-neutral">{{ __('Archived') }}</span>
                                    @elseif ($supplier->is_active)
                                        <span class="badge-success">{{ __('Active') }}</span>
                                    @else
                                        <span class="badge-warning">{{ __('Inactive') }}</span>
                                    @endif
                                </td>
                                <td class="td">
                                    <div class="flex items-center justify-end gap-1">
                                        <a href="{{ route('suppliers.show', $supplier) }}" class="btn-ghost btn-sm">{{ __('View') }}</a>
                                        @can('ledger.view')
                                            <a href="{{ route('khata.show', $supplier) }}" class="btn-ghost btn-sm">{{ __('View Khata') }}</a>
                                        @endcan
                                        @can('suppliers.manage')
                                            <a href="{{ route('suppliers.edit', $supplier) }}" class="btn-ghost btn-sm">{{ __('Edit') }}</a>
                                        @endcan
                                        @can('suppliers.delete')
                                            @if ($supplier->archived_at)
                                                <form method="POST" action="{{ route('suppliers.restore', $supplier) }}">
                                                    @csrf
                                                    <button type="submit" class="btn-ghost btn-sm text-emerald-600 dark:text-emerald-400">{{ __('Restore') }}</button>
                                                </form>
                                            @else
                                                <form method="POST" action="{{ route('suppliers.archive', $supplier) }}" data-confirm="{{ __('Archive this account? Financial history will be preserved.') }}">
                                                    @csrf
                                                    <button type="submit" class="btn-ghost btn-sm text-amber-600 dark:text-amber-400">{{ __('Archive') }}</button>
                                                </form>
                                            @endif
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="td py-12 text-center">
                                    <p class="text-slate-500 dark:text-slate-400">{{ __('No suppliers yet. Add your first supplier to begin.') }}</p>
                                    @can('suppliers.manage')
                                        <a href="{{ route('suppliers.create') }}" class="btn-primary mt-4">{{ __('Add Supplier') }}</a>
                                    @endcan
                                </td>
                            </tr>
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
