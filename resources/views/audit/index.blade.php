@extends('layouts.app')

@section('title', __('Audit Log'))
@section('subtitle', __('Track important changes across the system.'))

@section('content')
    <div class="space-y-6">
        <div class="card">
            <div class="card-body">
                <form method="GET" action="{{ route('audit.index') }}" class="flex flex-wrap items-end gap-3">
                    <div class="min-w-48 flex-1">
                        <label for="q" class="label">{{ __('Search') }}</label>
                        <input type="search" name="q" id="q" value="{{ request('q') }}" placeholder="{{ __('Description, action or module') }}" class="input">
                    </div>
                    <div>
                        <label for="action" class="label">{{ __('Action') }}</label>
                        <select name="action" id="action" class="select">
                            <option value="">{{ __('All actions') }}</option>
                            @foreach ($actions as $action)
                                <option value="{{ $action }}" @selected(request('action') === $action)>{{ __(ucfirst($action)) }}</option>
                            @endforeach
                        </select>
                    </div>
                    @include('partials.date-range')
                    <div class="flex gap-2">
                        <button type="submit" class="btn-primary">{{ __('Filter') }}</button>
                        <a href="{{ route('audit.index') }}" class="btn-secondary">{{ __('Reset') }}</a>
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
                            <th class="th">{{ __('User') }}</th>
                            <th class="th">{{ __('Action') }}</th>
                            <th class="th">{{ __('Module') }}</th>
                            <th class="th">{{ __('Description') }}</th>
                            <th class="th">{{ __('IP') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @forelse ($logs as $log)
                            <tr>
                                <td class="td whitespace-nowrap">{{ $log->created_at?->format('d M Y H:i:s') }}</td>
                                <td class="td">{{ $log->user?->name ?? __('System') }}</td>
                                <td class="td"><span class="badge-neutral">{{ __(ucfirst($log->action)) }}</span></td>
                                <td class="td">{{ __(ucfirst($log->module)) }}</td>
                                <td class="td">
                                    <p>{{ $log->description }}</p>
                                    @if ($log->old_values || $log->new_values)
                                        <details class="mt-1 text-xs text-slate-500">
                                            <summary class="cursor-pointer">{{ __('Changes') }}</summary>
                                            @if ($log->old_values)
                                                <p class="mt-1 break-all"><strong>{{ __('Old') }}:</strong> {{ json_encode($log->old_values) }}</p>
                                            @endif
                                            @if ($log->new_values)
                                                <p class="break-all"><strong>{{ __('New') }}:</strong> {{ json_encode($log->new_values) }}</p>
                                            @endif
                                        </details>
                                    @endif
                                </td>
                                <td class="td text-xs">{{ $log->ip_address ?? '—' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="td py-12 text-center text-slate-500">{{ __('No audit entries found.') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($logs->hasPages())
                <div class="border-t border-slate-200 px-4 py-3 dark:border-slate-800">
                    {{ $logs->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
