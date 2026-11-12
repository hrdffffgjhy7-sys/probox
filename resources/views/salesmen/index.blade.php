@extends('layouts.app')

@section('title', __('Salesmen'))
@section('subtitle', __('Manage salesmen and the suppliers they handle.'))

@section('content')
    <div class="space-y-6">
        <div class="card">
            <div class="card-body">
                <form method="GET" action="{{ route('salesmen.index') }}" class="flex flex-wrap items-end gap-3">
                    <div class="min-w-52 flex-1">
                        <label for="q" class="label">{{ __('Search') }}</label>
                        <input type="search" name="q" id="q" value="{{ request('q') }}" placeholder="{{ __('Search by name or phone') }}" class="input">
                    </div>
                    <div>
                        <label for="agency_id" class="label">{{ __('Agency') }}</label>
                        <select name="agency_id" id="agency_id" class="select">
                            <option value="">{{ __('All agencies') }}</option>
                            @foreach ($agencies as $agency)
                                <option value="{{ $agency->id }}" @selected((string) request('agency_id') === (string) $agency->id)>{{ $agency->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="status" class="label">{{ __('Status') }}</label>
                        <select name="status" id="status" class="select">
                            <option value="" @selected(! request('status'))>{{ __('All Status') }}</option>
                            <option value="active" @selected(request('status') === 'active')>{{ __('Active') }}</option>
                            <option value="archived" @selected(request('status') === 'archived')>{{ __('Inactive') }}</option>
                        </select>
                    </div>
                    <div class="flex gap-2">
                        <button type="submit" class="btn-primary">{{ __('Filter') }}</button>
                        <a href="{{ route('salesmen.index') }}" class="btn-secondary">{{ __('Reset') }}</a>
                    </div>
                    @can('salesmen.manage')
                        <a href="{{ route('salesmen.create') }}" class="btn-primary ms-auto">{{ __('Add Salesman') }}</a>
                    @endcan
                </form>
            </div>
        </div>

        <div class="card overflow-hidden">
            <div class="overflow-x-auto">
                <table class="table">
                    <thead class="bg-slate-50 dark:bg-slate-800/50">
                        <tr>
                            <th class="th">{{ __('Salesman') }}</th>
                            <th class="th">{{ __('Agency') }}</th>
                            <th class="th">{{ __('Phone') }}</th>
                            <th class="th text-end">{{ __('Suppliers') }}</th>
                            <th class="th">{{ __('Status') }}</th>
                            <th class="th text-end">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @forelse ($salesmen as $salesman)
                            <tr>
                                <td class="td">
                                    <a href="{{ route('salesmen.show', $salesman) }}" class="link">{{ $salesman->name }}</a>
                                    @if ($salesman->address)
                                        <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">{{ $salesman->address }}</p>
                                    @endif
                                </td>
                                <td class="td">{{ $salesman->agency?->name ?? '—' }}</td>
                                <td class="td">{{ $salesman->phone ?? '—' }}</td>
                                <td class="td text-end">{{ number_format($salesman->suppliers_count) }}</td>
                                <td class="td">
                                    @if ($salesman->is_active)
                                        <span class="badge-success">{{ __('Active') }}</span>
                                    @else
                                        <span class="badge-neutral">{{ __('Inactive') }}</span>
                                    @endif
                                </td>
                                <td class="td">
                                    <div class="flex items-center justify-end gap-1">
                                        <a href="{{ route('salesmen.show', $salesman) }}" class="btn-ghost btn-sm">{{ __('View') }}</a>
                                        @can('salesmen.manage')
                                            <a href="{{ route('salesmen.edit', $salesman) }}" class="btn-ghost btn-sm">{{ __('Edit') }}</a>
                                            @if ($salesman->is_active)
                                                <form method="POST" action="{{ route('salesmen.archive', $salesman) }}" data-confirm="{{ __('Archive this salesman?') }}">
                                                    @csrf
                                                    <button type="submit" class="btn-ghost btn-sm text-amber-600 dark:text-amber-400">{{ __('Archive') }}</button>
                                                </form>
                                            @else
                                                <form method="POST" action="{{ route('salesmen.restore', $salesman) }}">
                                                    @csrf
                                                    <button type="submit" class="btn-ghost btn-sm text-emerald-600 dark:text-emerald-400">{{ __('Restore') }}</button>
                                                </form>
                                            @endif
                                        @endcan
                                        @if (auth()->user()->isAdmin())
                                            <form method="POST" action="{{ route('salesmen.destroy', $salesman) }}" data-confirm="{{ __('Delete this salesman permanently?') }}">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn-ghost btn-sm text-rose-600 dark:text-rose-400">{{ __('Delete') }}</button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="td py-12 text-center">
                                    <p class="text-slate-500 dark:text-slate-400">{{ __('No salesmen found.') }}</p>
                                    @can('salesmen.manage')
                                        <a href="{{ route('salesmen.create') }}" class="btn-primary mt-4">{{ __('Add Salesman') }}</a>
                                    @endcan
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($salesmen->hasPages())
                <div class="border-t border-slate-200 px-4 py-3 dark:border-slate-800">
                    {{ $salesmen->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
