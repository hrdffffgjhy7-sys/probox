@extends('layouts.app')

@section('title', $supplier->name)
@section('subtitle', __('Supplier account'))

@section('content')
    @php
        $money = fn ($value) => \App\Support\Money::format($value);
        $balance = (float) $supplier->current_balance;
    @endphp

    <div class="space-y-6">
        <div class="card card-body">
            <div class="flex flex-col gap-5 lg:flex-row lg:items-start lg:justify-between">
                <div class="flex items-start gap-4">
                    <span class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-emerald-100 text-xl font-bold text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300">
                        {{ mb_strtoupper(mb_substr($supplier->name, 0, 1)) }}
                    </span>
                    <div>
                        <div class="flex flex-wrap items-center gap-2">
                            <h2 class="text-xl font-bold text-slate-900 dark:text-white">{{ $supplier->name }}</h2>
                            @if ($supplier->archived_at)
                                <span class="badge-neutral">{{ __('Archived') }}</span>
                            @elseif ($supplier->is_active)
                                <span class="badge-success">{{ __('Active') }}</span>
                            @else
                                <span class="badge-warning">{{ __('Inactive') }}</span>
                            @endif
                        </div>
                        <div class="mt-2 flex flex-wrap gap-x-5 gap-y-1 text-sm text-slate-500 dark:text-slate-400">
                            <span>{{ __('Agency') }}: <span class="text-slate-700 dark:text-slate-200">{{ $supplier->agency?->name ?? '—' }}</span></span>
                            <span>{{ __('Salesman') }}: <span class="text-slate-700 dark:text-slate-200">{{ $supplier->salesman?->name ?? '—' }}</span></span>
                            <span>{{ __('Phone') }}: <span class="text-slate-700 dark:text-slate-200">{{ $supplier->phone ?? '—' }}</span></span>
                            <span>{{ __('WhatsApp') }}: <span class="text-slate-700 dark:text-slate-200">{{ $supplier->whatsapp ?? '—' }}</span></span>
                        </div>
                        @if ($supplier->address)
                            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ $supplier->address }}</p>
                        @endif
                    </div>
                </div>

                <div class="flex flex-wrap gap-2">
                    @can('suppliers.manage')
                        <a href="{{ route('suppliers.edit', $supplier) }}" class="btn-secondary btn-sm">{{ __('Edit') }}</a>
                    @endcan
                    @can('ledger.view')
                        <a href="{{ route('khata.show', $supplier) }}" class="btn-secondary btn-sm">{{ __('View Khata') }}</a>
                        <a href="{{ route('suppliers.statement', $supplier) }}" class="btn-secondary btn-sm">{{ __('View statement') }}</a>
                    @endcan
                    @can('suppliers.view')
                        <a href="{{ route('suppliers.whatsapp', $supplier) }}" class="btn-secondary btn-sm">{{ __('Send WhatsApp') }}</a>
                    @endcan
                    @can('payments.manage')
                        <a href="{{ route('payments.create', ['supplier_id' => $supplier->id]) }}" class="btn-primary btn-sm">{{ __('Add Payment') }}</a>
                    @endcan
                    @can('purchases.manage')
                        <a href="{{ route('purchases.create') }}" class="btn-primary btn-sm">{{ __('Add Purchase') }}</a>
                    @endcan
                </div>
            </div>
        </div>

        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <div class="card card-body">
                <p class="muted">{{ __('Outstanding Balance') }}</p>
                <p class="stat-value mt-2 {{ $balance > 0 ? 'text-rose-600 dark:text-rose-400' : ($balance < 0 ? 'text-emerald-600 dark:text-emerald-400' : '') }}">
                    {{ $money(abs($balance)) }}
                </p>
                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ $balance > 0 ? __('Payable') : ($balance < 0 ? __('Receivable') : __('Settled')) }}</p>
            </div>
            <div class="card card-body">
                <p class="muted">{{ __('Total purchased') }}</p>
                <p class="stat-value mt-2">{{ $money($totals['purchases']) }}</p>
            </div>
            <div class="card card-body">
                <p class="muted">{{ __('Total paid') }}</p>
                <p class="stat-value mt-2 text-emerald-600 dark:text-emerald-400">{{ $money($totals['payments']) }}</p>
            </div>
            <div class="card card-body">
                <p class="muted">{{ __('Purchase Returns') }}</p>
                <p class="stat-value mt-2">{{ $money($totals['returns']) }}</p>
            </div>
        </div>

        <div class="grid gap-6 xl:grid-cols-3">
            <div class="card xl:col-span-2">
                <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4 dark:border-slate-800">
                    <h2 class="section-title">{{ __('Transaction History') }}</h2>
                    @can('ledger.view')
                        <a href="{{ route('suppliers.statement', $supplier) }}" class="link text-sm">{{ __('Complete Ledger') }}</a>
                    @endcan
                </div>
                <div class="overflow-x-auto">
                    <table class="table">
                        <thead class="bg-slate-50 dark:bg-slate-800/50">
                            <tr>
                                <th class="th">{{ __('Date') }}</th>
                                <th class="th">{{ __('Description') }}</th>
                                <th class="th text-end">{{ __('Debit') }}</th>
                                <th class="th text-end">{{ __('Credit') }}</th>
                                <th class="th text-end">{{ __('Balance') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            @forelse ($recent as $entry)
                                <tr>
                                    <td class="td">{{ $entry->entry_date?->format('d M Y') }}</td>
                                    <td class="td">
                                        {{ $entry->description ?: __($entry->type) }}
                                        <span class="ms-1 text-xs text-slate-400">{{ __($entry->type) }}</span>
                                    </td>
                                    <td class="td text-end">{{ (float) $entry->debit > 0 ? $money($entry->debit) : '—' }}</td>
                                    <td class="td text-end">{{ (float) $entry->credit > 0 ? $money($entry->credit) : '—' }}</td>
                                    <td class="td text-end font-medium">{{ $money($entry->running_balance) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="td py-8 text-center text-slate-500">{{ __('No transactions in this period') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card">
                <div class="border-b border-slate-200 px-5 py-4 dark:border-slate-800">
                    <h2 class="section-title">{{ __('Account Summary') }}</h2>
                </div>
                <div class="card-body space-y-3 text-sm">
                    <div class="flex items-center justify-between">
                        <span class="text-slate-500 dark:text-slate-400">{{ __('Opening Balance') }}</span>
                        <span class="font-semibold">{{ $money($supplier->opening_balance) }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-slate-500 dark:text-slate-400">{{ __('Opening Balance Type') }}</span>
                        <span class="font-semibold">{{ __(ucfirst($supplier->opening_balance_type ?? 'payable')) }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-slate-500 dark:text-slate-400">{{ __('Debit') }}</span>
                        <span class="font-semibold">{{ $money($totals['debit']) }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-slate-500 dark:text-slate-400">{{ __('Credit') }}</span>
                        <span class="font-semibold">{{ $money($totals['credit']) }}</span>
                    </div>
                    <div class="flex items-center justify-between border-t border-slate-200 pt-3 dark:border-slate-800">
                        <span class="text-slate-500 dark:text-slate-400">{{ __('Balance') }}</span>
                        <span class="text-lg font-bold {{ $balance > 0 ? 'text-rose-600 dark:text-rose-400' : 'text-emerald-600 dark:text-emerald-400' }}">{{ $money($balance) }}</span>
                    </div>
                    @if ($supplier->notes)
                        <div class="border-t border-slate-200 pt-3 dark:border-slate-800">
                            <p class="text-slate-500 dark:text-slate-400">{{ __('Notes') }}</p>
                            <p class="mt-1 whitespace-pre-line">{{ $supplier->notes }}</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="grid gap-6 xl:grid-cols-2">
            <div class="card">
                <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4 dark:border-slate-800">
                    <h2 class="section-title">{{ __('Purchase History') }}</h2>
                    @can('purchases.view')
                        <a href="{{ route('purchases.index', ['supplier_id' => $supplier->id]) }}" class="link text-sm">{{ __('View all') }}</a>
                    @endcan
                </div>
                <div class="overflow-x-auto">
                    <table class="table">
                        <thead class="bg-slate-50 dark:bg-slate-800/50">
                            <tr>
                                <th class="th">{{ __('Date') }}</th>
                                <th class="th">{{ __('Invoice') }}</th>
                                <th class="th text-end">{{ __('Total') }}</th>
                                <th class="th text-end">{{ __('Remaining') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            @forelse ($purchases as $purchase)
                                <tr>
                                    <td class="td">{{ $purchase->purchase_date?->format('d M Y') }}</td>
                                    <td class="td"><a href="{{ route('purchases.show', $purchase) }}" class="link">{{ $purchase->invoice_no }}</a></td>
                                    <td class="td text-end">{{ $money($purchase->total) }}</td>
                                    <td class="td text-end">{{ $money($purchase->remaining_amount) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="td py-8 text-center text-slate-500">{{ __('No purchases yet.') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card">
                <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4 dark:border-slate-800">
                    <h2 class="section-title">{{ __('Payment History') }}</h2>
                    @can('payments.view')
                        <a href="{{ route('payments.index', ['supplier_id' => $supplier->id]) }}" class="link text-sm">{{ __('View all') }}</a>
                    @endcan
                </div>
                <div class="overflow-x-auto">
                    <table class="table">
                        <thead class="bg-slate-50 dark:bg-slate-800/50">
                            <tr>
                                <th class="th">{{ __('Date') }}</th>
                                <th class="th">{{ __('Method') }}</th>
                                <th class="th">{{ __('Reference') }}</th>
                                <th class="th text-end">{{ __('Amount') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            @forelse ($payments as $payment)
                                <tr>
                                    <td class="td">{{ $payment->payment_date?->format('d M Y') }}</td>
                                    <td class="td">{{ __($payment->method) }}</td>
                                    <td class="td">{{ $payment->reference_no ?? '—' }}</td>
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

        @can('suppliers.delete')
            <div class="card border-rose-200 dark:border-rose-500/30">
                <div class="card-body">
                    <h2 class="section-title text-rose-700 dark:text-rose-400">{{ __('Danger zone') }}</h2>
                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ __('Permanent delete is restricted to administrators and only allowed when no financial history exists.') }}</p>

                    <div class="mt-4 flex flex-wrap gap-2">
                        @if ($supplier->archived_at)
                            <form method="POST" action="{{ route('suppliers.restore', $supplier) }}">
                                @csrf
                                <button type="submit" class="btn-secondary">{{ __('Restore supplier') }}</button>
                            </form>
                        @else
                            <form method="POST" action="{{ route('suppliers.archive', $supplier) }}" data-confirm="{{ __('Archive this supplier?') }} {{ __('This supplier will be hidden from active lists. All financial history remains intact and can be restored.') }}">
                                @csrf
                                <button type="submit" class="btn-secondary">{{ __('Archive supplier') }}</button>
                            </form>
                        @endif
                    </div>

                    <details class="mt-4 rounded-xl border border-rose-200 bg-rose-50/50 p-4 dark:border-rose-500/30 dark:bg-rose-500/5">
                        <summary class="cursor-pointer text-sm font-semibold text-rose-700 dark:text-rose-400">{{ __('Permanent delete') }}</summary>
                        <form method="POST" action="{{ route('suppliers.destroy', $supplier) }}" class="mt-3 space-y-3" data-confirm="{{ __('Delete supplier permanently?') }}">
                            @csrf
                            @method('DELETE')
                            <label class="flex items-start gap-2 text-sm text-rose-800 dark:text-rose-200">
                                <input type="checkbox" name="confirm" value="1" required class="mt-0.5 h-4 w-4 rounded border-rose-300 text-rose-600 focus:ring-rose-500">
                                <span>{{ __('This action cannot be undone.') }} {{ __('This supplier has financial history and cannot be permanently deleted.') }}</span>
                            </label>
                            <button type="submit" class="btn-danger">{{ __('Yes, delete') }}</button>
                        </form>
                    </details>
                </div>
            </div>
        @endcan
    </div>
@endsection
