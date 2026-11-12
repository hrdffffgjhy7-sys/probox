@extends('layouts.app')

@section('title', __('Payment').' #'.$payment->id)
@section('subtitle', __('Payment details'))

@section('content')
    @php $money = fn ($value) => \App\Support\Money::format($value); @endphp

    <div class="space-y-6">
        <div class="card card-body">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <p class="muted">{{ __('Amount') }}</p>
                    <p class="mt-1 text-3xl font-bold text-emerald-600 dark:text-emerald-400">{{ $money($payment->amount) }}</p>
                    <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">
                        {{ __('Paid to') }} <a href="{{ route('suppliers.show', $payment->supplier) }}" class="link">{{ $payment->supplier?->name }}</a>
                        {{ __('on') }} {{ $payment->payment_date?->format('d M Y') }}
                    </p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('payments.receipt.pdf', $payment) }}" class="btn-primary btn-sm">{{ __('Receipt PDF') }}</a>
                    @if ($payment->receipt_path)
                        <a href="{{ route('payments.receipt', $payment) }}" class="btn-secondary btn-sm">{{ __('Attachment') }}</a>
                    @endif
                    @can('payments.manage')
                        <a href="{{ route('payments.edit', $payment) }}" class="btn-secondary btn-sm">{{ __('Edit') }}</a>
                    @endcan
                </div>
            </div>
        </div>

        <div class="grid gap-6 xl:grid-cols-3">
            <div class="card card-body xl:col-span-2">
                <h2 class="section-title">{{ __('Details') }}</h2>
                <dl class="mt-4 grid gap-4 sm:grid-cols-2">
                    <div><dt class="label">{{ __('Supplier') }}</dt><dd>{{ $payment->supplier?->name }}</dd></div>
                    <div><dt class="label">{{ __('Salesman') }}</dt><dd>{{ $payment->salesman?->name ?? '—' }}</dd></div>
                    <div><dt class="label">{{ __('Purchase') }}</dt><dd>
                        @if ($payment->purchase)
                            <a href="{{ route('purchases.show', $payment->purchase) }}" class="link">{{ $payment->purchase->invoice_no }}</a>
                        @else
                            {{ __('General payment') }}
                        @endif
                    </dd></div>
                    <div><dt class="label">{{ __('Method') }}</dt><dd>{{ __($payment->method) }}</dd></div>
                    <div><dt class="label">{{ __('Reference No') }}</dt><dd>{{ $payment->reference_no ?? '—' }}</dd></div>
                    <div><dt class="label">{{ __('Recorded by') }}</dt><dd>{{ $payment->creator?->name ?? '—' }}</dd></div>
                </dl>
                @if ($payment->notes)
                    <div class="mt-4 border-t border-slate-200 pt-4 dark:border-slate-800">
                        <p class="label">{{ __('Notes') }}</p>
                        <p class="whitespace-pre-line text-sm text-slate-600 dark:text-slate-300">{{ $payment->notes }}</p>
                    </div>
                @endif
            </div>

            <div class="card card-body">
                <h2 class="section-title">{{ __('Quick links') }}</h2>
                <div class="mt-4 space-y-2 text-sm">
                    <a href="{{ route('khata.show', $payment->supplier) }}" class="link block">{{ __('Open supplier khata') }}</a>
                    <a href="{{ route('suppliers.statement', $payment->supplier) }}" class="link block">{{ __('Supplier statement') }}</a>
                    <a href="{{ route('payments.index') }}" class="link block">{{ __('All payments') }}</a>
                </div>
            </div>
        </div>

        @can('users.manage')
            <div class="card border-rose-200 dark:border-rose-500/30">
                <div class="card-body flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <h2 class="section-title text-rose-700 dark:text-rose-400">{{ __('Danger zone') }}</h2>
                        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ __('Deleting this payment reverses its ledger entry and restores the purchase balance.') }}</p>
                    </div>
                    <form method="POST" action="{{ route('payments.destroy', $payment) }}" data-confirm="{{ __('Delete this payment permanently?') }}">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn-danger">{{ __('Delete payment') }}</button>
                    </form>
                </div>
            </div>
        @endcan
    </div>
@endsection
