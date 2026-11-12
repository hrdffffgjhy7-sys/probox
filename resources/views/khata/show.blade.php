@extends('layouts.app')

@section('title', __('Ledger').' — '.$supplier->name)
@section('subtitle', __('Supplier account statement.'))

@section('content')
    @php
        $money = fn ($value) => \App\Support\Money::format($value);
        $balance = (float) $supplier->current_balance;
    @endphp

    <div class="space-y-6">
        <div class="card card-body">
            <div class="flex flex-col gap-5 lg:flex-row lg:items-start lg:justify-between">
                <div>
                    <div class="flex flex-wrap items-center gap-2">
                        <h2 class="text-xl font-bold text-slate-900 dark:text-white">{{ $supplier->name }}</h2>
                        @if ($balance > 0)
                            <span class="badge-warning">{{ __('Payable') }}</span>
                        @elseif ($balance < 0)
                            <span class="badge-success">{{ __('Receivable') }}</span>
                        @else
                            <span class="badge-neutral">{{ __('Settled') }}</span>
                        @endif
                        @if ($supplier->archived_at)
                            <span class="badge-neutral">{{ __('Archived') }}</span>
                        @endif
                    </div>
                    <div class="mt-2 flex flex-wrap gap-x-5 gap-y-1 text-sm text-slate-500 dark:text-slate-400">
                        <span>{{ __('Agency') }}: <span class="text-slate-700 dark:text-slate-200">{{ $supplier->agency?->name ?? '—' }}</span></span>
                        <span>{{ __('Salesman') }}: <span class="text-slate-700 dark:text-slate-200">{{ $supplier->salesman?->name ?? '—' }}</span></span>
                        <span>{{ __('Phone') }}: <span class="text-slate-700 dark:text-slate-200">{{ $supplier->phone ?? '—' }}</span></span>
                    </div>
                </div>
                <div class="text-start lg:text-end">
                    <p class="muted">{{ __('Current Balance') }}</p>
                    <p class="text-2xl font-bold {{ $balance > 0 ? 'text-rose-600 dark:text-rose-400' : ($balance < 0 ? 'text-emerald-600 dark:text-emerald-400' : '') }}">{{ $money(abs($balance)) }}</p>
                    <div class="mt-3 flex flex-wrap gap-2 lg:justify-end">
                        <a href="{{ route('suppliers.statement', $supplier) }}" class="btn-secondary btn-sm">{{ __('Statement') }}</a>
                        <a href="{{ route('suppliers.statement.pdf', $supplier) }}" class="btn-secondary btn-sm">{{ __('PDF') }}</a>
                        <a href="{{ route('suppliers.whatsapp', $supplier) }}" class="btn-secondary btn-sm">{{ __('WhatsApp') }}</a>
                        @can('suppliers.delete')
                            <form method="POST" action="{{ route('khata.clear', $supplier) }}" onsubmit="return confirm('{{ __('ui.confirm_clear_khata') }}')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn-danger btn-sm">{{ __('Clear Khata') }}</button>
                            </form>
                        @endcan
                        <a href="{{ route('khata.index') }}" class="btn-ghost btn-sm">{{ __('Back') }}</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <form method="GET" action="{{ route('khata.show', $supplier) }}" class="flex flex-wrap items-end gap-3">
                    @include('partials.date-range')
                    <button type="submit" class="btn-primary">{{ __('Apply') }}</button>
                    <a href="{{ route('khata.show', $supplier) }}" class="btn-secondary">{{ __('Reset') }}</a>
                </form>
            </div>
        </div>

        <div class="card overflow-hidden">
            <div class="border-b border-slate-200 px-5 py-4 dark:border-slate-800">
                <h2 class="section-title">{{ __('Ledger Entries') }}</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="table">
                    <thead class="bg-slate-50 dark:bg-slate-800/50">
                        <tr>
                            <th class="th">{{ __('Date') }}</th>
                            <th class="th">{{ __('Type') }}</th>
                            <th class="th">{{ __('Description') }}</th>
                            <th class="th text-end">{{ __('Debit') }}</th>
                            <th class="th text-end">{{ __('Credit') }}</th>
                            <th class="th text-end">{{ __('Balance') }}</th>
                            <th class="th text-end">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        <tr class="bg-slate-50/60 dark:bg-slate-800/30">
                            <td class="td" colspan="5">{{ __('Opening balance') }}</td>
                            <td class="td text-end font-medium">{{ $money($opening) }}</td>
                            <td class="td"></td>
                        </tr>
                        @forelse ($entries as $entry)
                            <tr>
                                <td class="td">{{ $entry->entry_date?->format('d M Y') }}</td>
                                <td class="td"><span class="badge-neutral">{{ __(ucfirst(str_replace('_', ' ', $entry->type))) }}</span></td>
                                <td class="td">{{ $entry->description ?? '—' }}</td>
                                <td class="td text-end">{{ (float) $entry->debit > 0 ? $money($entry->debit) : '—' }}</td>
                                <td class="td text-end text-emerald-600 dark:text-emerald-400">{{ (float) $entry->credit > 0 ? $money($entry->credit) : '—' }}</td>
                                <td class="td text-end font-medium">{{ $money($entry->running_balance) }}</td>
                                <td class="td text-end">
                                    @can('suppliers.manage')
                                        <form method="POST" action="{{ route('khata.entries.destroy', [$supplier, $entry]) }}" onsubmit="return confirm('{{ __('ui.confirm_delete_entry') }}')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn-danger btn-sm">{{ __('Delete') }}</button>
                                        </form>
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="td py-10 text-center text-slate-500">{{ __('No entries in this period.') }}</td></tr>
                        @endforelse
                    </tbody>
                    <tfoot class="border-t border-slate-200 bg-slate-50 dark:border-slate-800 dark:bg-slate-800/50">
                        <tr>
                            <td class="td" colspan="3">{{ __('Period totals') }}</td>
                            <td class="td text-end font-semibold">{{ $money($totals['debit']) }}</td>
                            <td class="td text-end font-semibold text-emerald-600 dark:text-emerald-400">{{ $money($totals['credit']) }}</td>
                            <td class="td text-end font-semibold">{{ $money($supplier->current_balance) }}</td>
                            <td class="td"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <div class="grid gap-6 xl:grid-cols-2">
            @can('suppliers.manage')
                <div class="card">
                    <div class="border-b border-slate-200 px-5 py-4 dark:border-slate-800">
                        <h2 class="section-title">{{ __('Add Adjustment') }}</h2>
                    </div>
                    <form method="POST" action="{{ route('khata.adjustment', $supplier) }}" class="card-body space-y-3">
                        @csrf
                        <div class="grid gap-3 sm:grid-cols-2">
                            <div>
                                <label for="adjustment_date" class="label">{{ __('Date') }}</label>
                                <input id="adjustment_date" name="adjustment_date" type="date" value="{{ now()->toDateString() }}" required class="input">
                            </div>
                            <div>
                                <label for="amount" class="label">{{ __('Amount') }}</label>
                                <input id="amount" name="amount" type="number" step="0.01" min="0.01" required class="input">
                            </div>
                            <div>
                                <label for="direction" class="label">{{ __('Direction') }}</label>
                                <select name="direction" id="direction" class="select">
                                    <option value="increase">{{ __('Increase balance') }}</option>
                                    <option value="decrease">{{ __('Decrease balance') }}</option>
                                </select>
                            </div>
                            <div>
                                <label for="category" class="label">{{ __('Category') }}</label>
                                <select name="category" id="category" class="select">
                                    <option value="payable">{{ __('Payable') }}</option>
                                    <option value="receivable">{{ __('Receivable') }}</option>
                                </select>
                            </div>
                            <div>
                                <label for="reference_no" class="label">{{ __('Reference No') }}</label>
                                <input id="reference_no" name="reference_no" type="text" class="input">
                            </div>
                            <div>
                                <label for="reason" class="label">{{ __('Reason') }}</label>
                                <input id="reason" name="reason" type="text" required class="input">
                            </div>
                        </div>
                        <button type="submit" class="btn-primary">{{ __('Save Adjustment') }}</button>
                    </form>
                </div>
            @endcan

            @can('purchases.manage')
                <div class="card">
                    <div class="border-b border-slate-200 px-5 py-4 dark:border-slate-800">
                        <h2 class="section-title">{{ __('Add Return') }}</h2>
                    </div>
                    <form method="POST" action="{{ route('khata.return', $supplier) }}" class="card-body space-y-3">
                        @csrf
                        <div class="grid gap-3 sm:grid-cols-2">
                            <div>
                                <label for="return_date" class="label">{{ __('Date') }}</label>
                                <input id="return_date" name="return_date" type="date" value="{{ now()->toDateString() }}" required class="input">
                            </div>
                            <div>
                                <label for="return_amount" class="label">{{ __('Amount') }}</label>
                                <input id="return_amount" name="amount" type="number" step="0.01" min="0.01" required class="input">
                            </div>
                            <div>
                                <label for="purchase_id" class="label">{{ __('Purchase') }}</label>
                                <select name="purchase_id" id="purchase_id" class="select">
                                    <option value="">{{ __('No specific invoice') }}</option>
                                    @foreach ($openPurchases as $purchase)
                                        <option value="{{ $purchase->id }}">{{ $purchase->invoice_no }} — {{ $money($purchase->remaining_amount) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label for="invoice_no" class="label">{{ __('Invoice No') }}</label>
                                <input id="invoice_no" name="invoice_no" type="text" class="input">
                            </div>
                            <div>
                                <label for="return_reference_no" class="label">{{ __('Reference No') }}</label>
                                <input id="return_reference_no" name="reference_no" type="text" class="input">
                            </div>
                            <div>
                                <label for="reason" class="label">{{ __('Reason') }}</label>
                                <input id="reason" name="reason" type="text" required class="input">
                            </div>
                        </div>
                        <button type="submit" class="btn-primary">{{ __('Save Return') }}</button>
                    </form>
                </div>
            @endcan
        </div>
    </div>
@endsection
