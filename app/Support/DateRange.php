<?php

namespace App\Support;

use Carbon\Carbon;
use Illuminate\Http\Request;

class DateRange
{
    public static function resolve(Request $request): array
    {
        $period = $request->input('period', 'all');
        $from = $request->input('from');
        $to = $request->input('to');

        [$start, $end, $label] = match ($period) {
            'today' => [now()->startOfDay(), now()->endOfDay(), __('Today')],
            'yesterday' => [now()->subDay()->startOfDay(), now()->subDay()->endOfDay(), __('Yesterday')],
            'week' => [now()->startOfWeek(), now()->endOfWeek(), __('This Week')],
            'month' => [now()->startOfMonth(), now()->endOfMonth(), __('This Month')],
            'year' => [now()->startOfYear(), now()->endOfYear(), __('Year')],
            'custom' => [
                $from ? Carbon::parse($from)->startOfDay() : now()->startOfMonth(),
                $to ? Carbon::parse($to)->endOfDay() : now()->endOfDay(),
                __('Custom Range'),
            ],
            default => [null, null, __('All time')],
        };

        return [$start, $end, $label, $period];
    }

    public static function apply($query, array $range, string $column = 'created_at')
    {
        [$start, $end] = $range;

        return $query->when($start, fn ($q) => $q->where($column, '>=', $start))
            ->when($end, fn ($q) => $q->where($column, '<=', $end));
    }
}
