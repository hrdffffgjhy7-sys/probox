@extends('layouts.app')

@section('title', __('Users'))
@section('subtitle', __('Manage system users and their roles.'))

@section('content')
    <div class="space-y-6">
        <div class="card">
            <div class="card-body">
                <form method="GET" action="{{ route('users.index') }}" class="flex flex-wrap items-end gap-3">
                    <div class="min-w-48 flex-1">
                        <label for="q" class="label">{{ __('Search') }}</label>
                        <input type="search" name="q" id="q" value="{{ request('q') }}" placeholder="{{ __('Name, username or email') }}" class="input">
                    </div>
                    <div>
                        <label for="role" class="label">{{ __('Role') }}</label>
                        <select name="role" id="role" class="select">
                            <option value="">{{ __('All roles') }}</option>
                            @foreach ($roles as $role)
                                <option value="{{ $role->name }}" @selected(request('role') === $role->name)>{{ $role->display_name ?? $role->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="status" class="label">{{ __('Status') }}</label>
                        <select name="status" id="status" class="select">
                            <option value="">{{ __('All') }}</option>
                            <option value="active" @selected(request('status') === 'active')>{{ __('Active') }}</option>
                            <option value="inactive" @selected(request('status') === 'inactive')>{{ __('Inactive') }}</option>
                        </select>
                    </div>
                    <button type="submit" class="btn-primary">{{ __('Filter') }}</button>
                    <a href="{{ route('users.index') }}" class="btn-secondary">{{ __('Reset') }}</a>
                    <a href="{{ route('users.create') }}" class="btn-primary ms-auto">{{ __('Add User') }}</a>
                </form>
            </div>
        </div>

        <div class="card overflow-hidden">
            <div class="overflow-x-auto">
                <table class="table">
                    <thead class="bg-slate-50 dark:bg-slate-800/50">
                        <tr>
                            <th class="th">{{ __('Name') }}</th>
                            <th class="th">{{ __('Username') }}</th>
                            <th class="th">{{ __('Email') }}</th>
                            <th class="th">{{ __('Role') }}</th>
                            <th class="th">{{ __('Locale') }}</th>
                            <th class="th">{{ __('Status') }}</th>
                            <th class="th">{{ __('Last Login') }}</th>
                            <th class="th text-end">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @forelse ($users as $user)
                            <tr>
                                <td class="td font-medium">
                                    {{ $user->name }}
                                    @if ($user->id === auth()->id())
                                        <span class="badge-neutral ms-2">{{ __('You') }}</span>
                                    @endif
                                </td>
                                <td class="td">{{ $user->username }}</td>
                                <td class="td">{{ $user->email ?? '—' }}</td>
                                <td class="td">{{ $user->roleModel?->display_name ?? ucfirst($user->role) }}</td>
                                <td class="td">{{ strtoupper($user->locale ?? 'en') }}</td>
                                <td class="td">
                                    @if ($user->is_active)
                                        <span class="badge-success">{{ __('Active') }}</span>
                                    @else
                                        <span class="badge-neutral">{{ __('Inactive') }}</span>
                                    @endif
                                </td>
                                <td class="td">{{ $user->last_login_at?->format('d M Y H:i') ?? '—' }}</td>
                                <td class="td">
                                    <div class="flex items-center justify-end gap-1">
                                        <a href="{{ route('users.edit', $user) }}" class="btn-ghost btn-sm">{{ __('Edit') }}</a>
                                        @if ($user->id !== auth()->id())
                                            <form method="POST" action="{{ route('users.destroy', $user) }}" data-confirm="{{ __('Delete this user?') }}">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn-ghost btn-sm text-rose-600 dark:text-rose-400">{{ __('Delete') }}</button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="td py-12 text-center text-slate-500">{{ __('No users found.') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($users->hasPages())
                <div class="border-t border-slate-200 px-4 py-3 dark:border-slate-800">
                    {{ $users->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
