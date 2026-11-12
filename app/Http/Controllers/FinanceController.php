<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use App\Support\BusinessSettings;
use App\Support\XlsxWriter;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class FinanceController extends Controller
{
    public function payables(Request $request)
    {
        abort_unless($request->user()->can('payables.view'), 403);
        $suppliers = Supplier::with(['agency', 'salesman'])
            ->whereNull('archived_at')
            ->where('current_balance', '>', 0)
            ->when($request->filled('q'), fn ($q) => $q->where('name', 'like', '%'.$request->q.'%'))
            ->orderByDesc('current_balance')->get();
        $total = (float) $suppliers->sum('current_balance');

        return view('finance.payables', compact('suppliers', 'total'));
    }

    public function receivables(Request $request)
    {
        abort_unless($request->user()->can('receivables.view'), 403);
        $suppliers = Supplier::with(['agency', 'salesman'])
            ->whereNull('archived_at')
            ->where('current_balance', '<', 0)
            ->when($request->filled('q'), fn ($q) => $q->where('name', 'like', '%'.$request->q.'%'))
            ->orderBy('current_balance')->get();
        $total = abs((float) $suppliers->sum('current_balance'));

        return view('finance.receivables', compact('suppliers', 'total'));
    }

    public function export(Request $request, string $type)
    {
        abort_unless(in_array($type, ['payables', 'receivables'], true), 404);
        abort_unless($request->user()->can($type === 'payables' ? 'payables.view' : 'receivables.view'), 403);

        $isPayable = $type === 'payables';
        $suppliers = Supplier::with('agency')
            ->whereNull('archived_at')
            ->where('current_balance', $isPayable ? '>' : '<', 0)
            ->orderBy('current_balance', $isPayable ? 'desc' : 'asc')->get();
        $title = $isPayable ? __('Payables') : __('Receivables');
        $total = abs((float) $suppliers->sum('current_balance'));

        if ($request->query('format') === 'excel') {
            $rows = $suppliers->map(fn ($s) => [
                $s->name, $s->agency?->name, $s->phone,
                $isPayable ? (float) $s->current_balance : abs((float) $s->current_balance),
            ])->all();
            $content = XlsxWriter::build([__('Supplier'), __('Agency'), __('Phone'), __('Balance')], $rows, $title);

            return response($content, 200, [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'Content-Disposition' => 'attachment; filename="'.$type.'.xlsx"',
            ]);
        }

        $settings = BusinessSettings::all();
        $pdf = Pdf::loadView('finance.pdf', compact('suppliers', 'total', 'title', 'isPayable', 'settings'))->setPaper('a4');

        return $pdf->download($type.'.pdf');
    }
}
