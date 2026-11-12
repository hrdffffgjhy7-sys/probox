@extends('layouts.app')

@section('title', __('Statement').' · '.$supplier->name)
@section('subtitle', __('Supplier account statement'))

@section('content')
    @php $money = fn ($value) => \App\Support\Money::format($value); @endphp

    <div class="space-y-6">
        <div class="card card-body">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                <div>
                    <h2 class="text-xl font-bold text-slate-900 dark:text-white">{{ $supplier->name }}</h2>
                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                        {{ $range[0]?->format('d M Y') ?? __('Beginning') }} &ndash; {{ $range[1]?->format('d M Y') ?? __('Today') }}
                    </p>
                    <div class="mt-2 flex flex-wrap gap-x-5 gap-y-1 text-sm text-slate-500 dark:text-slate-400">
                        <span>{{ __('Agency') }}: {{ $supplier->agency?->name ?? '—' }}</span>
                        <span>{{ __('Phone') }}: {{ $supplier->phone ?? '—' }}</span>
                    </div>
                </div>
                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('suppliers.statement', ['supplier' => $supplier, 'print' => 1] + request()->query()) }}" target="_blank" class="btn-secondary btn-sm">{{ __('Print') }}</a>
                    <a href="{{ route('suppliers.statement.pdf', ['supplier' => $supplier] + request()->query()) }}" class="btn-primary btn-sm">{{ __('PDF') }}</a>
                    <a href="{{ route('suppliers.statement.excel', ['supplier' => $supplier] + request()->query()) }}" class="btn-secondary btn-sm">{{ __('Excel') }}</a>
                    <a href="{{ route('suppliers.whatsapp', $supplier) }}" class="btn-secondary btn-sm">{{ __('WhatsApp') }}</a>
                </div>
            </div>
            <div class="mt-5 grid gap-4 sm:grid-cols-3">
                <div class="rounded-xl bg-slate-50 p-4 dark:bg-slate-800/60">
                    <p class="muted">{{ __('Opening balance') }}</p>
                    <p class="mt-1 text-xl font-bold">{{ $money($opening) }}</p>
                </div>
                <div class="rounded-xl bg-slate-50 p-4 dark:bg-slate-800/60">
                    <p class="muted">{{ __('Closing balance') }}</p>
                    <p class="mt-1 text-xl font-bold">{{ $money($closing) }}</p>
                </div>
                <div class="rounded-xl bg-slate-50 p-4 dark:bg-slate-800/60">
                    <p class="muted">{{ __('Entries') }}</p>
                    <p class="mt-1 text-xl font-bold">{{ number_format($entries->count()) }}</p>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <form method="GET" action="{{ route('suppliers.statement', $supplier) }}" class="flex flex-wrap items-end gap-3">
                    @include('partials.date-range')
                    <div class="flex gap-2">
                        <button type="submit" class="btn-primary">{{ __('Apply') }}</button>
                        <a href="{{ route('suppliers.statement', $supplier) }}" class="btn-secondary">{{ __('Reset') }}</a>
                    </div>
                </form>
            </div>
        </div>

        <div class="card overflow-hidden">
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
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        <tr class="bg-slate-50/60 dark:bg-slate-800/30">
                            <td class="td">{{ $range[0]?->format('d M Y') ?? __('Beginning') }}</td>
                            <td class="td" colspan="2">{{ __('Opening balance') }}</td>
                            <td class="td text-end">—</td>
                            <td class="td text-end">—</td>
                            <td class="td text-end font-semibold">{{ $money($opening) }}</td>
                        </tr>
                        @forelse ($entries as $entry)
                            <tr>
                                <td class="td">{{ $entry->entry_date?->format('d M Y') }}</td>
                                <td class="td"><span class="badge-neutral">{{ __($entry->type) }}</span></td>
                                <td class="td">{{ $entry->description ?: '—' }}</td>
                                <td class="td text-end">{{ (float) $entry->debit > 0 ? $money($entry->debit) : '—' }}</td>
                                <td class="td text-end text-emerald-600 dark:text-emerald-400">{{ (float) $entry->credit > 0 ? $money($entry->credit) : '—' }}</td>
                                <td class="td text-end font-medium">{{ $money($entry->running_balance) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="td py-10 text-center text-slate-500">{{ __('No entries in this period.') }}</td></tr>
                        @endforelse
                    </tbody>
                    <tfoot class="bg-slate-50 dark:bg-slate-800/50">
                        <tr>
                            <td class="td font-semibold" colspan="3">{{ __('Closing balance') }}</td>
                            <td class="td text-end font-semibold">{{ $money($entries->sum('debit')) }}</td>
                            <td class="td text-end font-semibold text-emerald-600 dark:text-emerald-400">{{ $money($entries->sum('credit')) }}</td>
                            <td class="td text-end font-bold">{{ $money($closing) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
@endsection
