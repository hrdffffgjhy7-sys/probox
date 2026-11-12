<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SettingsController extends Controller
{
    public function edit()
    {
        abort_unless(auth()->user()->can('settings.manage'), 403);
        $settings = [
            'business_name' => Setting::get('business_name', config('app.name')),
            'business_phone' => Setting::get('business_phone', ''),
            'business_address' => Setting::get('business_address', ''),
            'currency_symbol' => Setting::get('currency_symbol', 'Rs.'),
            'invoice_prefix' => Setting::get('invoice_prefix', 'INV'),
            'default_locale' => Setting::get('default_locale', 'en'),
            'session_timeout' => Setting::get('session_timeout', config('session.timeout_minutes', 30)),
            'backup_retention' => Setting::get('backup_retention', 30),
            'low_stock_threshold' => Setting::get('low_stock_threshold', 5),
        ];

        $dbHealthy = true;
        try {
            DB::select('SELECT 1');
        } catch (\Throwable $e) {
            $dbHealthy = false;
        }

        return view('settings.edit', compact('settings', 'dbHealthy'));
    }

    public function update(Request $request, AuditService $audit)
    {
        abort_unless($request->user()->can('settings.manage'), 403);
        $data = $request->validate([
            'business_name' => ['required', 'string', 'max:150'],
            'business_phone' => ['nullable', 'string', 'max:30'],
            'business_address' => ['nullable', 'string', 'max:255'],
            'currency_symbol' => ['required', 'string', 'max:10'],
            'invoice_prefix' => ['required', 'string', 'max:10', 'alpha_num'],
            'default_locale' => ['required', 'in:en,ur'],
            'session_timeout' => ['required', 'integer', 'min:5', 'max:480'],
            'backup_retention' => ['required', 'integer', 'min:3', 'max:365'],
            'low_stock_threshold' => ['required', 'numeric', 'min:0'],
        ]);

        $old = Setting::allSettings();
        foreach ($data as $key => $value) {
            Setting::put($key, (string) $value);
        }

        config(['session.timeout_minutes' => (int) $data['session_timeout']]);
        $audit->log('updated', 'settings', null, 'System settings updated', $old, $data);

        return back()->with('success', __('Saved successfully.'));
    }
}
