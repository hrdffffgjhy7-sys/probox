@extends('layouts.app')

@section('title', $agency->name)
@section('subtitle', __('Agency details'))

@section('content')
    @php $money = fn ($value) => \App\Support\Money::format($value); @endphp

    <div class="space-y-6">
        <div class="card card-body">
            <div class="flex flex-col gap-5 lg:flex-row lg:items-start lg:justify-between">
                <div class="flex items-start gap-4">
                    <span class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-emerald-100 text-xl font-bold text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300">
                        {{ mb_strtoupper(mb_substr($agency->name, 0, 1)) }}
                    </span>
                    <div>
                        <div class="flex flex-wrap items-center gap-2">
                            <h2 class="text-xl font-bold text-slate-900 dark:text-white">{{ $agency->name }}</h2>
                            @if ($agency->is_active)
                                <span class="badge-success">{{ __('Active') }}</span>
                            @else
                                <span class="badge-neutral">{{ __('Inactive') }}</span>
                            @endif
                        </div>
                        <div class="mt-2 flex flex-wrap gap-x-5 gap-y-1 text-sm text-slate-500 dark:text-slate-400">
                            <span>{{ __('Phone') }}: <span class="text-slate-700 dark:text-slate-200">{{ $agency->phone ?? '—' }}</span></span>
                            <span>{{ __('WhatsApp') }}: <span class="text-slate-700 dark:text-slate-200">{{ $agency->whatsapp ?? '—' }}</span></span>
                        </div>
                        @if ($agency->address)
                            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ $agency->address }}</p>
                        @endif
                    </div>
                </div>

                <div class="flex flex-wrap gap-2">
                    @can('agencies.manage')
                        <a href="{{ route('agencies.edit', $agency) }}" class="btn-secondary btn-sm">{{ __('Edit') }}</a>
                    @endcan
                    <a href="{{ route('agencies.index') }}" class="btn-ghost btn-sm">{{ __('Back to agencies') }}</a>
                </div>
            </div>
        </div>

        <div class="grid gap-4 sm:grid-cols-3">
            <div class="card card-body">
                <p class="muted">{{ __('Salesmen') }}</p>
                <p class="stat-value mt-2">{{ number_format($agency->salesmen->count()) }}</p>
            </div>
            <div class="card card-body">
                <p class="muted">{{ __('Suppliers') }}</p>
                <p class="stat-value mt-2">{{ number_format($agency->suppliers->count()) }}</p>
            </div>
            <div class="card card-body">
                <p class="muted">{{ __('Total Payable') }}</p>
                <p class="stat-value mt-2 text-rose-600 dark:text-rose-400">{{ $money($agency->suppliers->sum('current_balance')) }}</p>
            </div>
        </div>

        <div class="grid gap-6 xl:grid-cols-2">
            <div class="card">
                <div class="border-b border-slate-200 px-5 py-4 dark:border-slate-800">
                    <h2 class="section-title">{{ __('Salesmen') }}</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="table">
                        <thead class="bg-slate-50 dark:bg-slate-800/50">
                            <tr>
                                <th class="th">{{ __('Name') }}</th>
                                <th class="th">{{ __('Phone') }}</th>
                                <th class="th text-end">{{ __('Suppliers') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            @forelse ($agency->salesmen as $salesman)
                                <tr>
                                    <td class="td">
                                        @can('salesmen.manage')
                                            <a href="{{ route('salesmen.show', $salesman) }}" class="link">{{ $salesman->name }}</a>
                                        @else
                                            {{ $salesman->name }}
                                        @endcan
                                    </td>
                                    <td class="td">{{ $salesman->phone ?? '—' }}</td>
                                    <td class="td text-end">{{ number_format($salesman->suppliers->count()) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="td py-8 text-center text-slate-500">{{ __('No salesmen yet.') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card">
                <div class="border-b border-slate-200 px-5 py-4 dark:border-slate-800">
                    <h2 class="section-title">{{ __('Suppliers') }}</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="table">
                        <thead class="bg-slate-50 dark:bg-slate-800/50">
                            <tr>
                                <th class="th">{{ __('Name') }}</th>
                                <th class="th">{{ __('Salesman') }}</th>
                                <th class="th text-end">{{ __('Balance') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            @forelse ($agency->suppliers as $supplier)
                                <tr>
                                    <td class="td">
                                        @can('suppliers.view')
                                            <a href="{{ route('suppliers.show', $supplier) }}" class="link">{{ $supplier->name }}</a>
                                        @else
                                            {{ $supplier->name }}
                                        @endcan
                                    </td>
                                    <td class="td">{{ $supplier->salesman?->name ?? '—' }}</td>
                                    <td class="td text-end font-medium {{ (float) $supplier->current_balance > 0 ? 'text-rose-600 dark:text-rose-400' : 'text-emerald-600 dark:text-emerald-400' }}">{{ $money($supplier->current_balance) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="td py-8 text-center text-slate-500">{{ __('No suppliers yet.') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        @if ($agency->notes)
            <div class="card card-body">
                <h2 class="section-title">{{ __('Notes') }}</h2>
                <p class="mt-2 whitespace-pre-line text-sm text-slate-600 dark:text-slate-300">{{ $agency->notes }}</p>
            </div>
        @endif
    </div>
@endsection
