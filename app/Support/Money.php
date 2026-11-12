<?php

namespace App\Support;

class Money
{
    public static function normalize(mixed $value): string
    {
        if ($value === null || $value === '') {
            return '0.00';
        }

        $value = str_replace([',', ' ', 'Rs', 'PKR', '₨'], '', (string) $value);
        $value = preg_replace('/[^0-9.\-]/', '', $value);

        if ($value === '' || $value === '-' || $value === '.') {
            return '0.00';
        }

        return bcadd($value, '0', 2);
    }

    public static function add(mixed ...$values): string
    {
        $result = '0';
        foreach ($values as $value) {
            $result = bcadd($result, static::normalize($value), 2);
        }

        return $result;
    }

    public static function sub(mixed $a, mixed $b): string
    {
        return bcsub(static::normalize($a), static::normalize($b), 2);
    }

    public static function mul(mixed $a, mixed $b, int $scale = 2): string
    {
        return bcmul(static::normalize($a), static::normalize($b), $scale);
    }

    public static function div(mixed $a, mixed $b, int $scale = 2): string
    {
        if (bccomp(static::normalize($b), '0', 2) === 0) {
            return '0.00';
        }

        return bcdiv(static::normalize($a), static::normalize($b), $scale);
    }

    public static function cmp(mixed $a, mixed $b): int
    {
        return bccomp(static::normalize($a), static::normalize($b), 2);
    }

    public static function positive(mixed $value): string
    {
        return static::cmp($value, 0) > 0 ? static::normalize($value) : '0.00';
    }

    public static function negative(mixed $value): string
    {
        return static::cmp($value, 0) < 0 ? static::normalize($value) : '0.00';
    }

    public static function abs(mixed $value): string
    {
        $value = static::normalize($value);

        return bccomp($value, '0', 2) < 0 ? bcmul($value, '-1', 2) : $value;
    }

    public static function format(mixed $value, bool $symbol = true): string
    {
        $value = static::normalize($value);
        $negative = bccomp($value, '0', 2) < 0;
        $value = static::abs($value);
        [$whole, $decimal] = explode('.', $value);
        $whole = preg_replace('/\B(?=(\d{3})+(?!\d))/', ',', $whole);

        return ($negative ? '-' : '').($symbol ? 'Rs. ' : '').$whole.'.'.$decimal;
    }

    public static function label(mixed $value): array
    {
        $sign = static::cmp($value, 0);

        if ($sign > 0) {
            return ['label' => 'Payable', 'class' => 'danger'];
        }
        if ($sign < 0) {
            return ['label' => 'Receivable', 'class' => 'success'];
        }

        return ['label' => 'Settled', 'class' => 'secondary'];
    }
}
