@extends('layouts.app')

@section('title', __('Payments'))
@section('subtitle', __('Payments made to suppliers.'))

@section('content')
    @php $money = fn ($value) => \App\Support\Money::format($value); @endphp

    <div class="space-y-6">
        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
            <div class="card card-body">
                <p class="muted">{{ __('Payments in period') }}</p>
                <p class="stat-value mt-2 text-emerald-600 dark:text-emerald-400">{{ $money($total) }}</p>
            </div>
            <div class="card card-body">
                <p class="muted">{{ __('Records shown') }}</p>
                <p class="stat-value mt-2">{{ number_format($payments->total()) }}</p>
            </div>
            <div class="card card-body">
                <p class="muted">{{ __('Suppliers') }}</p>
                <p class="stat-value mt-2">{{ number_format($suppliers->count()) }}</p>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <form method="GET" action="{{ route('payments.index') }}" class="flex flex-wrap items-end gap-3">
                    <div class="min-w-52 flex-1">
                        <label for="q" class="label">{{ __('Search') }}</label>
                        <input type="search" name="q" id="q" value="{{ request('q') }}" placeholder="{{ __('Reference or supplier') }}" class="input">
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
                        <label for="method" class="label">{{ __('Method') }}</label>
                        <select name="method" id="method" class="select">
                            <option value="">{{ __('All methods') }}</option>
                            @foreach (['cash' => __('Cash'), 'bank' => __('Bank'), 'cheque' => __('Cheque'), 'online' => __('Online'), 'other' => __('Other')] as $value => $label)
                                <option value="{{ $value }}" @selected(request('method') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    @include('partials.date-range')
                    <div class="flex gap-2">
                        <button type="submit" class="btn-primary">{{ __('Filter') }}</button>
                        <a href="{{ route('payments.index') }}" class="btn-secondary">{{ __('Reset') }}</a>
                    </div>
                    @can('payments.manage')
                        <a href="{{ route('payments.create') }}" class="btn-primary ms-auto">{{ __('Add Payment') }}</a>
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
                            <th class="th">{{ __('Supplier') }}</th>
                            <th class="th">{{ __('Purchase') }}</th>
                            <th class="th">{{ __('Method') }}</th>
                            <th class="th">{{ __('Reference') }}</th>
                            <th class="th text-end">{{ __('Amount') }}</th>
                            <th class="th">{{ __('Receipt') }}</th>
                            <th class="th text-end">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @forelse ($payments as $payment)
                            <tr>
                                <td class="td">{{ $payment->payment_date?->format('d M Y') }}</td>
                                <td class="td"><a href="{{ route('suppliers.show', $payment->supplier) }}" class="link">{{ $payment->supplier?->name }}</a></td>
                                <td class="td">
                                    @if ($payment->purchase)
                                        <a href="{{ route('purchases.show', $payment->purchase) }}" class="link">{{ $payment->purchase->invoice_no }}</a>
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="td">{{ __($payment->method) }}</td>
                                <td class="td">{{ $payment->reference_no ?? '—' }}</td>
                                <td class="td text-end font-semibold text-emerald-600 dark:text-emerald-400">{{ $money($payment->amount) }}</td>
                                <td class="td">
                                    @if ($payment->receipt_path)
                                        <a href="{{ route('payments.receipt', $payment) }}" class="link text-sm">{{ __('File') }}</a>
                                    @else
                                        <span class="muted">—</span>
                                    @endif
                                </td>
                                <td class="td">
                                    <div class="flex items-center justify-end gap-1">
                                        <a href="{{ route('payments.show', $payment) }}" class="btn-ghost btn-sm">{{ __('View') }}</a>
                                        @can('payments.manage')
                                            <a href="{{ route('payments.edit', $payment) }}" class="btn-ghost btn-sm">{{ __('Edit') }}</a>
                                        @endcan
                                        @can('users.manage')
                                            <form method="POST" action="{{ route('payments.destroy', $payment) }}" data-confirm="{{ __('Delete this payment?') }}">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn-ghost btn-sm text-rose-600 dark:text-rose-400">{{ __('Delete') }}</button>
                                            </form>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="td py-12 text-center text-slate-500">{{ __('No payments found.') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($payments->hasPages())
                <div class="border-t border-slate-200 px-4 py-3 dark:border-slate-800">{{ $payments->links() }}</div>
            @endif
        </div>
    </div>
@endsection
