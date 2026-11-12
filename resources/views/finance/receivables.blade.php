@extends('layouts.app')

@section('title', __('Receivables'))
@section('subtitle', __('Suppliers who owe money to you.'))

@section('content')
    @php $money = fn ($value) => \App\Support\Money::format($value); @endphp

    <div class="space-y-6">
        <div class="grid gap-4 sm:grid-cols-2">
            <div class="card card-body">
                <p class="muted">{{ __('Total Receivable') }}</p>
                <p class="stat-value mt-2 text-emerald-600 dark:text-emerald-400">{{ $money($total) }}</p>
            </div>
            <div class="card card-body">
                <p class="muted">{{ __('Suppliers') }}</p>
                <p class="stat-value mt-2">{{ $suppliers->count() }}</p>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <form method="GET" action="{{ route('receivables.index') }}" class="flex flex-wrap items-end gap-3">
                    <div class="min-w-48 flex-1">
                        <label for="q" class="label">{{ __('Search') }}</label>
                        <input type="search" name="q" id="q" value="{{ request('q') }}" placeholder="{{ __('Supplier name') }}" class="input">
                    </div>
                    <button type="submit" class="btn-primary">{{ __('Filter') }}</button>
                    <a href="{{ route('receivables.index') }}" class="btn-secondary">{{ __('Reset') }}</a>
                    <div class="ms-auto flex gap-2">
                        <a href="{{ route('finance.export', ['type' => 'receivables', 'format' => 'pdf']) }}" class="btn-secondary btn-sm">{{ __('PDF') }}</a>
                        <a href="{{ route('finance.export', ['type' => 'receivables', 'format' => 'excel']) }}" class="btn-secondary btn-sm">{{ __('Excel') }}</a>
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
                            <th class="th text-end">{{ __('Receivable') }}</th>
                            <th class="th text-end">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @forelse ($suppliers as $supplier)
                            <tr>
                                <td class="td"><a href="{{ route('khata.show', $supplier) }}" class="link font-medium">{{ $supplier->name }}</a></td>
                                <td class="td">{{ $supplier->agency?->name ?? '—' }}</td>
                                <td class="td">{{ $supplier->salesman?->name ?? '—' }}</td>
                                <td class="td">{{ $supplier->phone ?? '—' }}</td>
                                <td class="td">{{ $supplier->last_transaction_at?->format('d M Y') ?? '—' }}</td>
                                <td class="td text-end font-semibold text-emerald-600 dark:text-emerald-400">{{ $money(abs((float) $supplier->current_balance)) }}</td>
                                <td class="td text-end">
                                    <a href="{{ route('khata.show', $supplier) }}" class="btn-ghost btn-sm">{{ __('Ledger') }}</a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="td py-12 text-center text-slate-500">{{ __('No receivables.') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
