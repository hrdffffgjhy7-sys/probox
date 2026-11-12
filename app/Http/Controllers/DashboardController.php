<?php

namespace App\Http\Controllers;

use App\Models\Agency;
use App\Models\AuditLog;
use App\Models\Expense;
use App\Models\Payment;
use App\Models\Purchase;
use App\Models\Salesman;
use App\Models\Supplier;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        abort_unless(auth()->user()->can('dashboard.view'), 403);

        $payable = (float) Supplier::where('current_balance', '>', 0)->sum('current_balance');
        $receivable = abs((float) Supplier::where('current_balance', '<', 0)->sum('current_balance'));

        $cards = [
            'suppliers' => Supplier::count(),
            'salesmen' => Salesman::count(),
            'agencies' => Agency::count(),
            'purchases' => (float) Purchase::sum('total'),
            'payments' => (float) Payment::sum('amount'),
            'payable' => $payable,
            'receivable' => $receivable,
            'net' => $payable - $receivable,
            'today_count' => Purchase::whereDate('purchase_date', today())->count() + Payment::whereDate('payment_date', today())->count(),
            'month_purchases' => (float) Purchase::whereBetween('purchase_date', [now()->startOfMonth(), now()->endOfMonth()])->sum('total'),
            'month_payments' => (float) Payment::whereBetween('payment_date', [now()->startOfMonth(), now()->endOfMonth()])->sum('amount'),
            'expenses_month' => (float) Expense::whereBetween('expense_date', [now()->startOfMonth(), now()->endOfMonth()])->sum('amount'),
            'pending' => (float) Purchase::where('remaining_amount', '>', 0)->sum('remaining_amount'),
            'overdue' => (float) Purchase::where('remaining_amount', '>', 0)->whereDate('due_date', '<', today())->sum('remaining_amount'),
        ];

        $months = collect(range(5, 0))->map(fn ($i) => now()->subMonths($i));
        $chart = [
            'labels' => $months->map(fn ($m) => $m->format('M Y'))->all(),
            'purchases' => $months->map(fn ($m) => (float) Purchase::whereBetween('purchase_date', [$m->copy()->startOfMonth(), $m->copy()->endOfMonth()])->sum('total'))->all(),
            'payments' => $months->map(fn ($m) => (float) Payment::whereBetween('payment_date', [$m->copy()->startOfMonth(), $m->copy()->endOfMonth()])->sum('amount'))->all(),
        ];

        $topSuppliers = Supplier::where('current_balance', '>', 0)->orderByDesc('current_balance')->limit(6)->get();
        $recentPurchases = Purchase::with('supplier')->orderByDesc('purchase_date')->orderByDesc('id')->limit(6)->get();
        $recentPayments = Payment::with('supplier')->orderByDesc('payment_date')->orderByDesc('id')->limit(6)->get();
        $recentActivities = AuditLog::with('user')->orderByDesc('id')->limit(8)->get();
        $overduePurchases = Purchase::with('supplier')->where('remaining_amount', '>', 0)->whereDate('due_date', '<', today())->orderBy('due_date')->limit(6)->get();
        $notifications = \App\Models\Notification::where(fn ($q) => $q->whereNull('user_id')->orWhere('user_id', auth()->id()))
            ->orderByDesc('id')->limit(5)->get();

        $dbHealthy = true;
        try {
            DB::select('SELECT 1');
        } catch (\Throwable $e) {
            $dbHealthy = false;
        }

        return view('dashboard', compact(
            'cards', 'chart', 'topSuppliers', 'recentPurchases', 'recentPayments',
            'recentActivities', 'overduePurchases', 'notifications', 'dbHealthy'
        ));
    }
}
