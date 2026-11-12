<?php

use App\Models\Setting;

if (! function_exists('setting')) {
    function setting(string $key, $default = null)
    {
        try {
            return Setting::get($key, $default);
        } catch (\Throwable $e) {
            return $default;
        }
    }
}

if (! function_exists('money')) {
    function money($amount, bool $withSymbol = true): string
    {
        $formatted = number_format((float) $amount, 2);
        $symbol = setting('currency_symbol', 'Rs.');

        return $withSymbol ? $symbol.' '.$formatted : $formatted;
    }
}

if (! function_exists('balance_text')) {
    function balance_text($balance): string
    {
        $balance = (float) $balance;
        if ($balance > 0) {
            return __('Payable').': '.money($balance);
        }
        if ($balance < 0) {
            return __('Receivable').': '.money(abs($balance));
        }

        return __('Settled');
    }
}

if (! function_exists('initials')) {
    function initials(string $name): string
    {
        $parts = preg_split('/\s+/', trim($name));

        return strtoupper(substr($parts[0] ?? '', 0, 1).substr($parts[1] ?? '', 0, 1));
    }
}
