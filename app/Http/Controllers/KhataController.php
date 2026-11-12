<?php

namespace App\Http\Controllers;

use App\Models\Adjustment;
use App\Models\Payment;
use App\Models\Purchase;
use App\Models\PurchaseReturn;
use App\Models\Supplier;
use App\Models\SupplierLedger;
use App\Services\AdjustmentService;
use App\Services\LedgerService;
use App\Services\PaymentService;
use App\Services\PurchaseService;
use App\Services\ReturnService;
use App\Support\DateRange;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class KhataController extends Controller
{
    public function __construct(
        private AdjustmentService $adjustments,
        private ReturnService $returns,
        private PurchaseService $purchases,
        private PaymentService $payments,
        private LedgerService $ledger
    ) {
    }

    public function index(Request $request)
    {
        abort_unless($request->user()->can('ledger.view'), 403);
        $range = DateRange::resolve($request);

        $suppliers = Supplier::with(['agency', 'salesman'])
            ->when($request->filled('q'), function ($q) use ($request) {
                $q->where(function ($w) use ($request) {
                    $w->where('name', 'like', '%'.$request->q.'%')
                        ->orWhere('phone', 'like', '%'.$request->q.'%');
                });
            })
            ->when($request->filled('status'), fn ($q) => $request->status === 'archived'
                ? $q->whereNotNull('archived_at')
                : $q->whereNull('archived_at')->where('is_active', true))
            ->orderByDesc('current_balance')
            ->paginate(15)->withQueryString();

        $periodStats = [
            'purchases' => (float) Purchase::when($range[0], fn ($q) => $q->where('purchase_date', '>=', $range[0]))
                ->when($range[1], fn ($q) => $q->where('purchase_date', '<=', $range[1]))->sum('total'),
            'payments' => (float) Payment::when($range[0], fn ($q) => $q->where('payment_date', '>=', $range[0]))
                ->when($range[1], fn ($q) => $q->where('payment_date', '<=', $range[1]))->sum('amount'),
        ];

        return view('khata.index', compact('suppliers', 'range', 'periodStats'));
    }

    public function show(Request $request, Supplier $supplier)
    {
        abort_unless($request->user()->can('ledger.view'), 403);
        $range = DateRange::resolve($request);
        $entries = DateRange::apply($supplier->ledger()->getQuery(), $range, 'entry_date')
            ->orderBy('entry_date')->orderBy('id')->get();

        $opening = '0.00';
        if ($range[0]) {
            $opening = bcsub(
                (string) $supplier->ledger()->where('entry_date', '<', $range[0])->sum('debit'),
                (string) $supplier->ledger()->where('entry_date', '<', $range[0])->sum('credit'),
                2
            );
        }

        $totals = [
            'debit' => (float) $entries->sum('debit'),
            'credit' => (float) $entries->sum('credit'),
        ];

        $openPurchases = $supplier->purchases()->where('remaining_amount', '>', 0)->orderByDesc('purchase_date')->get();

        return view('khata.show', compact('supplier', 'entries', 'range', 'opening', 'totals', 'openPurchases'));
    }

    public function addAdjustment(Request $request, Supplier $supplier)
    {
        abort_unless($request->user()->can('suppliers.manage'), 403);
        $data = $request->validate([
            'adjustment_date' => ['required', 'date'],
            'direction' => ['required', Rule::in(['increase', 'decrease'])],
            'category' => ['required', Rule::in(['payable', 'receivable'])],
            'amount' => ['required', 'numeric', 'gt:0'],
            'reason' => ['required', 'string', 'max:500'],
            'reference_no' => ['nullable', 'string', 'max:100'],
        ]);

        $this->adjustments->create(['supplier_id' => $supplier->id] + $data, $request->user()->id);

        return back()->with('success', __('Saved successfully.'));
    }

    public function addReturn(Request $request, Supplier $supplier)
    {
        abort_unless($request->user()->can('purchases.manage'), 403);
        $data = $request->validate([
            'return_date' => ['required', 'date'],
            'purchase_id' => ['nullable', Rule::exists('purchases', 'id')->where('supplier_id', $supplier->id)],
            'invoice_no' => ['nullable', 'string', 'max:80'],
            'amount' => ['required', 'numeric', 'gt:0'],
            'reference_no' => ['nullable', 'string', 'max:100'],
            'reason' => ['required', 'string', 'max:500'],
        ]);

        $this->returns->create(['supplier_id' => $supplier->id] + $data, $request->user()->id);

        return back()->with('success', __('Saved successfully.'));
    }

    public function destroyEntry(Request $request, Supplier $supplier, SupplierLedger $entry)
    {
        abort_unless($request->user()->can('suppliers.manage'), 403);
        abort_unless((int) $entry->supplier_id === (int) $supplier->id, 404);

        $this->deleteLedgerEntry($supplier, $entry);

        return back()->with('success', __('Deleted successfully.'));
    }

    public function clear(Request $request, Supplier $supplier)
    {
        abort_unless($request->user()->can('suppliers.delete'), 403);

        $failed = 0;

        foreach ($supplier->ledger()->orderByDesc('id')->get() as $entry) {
            try {
                $this->deleteLedgerEntry($supplier, $entry);
            } catch (ValidationException $e) {
                $failed++;
            }
        }

        return back()->with(
            $failed ? 'warning' : 'success',
            $failed
                ? __('ui.khata_cleared_partial', ['count' => $failed])
                : __('ui.khata_cleared')
        );
    }

    private function deleteLedgerEntry(Supplier $supplier, SupplierLedger $entry): void
    {
        if ($entry->reference_type && $entry->reference_id) {
            $record = match ($entry->reference_type) {
                'adjustment' => Adjustment::find($entry->reference_id),
                'purchase_return' => PurchaseReturn::find($entry->reference_id),
                'purchase' => Purchase::find($entry->reference_id),
                'payment' => Payment::find($entry->reference_id),
                default => null,
            };

            if ($record) {
                match ($entry->reference_type) {
                    'adjustment' => $this->adjustments->delete($record),
                    'purchase_return' => $this->returns->delete($record),
                    'purchase' => $this->purchases->delete($record),
                    'payment' => $this->payments->delete($record),
                };

                return;
            }
        }

        $entry->delete();
        $this->ledger->recalculate($supplier);
    }

    public function adjustments(Request $request)
    {
        abort_unless($request->user()->can('ledger.view'), 403);
        $rows = Adjustment::with('supplier')->orderByDesc('adjustment_date')->paginate(20);

        return view('khata.adjustments', compact('rows'));
    }

    public function returns(Request $request)
    {
        abort_unless($request->user()->can('purchases.view'), 403);
        $rows = PurchaseReturn::with(['supplier', 'purchase'])->orderByDesc('return_date')->paginate(20);

        return view('khata.returns', compact('rows'));
    }
}
