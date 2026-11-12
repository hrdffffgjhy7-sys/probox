@php
    $currentUser = auth()->user();
    $business = \App\Support\BusinessSettings::all();
    $layoutNotifications = \App\Models\Notification::query()
        ->where(fn ($q) => $q->whereNull('user_id')->orWhere('user_id', $currentUser?->id))
        ->orderByDesc('id')
        ->limit(5)
        ->get();
    $layoutUnread = \App\Models\Notification::query()
        ->where(fn ($q) => $q->whereNull('user_id')->orWhere('user_id', $currentUser?->id))
        ->whereNull('read_at')
        ->count();

    $navSections = [
        [
            'label' => __('Overview'),
            'items' => [
                ['route' => 'dashboard', 'pattern' => 'dashboard', 'label' => __('Dashboard'), 'permission' => 'dashboard.view', 'icon' => 'M2.25 12l8.954-8.955a1.126 1.126 0 011.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75'],
            ],
        ],
        [
            'label' => __('Suppliers'),
            'items' => [
                ['route' => 'suppliers.index', 'pattern' => 'suppliers.*', 'label' => __('Suppliers'), 'permission' => 'suppliers.view', 'icon' => 'M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z'],
                ['route' => 'khata.index', 'pattern' => 'khata.*', 'label' => __('Supplier Khata'), 'permission' => 'ledger.view', 'icon' => 'M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25'],
                ['route' => 'agencies.index', 'pattern' => 'agencies.*', 'label' => __('Agencies'), 'permission' => 'agencies.manage', 'icon' => 'M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21'],
                ['route' => 'salesmen.index', 'pattern' => 'salesmen.*', 'label' => __('Salesmen'), 'permission' => 'salesmen.manage', 'icon' => 'M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z'],
            ],
        ],
        [
            'label' => __('Transactions'),
            'items' => [
                ['route' => 'purchases.index', 'pattern' => 'purchases.*', 'label' => __('Purchases'), 'permission' => 'purchases.view', 'icon' => 'M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 00-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 00-16.536-1.84M7.5 14.25L5.106 5.272M6 20.25a.75.75 0 11-1.5 0 .75.75 0 011.5 0zm12.75 0a.75.75 0 11-1.5 0 .75.75 0 011.5 0z'],
                ['route' => 'payments.index', 'pattern' => 'payments.*', 'label' => __('Payments'), 'permission' => 'payments.view', 'icon' => 'M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18V10.5zm-12 0h.008v.008H6V10.5z'],
                ['route' => 'expenses.index', 'pattern' => 'expenses.*', 'label' => __('Expenses'), 'permission' => 'expenses.view', 'icon' => 'M9 14.25l6-6m4.5-3.493V21.75l-3.75-1.5-3.75 1.5-3.75-1.5-3.75 1.5V4.757c0-1.108.806-2.057 1.907-2.185a48.507 48.507 0 0111.186 0c1.1.128 1.907 1.077 1.907 2.185zM9.75 9h.008v.008H9.75V9zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm4.125 4.5h.008v.008h-.008V13.5zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z'],
                ['route' => 'products.index', 'pattern' => 'products.*', 'label' => __('Products'), 'permission' => 'products.manage', 'icon' => 'M21 7.5l-9-5.25L3 7.5m18 0l-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9'],
            ],
        ],
        [
            'label' => __('Finance'),
            'items' => [
                ['route' => 'payables.index', 'pattern' => 'payables.*', 'label' => __('Payables'), 'permission' => 'payables.view', 'icon' => 'M12 19.5v-15m0 0l-6.75 6.75M12 4.5l6.75 6.75'],
                ['route' => 'receivables.index', 'pattern' => 'receivables.*', 'label' => __('Receivables'), 'permission' => 'receivables.view', 'icon' => 'M12 4.5v15m0 0l6.75-6.75M12 19.5l-6.75-6.75'],
                ['route' => 'reports.index', 'pattern' => 'reports.*', 'label' => __('Reports'), 'permission' => 'reports.view', 'icon' => 'M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z'],
            ],
        ],
        [
            'label' => __('Administration'),
            'items' => [
                ['route' => 'backups.index', 'pattern' => 'backups.*', 'label' => __('Backup & Restore'), 'permission' => 'backups.manage', 'icon' => 'M12 16.5V9.75m0 0l3 3m-3-3l-3 3M6.75 19.5a4.5 4.5 0 01-1.41-8.775 5.25 5.25 0 0110.233-2.33 3 3 0 013.758 3.848A3.752 3.752 0 0118 19.5H6.75z'],
                ['route' => 'users.index', 'pattern' => 'users.*', 'label' => __('User Management'), 'permission' => 'users.manage', 'icon' => 'M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z'],
                ['route' => 'settings.edit', 'pattern' => 'settings.*', 'label' => __('Settings'), 'permission' => 'settings.manage', 'icon' => 'M10.343 3.94c.09-.542.56-.94 1.11-.94h1.093c.55 0 1.02.398 1.11.94l.149.894c.07.424.384.764.78.93.398.164.855.142 1.205-.108l.737-.527a1.125 1.125 0 011.45.12l.773.774c.39.389.44 1.002.12 1.45l-.527.737c-.25.35-.272.806-.107 1.204.165.397.505.71.93.78l.893.15c.543.09.94.56.94 1.109v1.094c0 .55-.397 1.02-.94 1.11l-.893.149c-.425.07-.765.383-.93.78-.165.398-.143.854.107 1.204l.527.738c.32.447.269 1.06-.12 1.45l-.774.773a1.125 1.125 0 01-1.449.12l-.738-.527c-.35-.25-.806-.272-1.203-.107-.397.165-.71.505-.781.929l-.149.894c-.09.542-.56.94-1.11.94h-1.094c-.55 0-1.019-.398-1.11-.94l-.148-.894c-.071-.424-.384-.764-.781-.93-.398-.164-.854-.142-1.204.108l-.738.527c-.447.32-1.06.269-1.45-.12l-.773-.774a1.125 1.125 0 01-.12-1.45l.527-.737c.25-.35.273-.806.108-1.204-.165-.397-.505-.71-.93-.78l-.894-.15c-.542-.09-.94-.56-.94-1.109v-1.094c0-.55.398-1.02.94-1.11l.894-.149c.424-.07.765-.383.93-.78.165-.398.143-.854-.108-1.204l-.526-.738a1.125 1.125 0 01.12-1.45l.773-.773a1.125 1.125 0 011.45-.12l.737.527c.35.25.807.272 1.204.107.397-.165.71-.505.78-.929l.15-.894zM15 12a3 3 0 11-6 0 3 3 0 016 0z'],
                ['route' => 'audit.index', 'pattern' => 'audit.*', 'label' => __('Audit Log'), 'permission' => 'audit.view', 'icon' => 'M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25z'],
            ],
        ],
    ];
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ $dir ?? 'ltr' }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', __('Dashboard')) &middot; {{ $business['name'] }}</title>
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
    @stack('head')
