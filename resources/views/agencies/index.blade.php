@extends('layouts.app')

@section('title', __('Agencies'))
@section('subtitle', __('Manage agencies and their salesmen and suppliers.'))

@section('content')
    <div class="space-y-6">
        <div class="card">
            <div class="card-body">
                <form method="GET" action="{{ route('agencies.index') }}" class="flex flex-wrap items-end gap-3">
                    <div class="min-w-52 flex-1">
                        <label for="q" class="label">{{ __('Search') }}</label>
                        <input type="search" name="q" id="q" value="{{ request('q') }}" placeholder="{{ __('Search by name or phone') }}" class="input">
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
                        <a href="{{ route('agencies.index') }}" class="btn-secondary">{{ __('Reset') }}</a>
                    </div>
                    @can('agencies.manage')
                        <a href="{{ route('agencies.create') }}" class="btn-primary ms-auto">{{ __('Add Agency') }}</a>
                    @endcan
                </form>
            </div>
        </div>

        <div class="card overflow-hidden">
            <div class="overflow-x-auto">
                <table class="table">
                    <thead class="bg-slate-50 dark:bg-slate-800/50">
                        <tr>
                            <th class="th">{{ __('Agency') }}</th>
                            <th class="th">{{ __('Phone') }}</th>
                            <th class="th text-end">{{ __('Salesmen') }}</th>
                            <th class="th text-end">{{ __('Suppliers') }}</th>
                            <th class="th">{{ __('Status') }}</th>
                            <th class="th text-end">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @forelse ($agencies as $agency)
                            <tr>
                                <td class="td">
                                    <a href="{{ route('agencies.show', $agency) }}" class="link">{{ $agency->name }}</a>
                                    @if ($agency->address)
                                        <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">{{ $agency->address }}</p>
                                    @endif
                                </td>
                                <td class="td">{{ $agency->phone ?? '—' }}</td>
                                <td class="td text-end">{{ number_format($agency->salesmen_count) }}</td>
                                <td class="td text-end">{{ number_format($agency->suppliers_count) }}</td>
                                <td class="td">
                                    @if ($agency->is_active)
                                        <span class="badge-success">{{ __('Active') }}</span>
                                    @else
                                        <span class="badge-neutral">{{ __('Inactive') }}</span>
                                    @endif
                                </td>
                                <td class="td">
                                    <div class="flex items-center justify-end gap-1">
                                        <a href="{{ route('agencies.show', $agency) }}" class="btn-ghost btn-sm">{{ __('View') }}</a>
                                        @can('agencies.manage')
                                            <a href="{{ route('agencies.edit', $agency) }}" class="btn-ghost btn-sm">{{ __('Edit') }}</a>
                                            @if ($agency->is_active)
                                                <form method="POST" action="{{ route('agencies.archive', $agency) }}" data-confirm="{{ __('Archive this agency?') }}">
                                                    @csrf
                                                    <button type="submit" class="btn-ghost btn-sm text-amber-600 dark:text-amber-400">{{ __('Archive') }}</button>
                                                </form>
                                            @else
                                                <form method="POST" action="{{ route('agencies.restore', $agency) }}">
                                                    @csrf
                                                    <button type="submit" class="btn-ghost btn-sm text-emerald-600 dark:text-emerald-400">{{ __('Restore') }}</button>
                                                </form>
                                            @endif
                                        @endcan
                                        @if (auth()->user()->isAdmin())
                                            <form method="POST" action="{{ route('agencies.destroy', $agency) }}" data-confirm="{{ __('Delete this agency permanently?') }}">
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
                                    <p class="text-slate-500 dark:text-slate-400">{{ __('No agencies found.') }}</p>
                                    @can('agencies.manage')
                                        <a href="{{ route('agencies.create') }}" class="btn-primary mt-4">{{ __('Add Agency') }}</a>
                                    @endcan
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($agencies->hasPages())
                <div class="border-t border-slate-200 px-4 py-3 dark:border-slate-800">
                    {{ $agencies->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
