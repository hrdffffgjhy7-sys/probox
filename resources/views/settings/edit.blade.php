@extends('layouts.app')

@section('title', __('Settings'))
@section('subtitle', __('Business, invoice and security settings.'))

@section('content')
    <form method="POST" action="{{ route('settings.update') }}" class="space-y-6">
        @csrf
        @method('PUT')

        <div class="grid gap-6 xl:grid-cols-3">
            <div class="card xl:col-span-2">
                <div class="border-b border-slate-200 px-5 py-4 dark:border-slate-800">
                    <h2 class="section-title">{{ __('Business details') }}</h2>
                </div>
                <div class="card-body grid gap-4 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <label for="business_name" class="label">{{ __('Business Name') }} <span class="text-rose-500">*</span></label>
                        <input id="business_name" name="business_name" type="text" value="{{ old('business_name', $settings['business_name']) }}" required class="input @error('business_name') border-rose-500 @enderror">
                        @error('business_name') <p class="mt-1 text-sm text-rose-600 dark:text-rose-400">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="business_phone" class="label">{{ __('Phone') }}</label>
                        <input id="business_phone" name="business_phone" type="text" value="{{ old('business_phone', $settings['business_phone']) }}" class="input">
                    </div>
                    <div>
                        <label for="business_address" class="label">{{ __('Address') }}</label>
                        <input id="business_address" name="business_address" type="text" value="{{ old('business_address', $settings['business_address']) }}" class="input">
                    </div>
                    <div>
                        <label for="currency_symbol" class="label">{{ __('Currency Symbol') }} <span class="text-rose-500">*</span></label>
                        <input id="currency_symbol" name="currency_symbol" type="text" value="{{ old('currency_symbol', $settings['currency_symbol']) }}" required class="input @error('currency_symbol') border-rose-500 @enderror">
                        @error('currency_symbol') <p class="mt-1 text-sm text-rose-600 dark:text-rose-400">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="invoice_prefix" class="label">{{ __('Invoice Prefix') }} <span class="text-rose-500">*</span></label>
                        <input id="invoice_prefix" name="invoice_prefix" type="text" value="{{ old('invoice_prefix', $settings['invoice_prefix']) }}" required class="input @error('invoice_prefix') border-rose-500 @enderror">
                        @error('invoice_prefix') <p class="mt-1 text-sm text-rose-600 dark:text-rose-400">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="border-b border-slate-200 px-5 py-4 dark:border-slate-800">
                    <h2 class="section-title">{{ __('System') }}</h2>
                </div>
                <div class="card-body space-y-4">
                    <div>
                        <label for="default_locale" class="label">{{ __('Default Language') }} <span class="text-rose-500">*</span></label>
                        <select name="default_locale" id="default_locale" class="select">
                            <option value="en" @selected(old('default_locale', $settings['default_locale']) === 'en')>{{ __('English') }}</option>
                            <option value="ur" @selected(old('default_locale', $settings['default_locale']) === 'ur')>{{ __('Urdu') }}</option>
                        </select>
                    </div>
                    <div>
                        <label for="session_timeout" class="label">{{ __('Session Timeout (minutes)') }} <span class="text-rose-500">*</span></label>
                        <input id="session_timeout" name="session_timeout" type="number" min="5" max="480" value="{{ old('session_timeout', $settings['session_timeout']) }}" required class="input @error('session_timeout') border-rose-500 @enderror">
                        @error('session_timeout') <p class="mt-1 text-sm text-rose-600 dark:text-rose-400">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="backup_retention" class="label">{{ __('Backup Retention (days)') }} <span class="text-rose-500">*</span></label>
                        <input id="backup_retention" name="backup_retention" type="number" min="3" max="365" value="{{ old('backup_retention', $settings['backup_retention']) }}" required class="input @error('backup_retention') border-rose-500 @enderror">
                        @error('backup_retention') <p class="mt-1 text-sm text-rose-600 dark:text-rose-400">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="low_stock_threshold" class="label">{{ __('Low Stock Threshold') }} <span class="text-rose-500">*</span></label>
                        <input id="low_stock_threshold" name="low_stock_threshold" type="number" step="0.001" min="0" value="{{ old('low_stock_threshold', $settings['low_stock_threshold']) }}" required class="input @error('low_stock_threshold') border-rose-500 @enderror">
                        @error('low_stock_threshold') <p class="mt-1 text-sm text-rose-600 dark:text-rose-400">{{ $message }}</p> @enderror
                    </div>
                    <div class="rounded-lg border border-slate-200 p-3 text-sm dark:border-slate-800">
                        <p class="font-medium">{{ __('Database') }}</p>
                        <p class="mt-1 {{ $dbHealthy ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }}">
                            {{ $dbHealthy ? __('Connection healthy') : __('Connection problem detected') }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <div class="flex items-center justify-end gap-3">
            <a href="{{ route('dashboard') }}" class="btn-secondary">{{ __('Cancel') }}</a>
            <button type="submit" class="btn-primary">{{ __('Save Settings') }}</button>
        </div>
    </form>
@endsection