</head>
<body class="min-h-full bg-slate-100 text-slate-800 antialiased dark:bg-slate-950 dark:text-slate-100">
<div class="flex min-h-screen">
    <div id="sidebar-backdrop" class="fixed inset-0 z-30 hidden bg-slate-900/50 backdrop-blur-sm lg:hidden"></div>

    <aside id="sidebar" class="fixed inset-y-0 left-0 z-40 flex w-72 -translate-x-full flex-col border-e border-slate-200 bg-white transition-transform duration-200 dark:border-slate-800 dark:bg-slate-900 lg:static lg:inset-auto lg:z-auto lg:translate-x-0 lg:border-e">
        <div class="flex h-16 items-center gap-3 border-b border-slate-200 px-5 dark:border-slate-800">
            <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-600 text-lg font-black text-white">K</span>
            <div class="min-w-0">
                <p class="truncate text-sm font-bold text-slate-900 dark:text-white">{{ $business['name'] }}</p>
                <p class="truncate text-xs text-slate-500 dark:text-slate-400">{{ __('Grocery Supplier Accounting') }}</p>
            </div>
            <button type="button" class="btn-ghost ms-auto lg:hidden" data-sidebar-toggle aria-label="{{ __('Close') }}">&times;</button>
        </div>

        <nav class="flex-1 space-y-6 overflow-y-auto px-3 py-5">
            @foreach ($navSections as $section)
                @php
                    $visible = collect($section['items'])->filter(fn ($item) => auth()->user()->can($item['permission']));
                @endphp
                @if ($visible->isNotEmpty())
                    <div>
                        <p class="px-3 pb-2 text-[0.65rem] font-semibold uppercase tracking-widest text-slate-400 dark:text-slate-500">{{ $section['label'] }}</p>
                        <ul class="space-y-1">
                            @foreach ($visible as $item)
                                @php $active = request()->routeIs($item['pattern']); @endphp
                                <li>
                                    <a href="{{ route($item['route']) }}" class="group flex items-center gap-3 rounded-xl px-3 py-2 text-sm font-medium transition {{ $active ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900 dark:text-slate-300 dark:hover:bg-slate-800 dark:hover:text-white' }}">
                                        <svg class="h-5 w-5 shrink-0 {{ $active ? 'text-emerald-600 dark:text-emerald-400' : 'text-slate-400 group-hover:text-slate-500 dark:text-slate-500' }}" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="{{ $item['icon'] }}" />
                                        </svg>
                                        <span class="truncate">{{ $item['label'] }}</span>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            @endforeach
        </nav>

        <div class="border-t border-slate-200 p-3 dark:border-slate-800">
            <div class="flex items-center gap-3 rounded-xl bg-slate-50 px-3 py-2 dark:bg-slate-800/60">
                <span class="flex h-9 w-9 items-center justify-center rounded-full bg-emerald-100 text-sm font-bold text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-300">
                    {{ mb_strtoupper(mb_substr($currentUser?->name ?? '?', 0, 1)) }}
                </span>
                <div class="min-w-0 flex-1">
                    <p class="truncate text-sm font-semibold text-slate-900 dark:text-white">{{ $currentUser?->name }}</p>
                    <p class="truncate text-xs text-slate-500 dark:text-slate-400">{{ $currentUser?->roleModel?->display_name ?? ucfirst($currentUser?->role ?? '') }}</p>
                </div>
            </div>
        </div>
    </aside>

    <div class="flex min-w-0 flex-1 flex-col">
        <header class="sticky top-0 z-20 border-b border-slate-200 bg-white/90 backdrop-blur dark:border-slate-800 dark:bg-slate-900/90">
            <div class="flex h-16 items-center gap-3 px-4 sm:px-6 lg:px-8">
                <button type="button" class="btn-ghost lg:hidden" data-sidebar-toggle aria-label="{{ __('Menu') }}">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5M3.75 17.25h16.5" />
                    </svg>
                </button>

                <div class="min-w-0 flex-1">
                    <h1 class="truncate text-base font-semibold text-slate-900 dark:text-white">@yield('title', __('Dashboard'))</h1>
                    @hasSection('subtitle')
                        <p class="truncate text-xs text-slate-500 dark:text-slate-400">@yield('subtitle')</p>
                    @endif
                </div>

                <form action="{{ route('search') }}" method="GET" class="hidden md:block">
                    <label class="relative block">
                        <span class="pointer-events-none absolute inset-y-0 start-0 flex items-center ps-3 text-slate-400">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 11a6 6 0 11-12 0 6 6 0 0112 0z" />
                            </svg>
                        </span>
                        <input type="search" name="q" value="{{ request()->routeIs('search') ? request('q') : '' }}" placeholder="{{ __('Global search') }}" class="input w-56 ps-9 lg:w-72">
                    </label>
                </form>

                <a href="{{ route('language.switch', app()->getLocale() === 'en' ? 'ur' : 'en') }}" class="btn-secondary btn-sm" title="{{ __('Language') }}">
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

                <div class="relative">
                    <button type="button" class="btn-ghost relative" data-dropdown-toggle="notifications-menu" title="{{ __('Notifications') }}">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75V9a6 6 0 00-12 0v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" />
                        </svg>
                        @if ($layoutUnread > 0)
                            <span class="absolute -end-1 -top-1 flex h-5 min-w-5 items-center justify-center rounded-full bg-rose-600 px-1 text-[0.65rem] font-bold text-white">{{ $layoutUnread }}</span>
                        @endif
                    </button>
                    <div id="notifications-menu" data-dropdown class="absolute end-0 mt-2 hidden w-80 overflow-hidden rounded-xl border border-slate-200 bg-white shadow-xl dark:border-slate-700 dark:bg-slate-900">
                        <div class="border-b border-slate-200 px-4 py-3 dark:border-slate-800">
                            <p class="text-sm font-semibold text-slate-900 dark:text-white">{{ __('Notifications') }}</p>
                        </div>
                        <div class="max-h-80 divide-y divide-slate-100 overflow-y-auto dark:divide-slate-800">
                            @forelse ($layoutNotifications as $notification)
                                <div class="px-4 py-3">
                                    <p class="text-sm font-medium text-slate-800 dark:text-slate-100">{{ $notification->title }}</p>
                                    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ $notification->body }}</p>
                                    <p class="mt-1 text-[0.65rem] text-slate-400">{{ $notification->created_at?->diffForHumans() }}</p>
                                </div>
                            @empty
                                <p class="px-4 py-6 text-center text-sm text-slate-500 dark:text-slate-400">{{ __('No notifications') }}</p>
                            @endforelse
                        </div>
                    </div>
                </div>

                <div class="relative">
                    <button type="button" class="flex items-center gap-2 rounded-full border border-slate-200 bg-white py-1 pe-2 ps-1 dark:border-slate-700 dark:bg-slate-900" data-dropdown-toggle="user-menu">
                        <span class="flex h-8 w-8 items-center justify-center rounded-full bg-emerald-100 text-sm font-bold text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-300">
                            {{ mb_strtoupper(mb_substr($currentUser?->name ?? '?', 0, 1)) }}
                        </span>
                        <span class="hidden text-sm font-medium text-slate-700 sm:block dark:text-slate-200">{{ $currentUser?->name }}</span>
                        <svg class="h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                        </svg>
                    </button>
                    <div id="user-menu" data-dropdown class="absolute end-0 mt-2 hidden w-56 overflow-hidden rounded-xl border border-slate-200 bg-white py-1 shadow-xl dark:border-slate-700 dark:bg-slate-900">
                        <div class="border-b border-slate-100 px-4 py-3 dark:border-slate-800">
                            <p class="truncate text-sm font-semibold text-slate-900 dark:text-white">{{ $currentUser?->name }}</p>
                            <p class="truncate text-xs text-slate-500 dark:text-slate-400">{{ '@'.$currentUser?->username }}</p>
                        </div>
                        <a href="{{ route('password.change') }}" class="block px-4 py-2 text-sm text-slate-700 hover:bg-slate-50 dark:text-slate-200 dark:hover:bg-slate-800">{{ __('Change Password') }}</a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="block w-full px-4 py-2 text-start text-sm text-rose-600 hover:bg-rose-50 dark:text-rose-400 dark:hover:bg-rose-500/10">{{ __('Logout') }}</button>
                        </form>
                    </div>
                </div>
            </div>
        </header>

        <main class="flex-1 px-4 py-6 sm:px-6 lg:px-8">
            @include('partials.flash')
            @yield('content')
        </main>

        <footer class="border-t border-slate-200 px-4 py-4 text-center text-xs text-slate-400 dark:border-slate-800 dark:text-slate-500 sm:px-6 lg:px-8">
            {{ $business['name'] }} &middot; {{ __('All financial records are kept in an immutable audit trail.') }}
        </footer>
    </div>
</div>
@stack('scripts')
</body>
</html>
