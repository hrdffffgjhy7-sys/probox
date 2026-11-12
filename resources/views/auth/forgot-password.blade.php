@extends('layouts.guest')

@section('title', __('Forgot your password?'))

@section('content')
    <div class="card card-body">
        <div class="mb-6 text-center">
            <h2 class="text-xl font-bold text-slate-900 dark:text-white">{{ __('Forgot your password?') }}</h2>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ __('Enter your email address') }}</p>
        </div>

        <form method="POST" action="{{ route('password.email') }}" class="space-y-4">
            @csrf

            <div>
                <label for="email" class="label">{{ __('Email') }}</label>
                <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus autocomplete="email" class="input @error('email') border-rose-500 @enderror">
                @error('email')
                    <p class="mt-1 text-sm text-rose-600 dark:text-rose-400">{{ $message }}</p>
                @enderror
            </div>

            <button type="submit" class="btn-primary w-full">{{ __('Send reset link') }}</button>
        </form>

        <p class="mt-6 text-center text-sm">
            <a href="{{ route('login') }}" class="link">{{ __('Back to login') }}</a>
        </p>
    </div>
@endsection
