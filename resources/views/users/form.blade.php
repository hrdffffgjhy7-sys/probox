@extends('layouts.app')

@php $editing = $user->exists; @endphp

@section('title', $editing ? __('Edit User') : __('Add User'))
@section('subtitle', __('User account and role assignment.'))

@section('content')
    <form method="POST" action="{{ $editing ? route('users.update', $user) : route('users.store') }}" class="space-y-6">
        @csrf
        @if ($editing)
            @method('PUT')
        @endif

        <div class="card">
            <div class="card-body grid gap-4 sm:grid-cols-2">
                <div>
                    <label for="name" class="label">{{ __('Name') }} <span class="text-rose-500">*</span></label>
                    <input id="name" name="name" type="text" value="{{ old('name', $user->name) }}" required class="input @error('name') border-rose-500 @enderror">
                    @error('name') <p class="mt-1 text-sm text-rose-600 dark:text-rose-400">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="username" class="label">{{ __('Username') }} <span class="text-rose-500">*</span></label>
                    <input id="username" name="username" type="text" value="{{ old('username', $user->username) }}" required class="input @error('username') border-rose-500 @enderror">
                    @error('username') <p class="mt-1 text-sm text-rose-600 dark:text-rose-400">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="email" class="label">{{ __('Email') }}</label>
                    <input id="email" name="email" type="email" value="{{ old('email', $user->email) }}" class="input @error('email') border-rose-500 @enderror">
                    @error('email') <p class="mt-1 text-sm text-rose-600 dark:text-rose-400">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="phone" class="label">{{ __('Phone') }}</label>
                    <input id="phone" name="phone" type="text" value="{{ old('phone', $user->phone) }}" class="input">
                </div>
                <div>
                    <label for="role_id" class="label">{{ __('Role') }} <span class="text-rose-500">*</span></label>
                    <select name="role_id" id="role_id" required class="select @error('role_id') border-rose-500 @enderror">
                        <option value="">{{ __('Select role') }}</option>
                        @foreach ($roles as $role)
                            <option value="{{ $role->id }}" @selected((string) old('role_id', $user->role_id) === (string) $role->id)>{{ $role->display_name ?? $role->name }}</option>
                        @endforeach
                    </select>
                    @error('role_id') <p class="mt-1 text-sm text-rose-600 dark:text-rose-400">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="locale" class="label">{{ __('Language') }}</label>
                    <select name="locale" id="locale" class="select">
                        <option value="en" @selected(old('locale', $user->locale ?? 'en') === 'en')>{{ __('English') }}</option>
                        <option value="ur" @selected(old('locale', $user->locale) === 'ur')>{{ __('Urdu') }}</option>
                    </select>
                </div>
                <div>
                    <label for="password" class="label">{{ __('Password') }} @if (! $editing)<span class="text-rose-500">*</span>@endif</label>
                    <input id="password" name="password" type="password" autocomplete="new-password" class="input @error('password') border-rose-500 @enderror">
                    @if ($editing)
                        <p class="mt-1 text-xs text-slate-500">{{ __('Leave blank to keep the current password.') }}</p>
                    @endif
                    @error('password') <p class="mt-1 text-sm text-rose-600 dark:text-rose-400">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="mt-6 flex items-center gap-2 text-sm">
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $user->is_active ?? true)) class="checkbox">
                        <span>{{ __('Active') }}</span>
                    </label>
                </div>
            </div>
        </div>

        <div class="flex items-center justify-end gap-3">
            <a href="{{ route('users.index') }}" class="btn-secondary">{{ __('Cancel') }}</a>
            <button type="submit" class="btn-primary">{{ $editing ? __('Update') : __('Save') }}</button>
        </div>
    </form>
@endsection
