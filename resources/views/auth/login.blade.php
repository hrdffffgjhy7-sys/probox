@extends('layouts.guest')

@section('title', __('Login'))

@section('content')
    <div class="card card-body">
        <div class="mb-6 text-center">
            <h2 class="text-xl font-bold text-slate-900 dark:text-white">{{ __('Sign in to your account') }}</h2>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ __('Grocery Supplier Accounting') }}</p>
        </div>

        <form method="POST" action="{{ route('login.store') }}" class="space-y-4">
            @csrf

            <div>
                <label for="username" class="label">{{ __('Username') }}</label>
                <input id="username" name="username" type="text" value="{{ old('username') }}" required autofocus autocomplete="username" class="input @error('username') border-rose-500 @enderror">
                @error('username')
                    <p class="mt-1 text-sm text-rose-600 dark:text-rose-400">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="password" class="label">{{ __('Password') }}</label>
                <input id="password" name="password" type="password" required autocomplete="current-password" class="input @error('password') border-rose-500 @enderror">
                @error('password')
                    <p class="mt-1 text-sm text-rose-600 dark:text-rose-400">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex items-center justify-between">
                <label class="flex items-center gap-2 text-sm text-slate-600 dark:text-slate-300">
                    <input type="checkbox" name="remember" value="1" @checked(old('remember')) class="h-4 w-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500 dark:border-slate-700 dark:bg-slate-900">
                    {{ __('Remember me') }}
                </label>
                <a href="{{ route('password.request') }}" class="link text-sm">{{ __('Forgot password?') }}</a>
            </div>

            <button type="submit" class="btn-primary w-full">{{ __('Sign in') }}</button>
        </form>

        @if (app()->environment('local'))
            <div class="mt-6 rounded-xl border border-dashed border-slate-300 bg-slate-50 px-4 py-3 text-xs text-slate-600 dark:border-slate-700 dark:bg-slate-800/50 dark:text-slate-300">
                <p class="font-semibold">{{ __('Demo credentials') }}</p>
                <p class="mt-1">{{ __('Admin') }}: <span class="font-mono">admin / admin123</span></p>
                <p>{{ __('Staff') }}: <span class="font-mono">staff / staff123</span></p>
            </div>
        @endif
    </div>
@endsection
