<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\LedgerEntry;
use App\Models\Payment;
use App\Models\Purchase;
use App\Models\PurchaseReturn;
use App\Models\Salesman;
use App\Models\Supplier;
use App\Support\DateRange;
use App\Support\XlsxWriter;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    private array $types = [
        'daily' => 'Daily Purchases',
        'monthly' => 'Monthly Purchases Report',
        'supplier_purchases' => 'Supplier-wise Purchases',
        'supplier_payments' => 'Supplier-wise Payments',
        'payables' => 'Payables',
        'receivables' => 'Receivables',
        'salesman' => 'Salesman-wise Transactions',
        'returns' => 'Purchase Returns',
        'payments' => 'Payment History',
        'profit' => 'Profit / Loss',
        'ledger' => 'Complete Business Ledger',
    ];

    public function index(Request $request)
    {
        abort_unless(auth()->user()->can('reports.view'), 403);
        $range = DateRange::resolve($request);
        $type = array_key_exists($request->input('type', 'daily'), $this->types) ? $request->input('type', 'daily') : 'daily';
        $report = $this->build($type, $request, $range);

        return view('reports.index', [
            'types' => $this->types,
            'type' => $type,
            'range' => $range,
            'report' => $report,
            'suppliers' => Supplier::orderBy('name')->get(),
            'salesmen' => Salesman::orderBy('name')->get(),
        ]);
    }

    public function print(Request $request)
    {
        abort_unless(auth()->user()->can('reports.view'), 403);
        $range = DateRange::resolve($request);
        $type = array_key_exists($request->input('type', 'daily'), $this->types) ? $request->input('type', 'daily') : 'daily';
        $report = $this->build($type, $request, $range);
        $settings = $this->settings();

        return view('reports.print', compact('report', 'range', 'settings'));
    }

    public function pdf(Request $request)
    {
        abort_unless(auth()->user()->can('reports.view'), 403);
        $range = DateRange::resolve($request);
        $type = array_key_exists($request->input('type', 'daily'), $this->types) ? $request->input('type', 'daily') : 'daily';
        $report = $this->build($type, $request, $range);
        $settings = $this->settings();
        $pdf = Pdf::loadView('reports.pdf', compact('report', 'range', 'settings'))->setPaper('a4', 'landscape');

        return $pdf->download('report-'.$type.'-'.now()->format('Ymd').'.pdf');
    }

    public function excel(Request $request)
    {
        abort_unless(auth()->user()->can('reports.view'), 403);
        $range = DateRange::resolve($request);
        $type = array_key_exists($request->input('type', 'daily'), $this->types) ? $request->input('type', 'daily') : 'daily';
        $report = $this->build($type, $request, $range);

        $content = XlsxWriter::build($report['headers'], $report['rows'], $report['title']);

        return response($content, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="report-'.$type.'.xlsx"',
        ]);
    }

    private function build(string $type, Request $request, array $range): array
    {
        $supplierId = $request->input('supplier_id');
        $salesmanId = $request->input('salesman_id');
        $title = __($this->types[$type]);
        $headers = [];
        $rows = [];
        $note = null;

        switch ($type) {
            case 'daily':
                $headers = [__('Date'), __('Purchases'), __('Payments')];
                $data = Purchase::selectRaw('purchase_date as d, SUM(total) as total')
                    ->when($range[0], fn ($q) => $q->where('purchase_date', '>=', $range[0]))
                    ->when($range[1], fn ($q) => $q->where('purchase_date', '<=', $range[1]))
                    ->groupBy('purchase_date')->orderBy('purchase_date')->get();
                $payments = Payment::selectRaw('payment_date as d, SUM(amount) as total')
                    ->when($range[0], fn ($q) => $q->where('payment_date', '>=', $range[0]))
                    ->when($range[1], fn ($q) => $q->where('payment_date', '<=', $range[1]))
                    ->groupBy('payment_date')->pluck('total', 'd');
                foreach ($data as $row) {
                    $rows[] = [$row->d, number_format((float) $row->total, 2, '.', ''), number_format((float) ($payments[$row->d] ?? 0), 2, '.', '')];
                }
                break;

            case 'monthly':
                $headers = [__('Month'), __('Purchases'), __('Payments'), __('Expenses')];
                $start = $range[0] ?: now()->startOfYear();
                $end = $range[1] ?: now()->endOfMonth();
                $cursor = $start->copy()->startOfMonth();
                while ($cursor <= $end) {
                    $rows[] = [
                        $cursor->format('M Y'),
                        number_format((float) Purchase::whereBetween('purchase_date', [$cursor->copy()->startOfMonth(), $cursor->copy()->endOfMonth()])->sum('total'), 2, '.', ''),
                        number_format((float) Payment::whereBetween('payment_date', [$cursor->copy()->startOfMonth(), $cursor->copy()->endOfMonth()])->sum('amount'), 2, '.', ''),
                        number_format((float) Expense::whereBetween('expense_date', [$cursor->copy()->startOfMonth(), $cursor->copy()->endOfMonth()])->sum('amount'), 2, '.', ''),
                    ];
                    $cursor->addMonth();
                }
                break;

            case 'supplier_purchases':
                $headers = [__('Supplier'), __('Invoices'), __('Purchases'), __('Paid'), __('Remaining')];
                $q = Purchase::selectRaw('supplier_id, COUNT(*) as invoices, SUM(total) as total, SUM(paid_amount) as paid, SUM(remaining_amount) as remaining')
                    ->when($range[0], fn ($x) => $x->where('purchase_date', '>=', $range[0]))
                    ->when($range[1], fn ($x) => $x->where('purchase_date', '<=', $range[1]))
                    ->when($supplierId, fn ($x) => $x->where('supplier_id', $supplierId))
                    ->groupBy('supplier_id')->get();
                foreach ($q as $row) {
                    $rows[] = [Supplier::find($row->supplier_id)?->name, $row->invoices, number_format((float) $row->total, 2, '.', ''), number_format((float) $row->paid, 2, '.', ''), number_format((float) $row->remaining, 2, '.', '')];
                }
                break;

            case 'supplier_payments':
            case 'payments':
                $headers = [__('Date'), __('Supplier'), __('Method'), __('Reference'), __('Amount')];
                $q = Payment::with('supplier')
                    ->when($range[0], fn ($x) => $x->where('payment_date', '>=', $range[0]))
                    ->when($range[1], fn ($x) => $x->where('payment_date', '<=', $range[1]))
                    ->when($supplierId, fn ($x) => $x->where('supplier_id', $supplierId))
                    ->orderByDesc('payment_date');
                foreach ($q->get() as $p) {
                    $rows[] = [$p->payment_date->format('Y-m-d'), $p->supplier?->name, __($p->method), $p->reference_no, number_format((float) $p->amount, 2, '.', '')];
                }
                break;

            case 'payables':
            case 'receivables':
                $isPayable = $type === 'payables';
                $headers = [__('Supplier'), __('Agency'), __('Phone'), __('Balance')];
                foreach (Supplier::with('agency')->where('current_balance', $isPayable ? '>' : '<', 0)->orderBy('current_balance', $isPayable ? 'desc' : 'asc')->get() as $s) {
                    $rows[] = [$s->name, $s->agency?->name, $s->phone, number_format((float) ($isPayable ? $s->current_balance : abs($s->current_balance)), 2, '.', '')];
                }
                break;

            case 'salesman':
                $headers = [__('Salesman'), __('Agency'), __('Purchases'), __('Payments'), __('Outstanding')];
                foreach (Salesman::with('agency')->when($salesmanId, fn ($x) => $x->whereKey($salesmanId))->get() as $s) {
                    $rows[] = [
                        $s->name, $s->agency?->name,
                        number_format((float) $s->purchases()->when($range[0], fn ($x) => $x->where('purchase_date', '>=', $range[0]))->when($range[1], fn ($x) => $x->where('purchase_date', '<=', $range[1]))->sum('total'), 2, '.', ''),
                        number_format((float) $s->payments()->when($range[0], fn ($x) => $x->where('payment_date', '>=', $range[0]))->when($range[1], fn ($x) => $x->where('payment_date', '<=', $range[1]))->sum('amount'), 2, '.', ''),
                        number_format((float) $s->suppliers()->sum('current_balance'), 2, '.', ''),
                    ];
                }
                break;

            case 'returns':
                $headers = [__('Date'), __('Supplier'), __('Invoice'), __('Reason'), __('Amount')];
                foreach (PurchaseReturn::with(['supplier', 'purchase'])->when($range[0], fn ($x) => $x->where('return_date', '>=', $range[0]))->when($range[1], fn ($x) => $x->where('return_date', '<=', $range[1]))->when($supplierId, fn ($x) => $x->where('supplier_id', $supplierId))->orderByDesc('return_date')->get() as $r) {
                    $rows[] = [$r->return_date->format('Y-m-d'), $r->supplier?->name, $r->purchase?->invoice_no, $r->reason, number_format((float) $r->amount, 2, '.', '')];
                }
                break;

            case 'profit':
                $note = __('This app does not include a sales register, so a true profit/loss cannot be calculated. It reports purchase, expense and outstanding position instead.');
                $headers = [__('Metric'), __('Amount')];
                $purchases = (float) Purchase::when($range[0], fn ($x) => $x->where('purchase_date', '>=', $range[0]))->when($range[1], fn ($x) => $x->where('purchase_date', '<=', $range[1]))->sum('total');
                $expenses = (float) Expense::when($range[0], fn ($x) => $x->where('expense_date', '>=', $range[0]))->when($range[1], fn ($x) => $x->where('expense_date', '<=', $range[1]))->sum('amount');
                $paid = (float) Payment::when($range[0], fn ($x) => $x->where('payment_date', '>=', $range[0]))->when($range[1], fn ($x) => $x->where('payment_date', '<=', $range[1]))->sum('amount');
                $rows = [
                    [__('Purchase totals'), number_format($purchases, 2, '.', '')],
                    [__('Expense totals'), number_format($expenses, 2, '.', '')],
                    [__('Payment totals'), number_format($paid, 2, '.', '')],
                    [__('Gross position'), number_format($purchases + $expenses, 2, '.', '')],
                ];
                break;

            default:
                $headers = [__('Date'), __('Supplier'), __('Type'), __('Description'), __('Debit'), __('Credit'), __('Balance')];
                $q = LedgerEntry::with('supplier')
                    ->when($range[0], fn ($x) => $x->where('entry_date', '>=', $range[0]))
                    ->when($range[1], fn ($x) => $x->where('entry_date', '<=', $range[1]))
                    ->when($supplierId, fn ($x) => $x->where('supplier_id', $supplierId))
                    ->orderBy('entry_date')->orderBy('id');
                foreach ($q->get() as $e) {
                    $rows[] = [$e->entry_date->format('Y-m-d'), $e->supplier?->name, __($e->type), $e->description, number_format((float) $e->debit, 2, '.', ''), number_format((float) $e->credit, 2, '.', ''), number_format((float) $e->running_balance, 2, '.', '')];
                }
                break;
        }

        $totals = [];
        foreach ($headers as $i => $h) {
            $sum = 0;
            $numeric = false;
            foreach ($rows as $row) {
                if (isset($row[$i]) && is_numeric(str_replace(',', '', (string) $row[$i]))) {
                    $sum += (float) str_replace(',', '', (string) $row[$i]);
                    $numeric = true;
                }
            }
            if ($numeric && $i > 0) {
                $totals[$i] = number_format($sum, 2, '.', '');
            }
        }

        return ['title' => $title, 'headers' => $headers, 'rows' => $rows, 'totals' => $totals, 'note' => $note, 'type' => $type];
    }

    private function settings(): array
    {
        return [
            'name' => \App\Models\Setting::get('business_name', config('app.name')),
            'phone' => \App\Models\Setting::get('business_phone', ''),
            'address' => \App\Models\Setting::get('business_address', ''),
            'currency' => \App\Models\Setting::get('currency_symbol', 'Rs.'),
        ];
    }
}
