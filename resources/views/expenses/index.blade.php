@extends('layouts.app')

@section('title', __('Expenses'))
@section('subtitle', __('Business expenses in the selected period.'))

@section('content')
    @php $money = fn ($value) => \App\Support\Money::format($value); @endphp

    <div class="space-y-6">
        <div class="card card-body">
            <p class="muted">{{ __('Total expenses in period') }}</p>
            <p class="stat-value mt-2 text-rose-600 dark:text-rose-400">{{ $money($total) }}</p>
        </div>

        <div class="card">
            <div class="card-body">
                <form method="GET" action="{{ route('expenses.index') }}" class="flex flex-wrap items-end gap-3">
                    <div class="min-w-48 flex-1">
                        <label for="q" class="label">{{ __('Search') }}</label>
                        <input type="search" name="q" id="q" value="{{ request('q') }}" placeholder="{{ __('Title, reference or notes') }}" class="input">
                    </div>
                    <div>
                        <label for="category" class="label">{{ __('Category') }}</label>
                        <select name="category" id="category" class="select">
                            <option value="">{{ __('All categories') }}</option>
                            @foreach ($categories as $value => $label)
                                <option value="{{ $value }}" @selected(request('category') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="method" class="label">{{ __('Method') }}</label>
                        <select name="method" id="method" class="select">
                            <option value="">{{ __('All methods') }}</option>
                            @foreach ($methods as $value => $label)
                                <option value="{{ $value }}" @selected(request('method') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    @include('partials.date-range')
                    <div class="flex gap-2">
                        <button type="submit" class="btn-primary">{{ __('Filter') }}</button>
                        <a href="{{ route('expenses.index') }}" class="btn-secondary">{{ __('Reset') }}</a>
                    </div>
                    @can('expenses.manage')
                        <a href="{{ route('expenses.create') }}" class="btn-primary ms-auto">{{ __('Add Expense') }}</a>
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
                            <th class="th">{{ __('Title') }}</th>
                            <th class="th">{{ __('Category') }}</th>
                            <th class="th">{{ __('Method') }}</th>
                            <th class="th">{{ __('Reference') }}</th>
                            <th class="th">{{ __('Recorded by') }}</th>
                            <th class="th text-end">{{ __('Amount') }}</th>
                            <th class="th text-end">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @forelse ($expenses as $expense)
                            <tr>
                                <td class="td">{{ $expense->expense_date?->format('d M Y') }}</td>
                                <td class="td font-medium">{{ $expense->title }}</td>
                                <td class="td">{{ __(ucfirst($expense->category)) }}</td>
                                <td class="td">{{ __(ucfirst($expense->method)) }}</td>
                                <td class="td">{{ $expense->reference_no ?? '—' }}</td>
                                <td class="td">{{ $expense->creator?->name ?? '—' }}</td>
                                <td class="td text-end font-semibold text-rose-600 dark:text-rose-400">{{ $money($expense->amount) }}</td>
                                <td class="td">
                                    <div class="flex items-center justify-end gap-1">
                                        @can('expenses.manage')
                                            <a href="{{ route('expenses.edit', $expense) }}" class="btn-ghost btn-sm">{{ __('Edit') }}</a>
                                        @endcan
                                        @can('users.manage')
                                            <form method="POST" action="{{ route('expenses.destroy', $expense) }}" data-confirm="{{ __('Delete this expense?') }}">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn-ghost btn-sm text-rose-600 dark:text-rose-400">{{ __('Delete') }}</button>
                                            </form>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="td py-12 text-center text-slate-500">{{ __('No expenses found.') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($expenses->hasPages())
                <div class="border-t border-slate-200 px-4 py-3 dark:border-slate-800">
                    {{ $expenses->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
