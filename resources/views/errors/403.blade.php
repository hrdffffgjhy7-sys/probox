@extends('layouts.guest')

@section('title', __('Access denied'))

@section('content')
    <div class="card card-body text-center">
        <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-rose-100 text-rose-600 dark:bg-rose-500/15 dark:text-rose-400">
            <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
            </svg>
        </div>
        <h2 class="mt-4 text-lg font-semibold text-slate-900 dark:text-white">{{ __('Access denied') }}</h2>
        <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">{{ __('You do not have permission to view this page.') }}</p>
        <div class="mt-6">
            <a href="{{ route('dashboard') }}" class="btn-primary">{{ __('Back') }}</a>
        </div>
    </div>
@endsection
