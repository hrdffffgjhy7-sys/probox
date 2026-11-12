<?php

namespace App\Support;

use App\Models\Setting;

class BusinessSettings
{
    public static function all(): array
    {
        return [
            'name' => Setting::get('business_name', config('app.name')),
            'phone' => Setting::get('business_phone', ''),
            'address' => Setting::get('business_address', ''),
            'currency' => Setting::get('currency_symbol', 'Rs.'),
        ];
    }
}
