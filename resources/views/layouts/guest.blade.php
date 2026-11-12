@php
    $business = \App\Support\BusinessSettings::all();
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ $dir ?? 'ltr' }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', __('Login')) &middot; {{ $business['name'] }}</title>
    <script>
        (function () {
            try {
                var theme = localStorage.getItem('theme');
                if (theme === 'dark' || (!theme && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
                    document.documentElement.classList.add('dark');
                }
            } catch (e) {}
        })();
    </script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-full bg-gradient-to-br from-slate-100 via-white to-emerald-50 text-slate-800 antialiased dark:from-slate-950 dark:via-slate-950 dark:to-emerald-950/40 dark:text-slate-100">
<div class="flex min-h-screen flex-col">
    <header class="flex items-center justify-between px-5 py-5 sm:px-8">
        <div class="flex items-center gap-3">
            <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-600 text-lg font-black text-white">K</span>
            <div>
                <p class="text-sm font-bold text-slate-900 dark:text-white">{{ $business['name'] }}</p>
                <p class="text-xs text-slate-500 dark:text-slate-400">{{ __('Grocery Supplier Accounting') }}</p>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('language.switch', app()->getLocale() === 'en' ? 'ur' : 'en') }}" class="btn-secondary btn-sm">
                {{ app()->getLocale() === 'en' ? __('Urdu') : __('English') }}
            </a>
            <button type="button" class="btn-ghost" data-theme-toggle title="{{ __('Dark Mode') }}">
                <svg class="h-5 w-5 dark:hidden" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.718 9.718 0 0118 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 003 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 009.002-5.998z" />
                </svg>
                <svg class="hidden h-5 w-5 dark:block" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m6.364.386l-1.591 1.591M21 12h-2.25m-.386 6.364l-1.591-1.591M12 18.75V21m-4.773-4.227l-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0z" />
                </svg>
            </button>
        </div>
    </header>

    <main class="flex flex-1 items-center justify-center px-4 py-8 sm:px-6">
        <div class="w-full max-w-md">
            @include('partials.flash')
            @yield('content')
        </div>
    </main>

    <footer class="px-5 py-5 text-center text-xs text-slate-400 dark:text-slate-500">
        {{ $business['name'] }} &middot; {{ date('Y') }}
    </footer>
</div>
</body>
</html>
