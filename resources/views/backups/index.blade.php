@extends('layouts.app')

@section('title', __('Backups'))
@section('subtitle', __('Create, verify and restore database backups.'))

@section('content')
    @php
        $bytes = function ($size) {
            $size = (float) $size;
            if ($size >= 1073741824) return number_format($size / 1073741824, 2).' GB';
            if ($size >= 1048576) return number_format($size / 1048576, 2).' MB';
            if ($size >= 1024) return number_format($size / 1024, 2).' KB';
            return number_format($size, 0).' B';
        };
    @endphp

    <div class="space-y-6">
        <div class="grid gap-4 sm:grid-cols-3">
            <div class="card card-body">
                <p class="muted">{{ __('Last backup') }}</p>
                <p class="mt-2 text-lg font-bold">{{ $last?->created_at?->format('d M Y H:i') ?? __('Never') }}</p>
            </div>
            <div class="card card-body">
                <p class="muted">{{ __('Total backups') }}</p>
                <p class="stat-value mt-2">{{ $backups->total() }}</p>
            </div>
            <div class="card card-body">
                <p class="muted">{{ __('Storage directory') }}</p>
                <p class="mt-2 truncate text-sm" title="{{ $directory }}">{{ $directory }}</p>
            </div>
        </div>

        <div class="grid gap-6 xl:grid-cols-3">
            <div class="card">
                <div class="border-b border-slate-200 px-5 py-4 dark:border-slate-800">
                    <h2 class="section-title">{{ __('Create Backup') }}</h2>
                </div>
                <form method="POST" action="{{ route('backups.create') }}" class="card-body space-y-3">
                    @csrf
                    <div>
                        <label for="type" class="label">{{ __('Type') }}</label>
                        <select name="type" id="type" class="select">
                            <option value="manual">{{ __('Database snapshot') }}</option>
                            <option value="json">{{ __('JSON export') }}</option>
                        </select>
                    </div>
                    <div>
                        <label for="notes" class="label">{{ __('Notes') }}</label>
                        <input id="notes" name="notes" type="text" maxlength="500" class="input">
                    </div>
                    <button type="submit" class="btn-primary">{{ __('Create Backup') }}</button>
                </form>
            </div>

            <div class="card">
                <div class="border-b border-slate-200 px-5 py-4 dark:border-slate-800">
                    <h2 class="section-title">{{ __('Quick Actions') }}</h2>
                </div>
                <div class="card-body space-y-3">
                    <form method="POST" action="{{ route('backups.json') }}">
                        @csrf
                        <button type="submit" class="btn-secondary w-full">{{ __('Export JSON') }}</button>
                    </form>
                    @if (auth()->user()->isAdmin())
                        <form method="POST" action="{{ route('backups.upload') }}" enctype="multipart/form-data" class="space-y-2" data-confirm="{{ __('Uploading will restore this file over the current database. Continue?') }}">
                            @csrf
                            <label for="file" class="label">{{ __('Upload & restore') }}</label>
                            <input id="file" name="file" type="file" accept=".sqlite,.sql,.db,.json" required class="input">
                            <button type="submit" class="btn-secondary w-full">{{ __('Upload and Restore') }}</button>
                        </form>
                    @endif
                </div>
            </div>

            <div class="card">
                <div class="border-b border-slate-200 px-5 py-4 dark:border-slate-800">
                    <h2 class="section-title">{{ __('Info') }}</h2>
                </div>
                <div class="card-body text-sm text-slate-600 dark:text-slate-300">
                    <p>{{ __('Backups are stored on the local disk. Keep a copy of important backups in another location.') }}</p>
                    <p class="mt-3">{{ __('Restoring a backup replaces current data. Only administrators can restore or delete backups.') }}</p>
                </div>
            </div>
        </div>

        <div class="card overflow-hidden">
            <div class="overflow-x-auto">
                <table class="table">
                    <thead class="bg-slate-50 dark:bg-slate-800/50">
                        <tr>
                            <th class="th">{{ __('Name') }}</th>
                            <th class="th">{{ __('Type') }}</th>
                            <th class="th text-end">{{ __('Size') }}</th>
                            <th class="th">{{ __('Status') }}</th>
                            <th class="th">{{ __('Created By') }}</th>
                            <th class="th">{{ __('Created') }}</th>
                            <th class="th text-end">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @forelse ($backups as $backup)
                            <tr>
                                <td class="td font-medium">
                                    {{ $backup->name }}
                                    @if ($backup->notes)
                                        <span class="block text-xs text-slate-400">{{ $backup->notes }}</span>
                                    @endif
                                </td>
                                <td class="td">{{ __(ucfirst($backup->type)) }}</td>
                                <td class="td text-end">{{ $bytes($backup->size) }}</td>
                                <td class="td">
                                    <span class="{{ $backup->status === 'completed' ? 'badge-success' : 'badge-warning' }}">{{ __(ucfirst($backup->status)) }}</span>
                                </td>
                                <td class="td">{{ $backup->creator?->name ?? '—' }}</td>
                                <td class="td">{{ $backup->created_at?->format('d M Y H:i') }}</td>
                                <td class="td">
                                    <div class="flex flex-wrap items-center justify-end gap-1">
                                        <a href="{{ route('backups.download', $backup) }}" class="btn-ghost btn-sm">{{ __('Download') }}</a>
                                        <form method="POST" action="{{ route('backups.verify', $backup) }}">
                                            @csrf
                                            <button type="submit" class="btn-ghost btn-sm">{{ __('Verify') }}</button>
                                        </form>
                                        @if (auth()->user()->isAdmin())
                                            <form method="POST" action="{{ route('backups.restore', $backup) }}" data-confirm="{{ __('Restore this backup? Current data will be replaced.') }}">
                                                @csrf
                                                <button type="submit" class="btn-ghost btn-sm text-amber-600 dark:text-amber-400">{{ __('Restore') }}</button>
                                            </form>
                                            <form method="POST" action="{{ route('backups.destroy', $backup) }}" data-confirm="{{ __('Delete this backup?') }}">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn-ghost btn-sm text-rose-600 dark:text-rose-400">{{ __('Delete') }}</button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="td py-12 text-center text-slate-500">{{ __('No backups yet.') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($backups->hasPages())
                <div class="border-t border-slate-200 px-4 py-3 dark:border-slate-800">
                    {{ $backups->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
