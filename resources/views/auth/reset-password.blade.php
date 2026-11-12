@extends('layouts.guest')

@section('title', __('Reset Password'))

@section('content')
    <div class="card card-body">
        <div class="mb-6 text-center">
            <h2 class="text-xl font-bold text-slate-900 dark:text-white">{{ __('Reset Password') }}</h2>
        </div>

        <form method="POST" action="{{ route('password.update') }}" class="space-y-4">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">

            <div>
                <label for="email" class="label">{{ __('Email') }}</label>
                <input id="email" name="email" type="email" value="{{ old('email', $email) }}" required autofocus autocomplete="email" class="input @error('email') border-rose-500 @enderror">
                @error('email')
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

            <button type="submit" class="btn-primary w-full">{{ __('Reset Password') }}</button>
        </form>
    </div>
@endsection
