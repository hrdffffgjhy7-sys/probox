@extends('layouts.app')

@section('title', __('Purchase').' '.$purchase->invoice_no)
@section('subtitle', __('Purchase invoice details'))

@section('content')
    @php $money = fn ($value) => \App\Support\Money::format($value); @endphp

    <div class="space-y-6">
        <div class="card card-body">
            <div class="flex flex-col gap-5 lg:flex-row lg:items-start lg:justify-between">
                <div>
                    <div class="flex flex-wrap items-center gap-2">
                        <h2 class="text-xl font-bold text-slate-900 dark:text-white">{{ $purchase->invoice_no }}</h2>
                        @if ($purchase->status === 'paid')
                            <span class="badge-success">{{ __('Paid') }}</span>
                        @elseif ($purchase->status === 'partial')
                            <span class="badge-warning">{{ __('Partial') }}</span>
                        @else
                            <span class="badge-danger">{{ __('Unpaid') }}</span>
                        @endif
                    </div>
                    <div class="mt-2 flex flex-wrap gap-x-5 gap-y-1 text-sm text-slate-500 dark:text-slate-400">
                        <span>{{ __('Date') }}: <span class="text-slate-700 dark:text-slate-200">{{ $purchase->purchase_date?->format('d M Y') }}</span></span>
                        <span>{{ __('Due Date') }}: <span class="text-slate-700 dark:text-slate-200">{{ $purchase->due_date?->format('d M Y') ?? '—' }}</span></span>
                        <span>{{ __('Supplier') }}: <a href="{{ route('suppliers.show', $purchase->supplier) }}" class="link">{{ $purchase->supplier?->name }}</a></span>
                        <span>{{ __('Salesman') }}: <span class="text-slate-700 dark:text-slate-200">{{ $purchase->salesman?->name ?? '—' }}</span></span>
                    </div>
                </div>

                <div class="flex flex-wrap gap-2">
                    @can('purchases.manage')
                        <a href="{{ route('purchases.edit', $purchase) }}" class="btn-secondary btn-sm">{{ __('Edit') }}</a>
                    @endcan
                    <a href="{{ route('purchases.print', $purchase) }}" target="_blank" class="btn-secondary btn-sm">{{ __('Print') }}</a>
                    <a href="{{ route('purchases.pdf', $purchase) }}" class="btn-secondary btn-sm">{{ __('PDF') }}</a>
                    @if ($purchase->attachment_path)
                        <a href="{{ route('purchases.attachment', $purchase) }}" class="btn-secondary btn-sm">{{ __('Attachment') }}</a>
                    @endif
                    <a href="{{ route('purchases.index') }}" class="btn-ghost btn-sm">{{ __('Back') }}</a>
                </div>
            </div>
        </div>

        <div class="grid gap-6 xl:grid-cols-3">
            <div class="card xl:col-span-2">
                <div class="border-b border-slate-200 px-5 py-4 dark:border-slate-800">
                    <h2 class="section-title">{{ __('Items') }}</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="table">
                        <thead class="bg-slate-50 dark:bg-slate-800/50">
                            <tr>
                                <th class="th">{{ __('Item') }}</th>
                                <th class="th text-end">{{ __('Qty') }}</th>
                                <th class="th">{{ __('Unit') }}</th>
                                <th class="th text-end">{{ __('Rate') }}</th>
                                <th class="th text-end">{{ __('Discount') }}</th>
                                <th class="th text-end">{{ __('Tax') }}</th>
                                <th class="th text-end">{{ __('Total') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            @foreach ($purchase->items as $item)
                                <tr>
                                    <td class="td">{{ $item->product_name }}</td>
                                    <td class="td text-end">{{ (float) $item->quantity }}</td>
                                    <td class="td">{{ $item->unit }}</td>
                                    <td class="td text-end">{{ $money($item->rate) }}</td>
                                    <td class="td text-end">{{ $money($item->discount) }}</td>
                                    <td class="td text-end">{{ $money($item->tax) }}</td>
                                    <td class="td text-end font-medium">{{ $money($item->total) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card">
                <div class="border-b border-slate-200 px-5 py-4 dark:border-slate-800">
                    <h2 class="section-title">{{ __('Totals') }}</h2>
                </div>
                <div class="card-body space-y-3 text-sm">
                    <div class="flex items-center justify-between"><span class="text-slate-500 dark:text-slate-400">{{ __('Subtotal') }}</span><span class="font-semibold">{{ $money($purchase->subtotal) }}</span></div>
                    <div class="flex items-center justify-between"><span class="text-slate-500 dark:text-slate-400">{{ __('Discount') }}</span><span class="font-semibold">{{ $money($purchase->discount) }}</span></div>
                    <div class="flex items-center justify-between"><span class="text-slate-500 dark:text-slate-400">{{ __('Tax') }}</span><span class="font-semibold">{{ $money($purchase->tax) }}</span></div>
                    <div class="flex items-center justify-between"><span class="text-slate-500 dark:text-slate-400">{{ __('Additional Charges') }}</span><span class="font-semibold">{{ $money($purchase->additional_charges) }}</span></div>
                    <div class="flex items-center justify-between border-t border-slate-200 pt-3 dark:border-slate-800"><span class="text-slate-500 dark:text-slate-400">{{ __('Grand Total') }}</span><span class="text-lg font-bold">{{ $money($purchase->total) }}</span></div>
                    <div class="flex items-center justify-between"><span class="text-slate-500 dark:text-slate-400">{{ __('Paid') }}</span><span class="font-semibold text-emerald-600 dark:text-emerald-400">{{ $money($purchase->paid_amount) }}</span></div>
                    <div class="flex items-center justify-between"><span class="text-slate-500 dark:text-slate-400">{{ __('Remaining') }}</span><span class="font-semibold text-rose-600 dark:text-rose-400">{{ $money($purchase->remaining_amount) }}</span></div>
                    @if ($purchase->creator)
                        <p class="border-t border-slate-200 pt-3 text-xs text-slate-400 dark:border-slate-800">{{ __('Created by') }}: {{ $purchase->creator->name }}</p>
                    @endif
                </div>
            </div>
        </div>

        <div class="grid gap-6 xl:grid-cols-2">
            <div class="card">
                <div class="border-b border-slate-200 px-5 py-4 dark:border-slate-800">
                    <h2 class="section-title">{{ __('Payments') }}</h2>
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
                            @forelse ($purchase->payments as $payment)
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

            <div class="card">
                <div class="border-b border-slate-200 px-5 py-4 dark:border-slate-800">
                    <h2 class="section-title">{{ __('Returns') }}</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="table">
                        <thead class="bg-slate-50 dark:bg-slate-800/50">
                            <tr>
                                <th class="th">{{ __('Date') }}</th>
                                <th class="th">{{ __('Reason') }}</th>
                                <th class="th">{{ __('Reference') }}</th>
                                <th class="th text-end">{{ __('Amount') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            @forelse ($purchase->returns as $return)
                                <tr>
                                    <td class="td">{{ $return->return_date?->format('d M Y') }}</td>
                                    <td class="td">{{ $return->reason ?? '—' }}</td>
                                    <td class="td">{{ $return->reference_no ?? '—' }}</td>
                                    <td class="td text-end font-medium">{{ $money($return->amount) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="td py-8 text-center text-slate-500">{{ __('No returns yet.') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        @if ($purchase->notes)
            <div class="card card-body">
                <h2 class="section-title">{{ __('Notes') }}</h2>
                <p class="mt-2 whitespace-pre-line text-sm text-slate-600 dark:text-slate-300">{{ $purchase->notes }}</p>
            </div>
        @endif

        @can('users.manage')
            <div class="card border-rose-200 dark:border-rose-500/30">
                <div class="card-body">
                    <h2 class="section-title text-rose-700 dark:text-rose-400">{{ __('Danger zone') }}</h2>
                    <form method="POST" action="{{ route('purchases.destroy', $purchase) }}" class="mt-3" data-confirm="{{ __('Delete this purchase? This will reverse its ledger entries.') }}">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn-danger">{{ __('Delete purchase') }}</button>
                    </form>
                </div>
            </div>
        @endcan
    </div>
@endsection
