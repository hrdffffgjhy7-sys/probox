@extends('layouts.app')

@section('title', __('Purchases'))
@section('subtitle', __('Purchase invoices and outstanding balances.'))

@section('content')
    @php $money = fn ($value) => \App\Support\Money::format($value); @endphp

    <div class="space-y-6">
        <div class="grid gap-4 sm:grid-cols-2">
            <div class="card card-body">
                <p class="muted">{{ __('Purchases in period') }}</p>
                <p class="stat-value mt-2">{{ $money($totals['grand']) }}</p>
            </div>
            <div class="card card-body">
                <p class="muted">{{ __('Total remaining') }}</p>
                <p class="stat-value mt-2 text-rose-600 dark:text-rose-400">{{ $money($totals['remaining']) }}</p>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <form method="GET" action="{{ route('purchases.index') }}" class="flex flex-wrap items-end gap-3">
                    <div class="min-w-48 flex-1">
                        <label for="q" class="label">{{ __('Search') }}</label>
                        <input type="search" name="q" id="q" value="{{ request('q') }}" placeholder="{{ __('Invoice or supplier') }}" class="input">
                    </div>
                    <div>
                        <label for="supplier_id" class="label">{{ __('Supplier') }}</label>
                        <select name="supplier_id" id="supplier_id" class="select">
                            <option value="">{{ __('All suppliers') }}</option>
                            @foreach ($suppliers as $supplier)
                                <option value="{{ $supplier->id }}" @selected((string) request('supplier_id') === (string) $supplier->id)>{{ $supplier->name }}</option>
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
                            <option value="">{{ __('All Status') }}</option>
                            <option value="due" @selected(request('status') === 'due')>{{ __('Due') }}</option>
                            <option value="overdue" @selected(request('status') === 'overdue')>{{ __('Overdue') }}</option>
                            <option value="settled" @selected(request('status') === 'settled')>{{ __('Settled') }}</option>
                        </select>
                    </div>
                    @include('partials.date-range')
                    <div class="flex gap-2">
                        <button type="submit" class="btn-primary">{{ __('Filter') }}</button>
                        <a href="{{ route('purchases.index') }}" class="btn-secondary">{{ __('Reset') }}</a>
                    </div>
                    @can('purchases.manage')
                        <a href="{{ route('purchases.create') }}" class="btn-primary ms-auto">{{ __('Add Purchase') }}</a>
                    @endcan
                </form>
            </div>
        </div>

        <div class="card overflow-hidden">
            <div class="overflow-x-auto">
                <table class="table">
                    <thead class="bg-slate-50 dark:bg-slate-800/50">
                        <tr>
                            <th class="th">{{ __('Date') }}</th>
                            <th class="th">{{ __('Invoice') }}</th>
                            <th class="th">{{ __('Supplier') }}</th>
                            <th class="th">{{ __('Salesman') }}</th>
                            <th class="th text-end">{{ __('Total') }}</th>
                            <th class="th text-end">{{ __('Paid') }}</th>
                            <th class="th text-end">{{ __('Remaining') }}</th>
                            <th class="th">{{ __('Status') }}</th>
                            <th class="th text-end">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @forelse ($purchases as $purchase)
                            <tr>
                                <td class="td">{{ $purchase->purchase_date?->format('d M Y') }}</td>
                                <td class="td"><a href="{{ route('purchases.show', $purchase) }}" class="link">{{ $purchase->invoice_no }}</a></td>
                                <td class="td">{{ $purchase->supplier?->name ?? '—' }}</td>
                                <td class="td">{{ $purchase->salesman?->name ?? '—' }}</td>
                                <td class="td text-end">{{ $money($purchase->total) }}</td>
                                <td class="td text-end text-emerald-600 dark:text-emerald-400">{{ $money($purchase->paid_amount) }}</td>
                                <td class="td text-end font-semibold {{ (float) $purchase->remaining_amount > 0 ? 'text-rose-600 dark:text-rose-400' : 'text-slate-500' }}">{{ $money($purchase->remaining_amount) }}</td>
                                <td class="td">
                                    @if ($purchase->status === 'paid')
                                        <span class="badge-success">{{ __('Paid') }}</span>
                                    @elseif ($purchase->status === 'partial')
                                        <span class="badge-warning">{{ __('Partial') }}</span>
                                    @else
                                        <span class="badge-danger">{{ __('Unpaid') }}</span>
                                    @endif
                                </td>
                                <td class="td">
                                    <div class="flex items-center justify-end gap-1">
                                        <a href="{{ route('purchases.show', $purchase) }}" class="btn-ghost btn-sm">{{ __('View') }}</a>
                                        @can('purchases.manage')
                                            <a href="{{ route('purchases.edit', $purchase) }}" class="btn-ghost btn-sm">{{ __('Edit') }}</a>
                                        @endcan
                                        @can('users.manage')
                                            <form method="POST" action="{{ route('purchases.destroy', $purchase) }}" data-confirm="{{ __('Delete this purchase?') }}">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn-ghost btn-sm text-rose-600 dark:text-rose-400">{{ __('Delete') }}</button>
                                            </form>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="9" class="td py-12 text-center text-slate-500">{{ __('No purchases found.') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($purchases->hasPages())
                <div class="border-t border-slate-200 px-4 py-3 dark:border-slate-800">
                    {{ $purchases->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
