<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Purchase;
use App\Models\Salesman;
use App\Models\Supplier;
use App\Services\PaymentService;
use App\Support\BusinessSettings;
use App\Support\DateRange;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PaymentController extends Controller
{
    public function __construct(private PaymentService $payments)
    {
    }

    public function index(Request $request)
    {
        abort_unless($request->user()->can('payments.view'), 403);
        $range = DateRange::resolve($request);

        $payments = Payment::with(['supplier', 'salesman', 'purchase'])
            ->when($request->filled('q'), function ($q) use ($request) {
                $q->where(function ($w) use ($request) {
                    $w->where('reference_no', 'like', '%'.$request->q.'%')
                        ->orWhereHas('supplier', fn ($s) => $s->where('name', 'like', '%'.$request->q.'%'));
                });
            })
            ->when($request->filled('supplier_id'), fn ($q) => $q->where('supplier_id', $request->supplier_id))
            ->when($request->filled('method'), fn ($q) => $q->where('method', $request->method))
            ->tap(fn ($q) => DateRange::apply($q, $range, 'payment_date'))
            ->orderByDesc('payment_date')->orderByDesc('id')
            ->paginate(20)->withQueryString();

        $total = (float) Payment::when($range[0], fn ($q) => $q->where('payment_date', '>=', $range[0]))
            ->when($range[1], fn ($q) => $q->where('payment_date', '<=', $range[1]))
            ->sum('amount');

        return view('payments.index', [
            'payments' => $payments,
            'suppliers' => Supplier::orderBy('name')->get(),
            'range' => $range,
            'total' => $total,
        ]);
    }

    public function create(Request $request)
    {
        abort_unless($request->user()->can('payments.manage'), 403);
        $supplier = $request->filled('supplier_id') ? Supplier::find($request->supplier_id) : null;

        return view('payments.form', [
            'payment' => new Payment([
                'supplier_id' => $supplier?->id,
                'payment_date' => now()->toDateString(),
                'method' => 'cash',
            ]),
            'suppliers' => Supplier::where('is_active', true)->orWhereHas('purchases')->orderBy('name')->get(),
            'salesmen' => Salesman::orderBy('name')->get(),
            'purchases' => $supplier ? $supplier->purchases()->where('remaining_amount', '>', 0)->orderByDesc('purchase_date')->get() : collect(),
        ]);
    }

    public function store(Request $request)
    {
        abort_unless($request->user()->can('payments.manage'), 403);
        $data = $this->validated($request);

        if ($request->hasFile('receipt')) {
            $data['receipt_path'] = $request->file('receipt')->store('receipts', 'local');
        }

        $this->payments->create($data, $request->user()->id);

        return redirect()->route('payments.index')->with('success', __('Payment recorded successfully.'));
    }

    public function show(Payment $payment)
    {
        abort_unless(auth()->user()->can('payments.view'), 403);
        $payment->load(['supplier', 'salesman', 'purchase', 'creator']);

        return view('payments.show', compact('payment'));
    }

    public function receipt(Payment $payment)
    {
        abort_unless(auth()->user()->can('payments.view'), 403);
        $payment->load(['supplier', 'salesman', 'purchase']);
        $settings = BusinessSettings::all();
        $pdf = Pdf::loadView('payments.receipt-pdf', compact('payment', 'settings'))->setPaper('a4');

        return $pdf->download('payment-receipt-'.$payment->id.'.pdf');
    }

    public function edit(Payment $payment)
    {
        abort_unless(auth()->user()->can('payments.manage'), 403);

        return view('payments.form', [
            'payment' => $payment,
            'suppliers' => Supplier::orderBy('name')->get(),
            'salesmen' => Salesman::orderBy('name')->get(),
            'purchases' => $payment->supplier->purchases()->orderByDesc('purchase_date')->get(),
        ]);
    }

    public function update(Request $request, Payment $payment)
    {
        abort_unless($request->user()->can('payments.manage'), 403);
        $data = $this->validated($request, $payment);

        if ($request->hasFile('receipt')) {
            $data['receipt_path'] = $request->file('receipt')->store('receipts', 'local');
        }

        $this->payments->update($payment, $data, $request->user()->id);

        return redirect()->route('payments.index')->with('success', __('Updated successfully.'));
    }

    public function destroy(Payment $payment)
    {
        abort_unless(auth()->user()->can('users.manage'), 403);

        $this->payments->delete($payment);

        return redirect()->route('payments.index')->with('success', __('Deleted successfully.'));
    }

    public function downloadReceipt(Payment $payment)
    {
        abort_unless(auth()->user()->can('payments.view'), 403);
        abort_unless($payment->receipt_path && \Storage::disk('local')->exists($payment->receipt_path), 404);

        return \Storage::disk('local')->download($payment->receipt_path);
    }

    private function validated(Request $request, ?Payment $payment = null): array
    {
        return $request->validate([
            'supplier_id' => ['required', 'exists:suppliers,id'],
            'salesman_id' => ['nullable', 'exists:salesmen,id'],
            'purchase_id' => ['nullable', 'exists:purchases,id'],
            'payment_date' => ['required', 'date'],
            'amount' => ['required', 'numeric', 'gt:0'],
            'method' => ['required', Rule::in(['cash', 'bank', 'cheque', 'online', 'other'])],
            'reference_no' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'receipt' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png,webp', 'max:5120'],
        ]);
    }
}
