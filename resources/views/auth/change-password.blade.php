@extends('layouts.app')

@section('title', __('Change Password'))

@section('content')
    <div class="mx-auto max-w-xl">
        @if (auth()->user()?->must_change_password)
            <div class="mb-5 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800 dark:border-amber-500/30 dark:bg-amber-500/10 dark:text-amber-200">
                {{ __('You must change your password before continuing.') }}
            </div>
        @endif

        <div class="card">
            <div class="card-body">
                <div class="mb-5 flex items-center gap-3">
                    <span class="flex h-11 w-11 items-center justify-center rounded-full bg-emerald-100 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
                        </svg>
                    </span>
                    <div>
                        <h2 class="section-title">{{ __('Change Password') }}</h2>
                        <p class="muted">{{ __('At least 8 characters with letters and numbers.') }}</p>
                    </div>
                </div>

                <form method="POST" action="{{ route('password.change.update') }}" class="space-y-4">
                    @csrf

                    <div>
                        <label for="current_password" class="label">{{ __('Current Password') }}</label>
                        <input id="current_password" name="current_password" type="password" required autocomplete="current-password" class="input @error('current_password') border-rose-500 @enderror">
                        @error('current_password')
                            <p class="mt-1 text-sm text-rose-600 dark:text-rose-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="password" class="label">{{ __('New Password') }}</label>
                        <input id="password" name="password" type="password" required autocomplete="new-password" class="input @error('password') border-rose-500 @enderror">
                        @error('password')
                            <p class="mt-1 text-sm text-rose-600 dark:text-rose-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="password_confirmation" class="label">{{ __('Confirm Password') }}</label>
                        <input id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password" class="input">
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-2">
                        <a href="{{ route('dashboard') }}" class="btn-secondary">{{ __('Cancel') }}</a>
                        <button type="submit" class="btn-primary">{{ __('Update') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
