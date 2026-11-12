<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Purchase;
use App\Models\Salesman;
use App\Models\Supplier;
use App\Services\PurchaseService;
use App\Support\BusinessSettings;
use App\Support\DateRange;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PurchaseController extends Controller
{
    public function __construct(private PurchaseService $purchases)
    {
    }

    public function index(Request $request)
    {
        abort_unless($request->user()->can('purchases.view'), 403);
        $range = DateRange::resolve($request);

        $purchases = Purchase::with(['supplier', 'salesman'])
            ->when($request->filled('q'), function ($q) use ($request) {
                $q->where(function ($w) use ($request) {
                    $w->where('invoice_no', 'like', '%'.$request->q.'%')
                        ->orWhereHas('supplier', fn ($s) => $s->where('name', 'like', '%'.$request->q.'%'));
                });
            })
            ->when($request->filled('supplier_id'), fn ($q) => $q->where('supplier_id', $request->supplier_id))
            ->when($request->filled('salesman_id'), fn ($q) => $q->where('salesman_id', $request->salesman_id))
            ->when($request->filled('status') && $request->status === 'due', fn ($q) => $q->where('remaining_amount', '>', 0))
            ->when($request->filled('status') && $request->status === 'settled', fn ($q) => $q->where('remaining_amount', '<=', 0))
            ->when($request->filled('status') && $request->status === 'overdue', fn ($q) => $q->where('remaining_amount', '>', 0)->whereDate('due_date', '<', now()))
            ->tap(fn ($q) => DateRange::apply($q, $range, 'purchase_date'))
            ->orderByDesc('purchase_date')->orderByDesc('id')
            ->paginate(20)->withQueryString();

        $totals = [
            'grand' => (float) Purchase::when($range[0], fn ($q) => $q->where('purchase_date', '>=', $range[0]))
                ->when($range[1], fn ($q) => $q->where('purchase_date', '<=', $range[1]))->sum('total'),
            'remaining' => (float) Purchase::where('remaining_amount', '>', 0)->sum('remaining_amount'),
        ];

        return view('purchases.index', [
            'purchases' => $purchases,
            'suppliers' => Supplier::orderBy('name')->get(),
            'salesmen' => Salesman::orderBy('name')->get(),
            'range' => $range,
            'totals' => $totals,
        ]);
    }

    public function create()
    {
        abort_unless(auth()->user()->can('purchases.manage'), 403);

        return view('purchases.form', [
            'purchase' => new Purchase([
                'purchase_date' => now()->toDateString(),
                'discount' => 0,
                'tax' => 0,
                'additional_charges' => 0,
                'paid_amount' => 0,
            ]),
            'suppliers' => Supplier::active()->orderBy('name')->get(),
            'salesmen' => Salesman::orderBy('name')->get(),
            'products' => Product::where('is_active', true)->orderBy('name')->get(),
            'nextInvoice' => $this->purchases->nextInvoiceNo(),
        ]);
    }

    public function store(Request $request)
    {
        abort_unless($request->user()->can('purchases.manage'), 403);
        $data = $this->validated($request);

        $attachment = $request->hasFile('attachment')
            ? $request->file('attachment')->store('attachments', 'local')
            : null;

        $purchase = $this->purchases->create(
            array_merge($data, ['attachment_path' => $attachment]),
            $data['items'],
            $request->user()->id
        );

        return redirect()->route('purchases.show', $purchase)->with('success', __('Saved successfully.'));
    }

    public function show(Purchase $purchase)
    {
        abort_unless(auth()->user()->can('purchases.view'), 403);
        $purchase->load(['items', 'supplier', 'salesman', 'payments', 'returns', 'creator']);
        $settings = BusinessSettings::all();

        return view('purchases.show', compact('purchase', 'settings'));
    }

    public function print(Purchase $purchase)
    {
        abort_unless(auth()->user()->can('purchases.view'), 403);
        $purchase->load(['items', 'supplier', 'salesman']);
        $settings = BusinessSettings::all();

        return view('purchases.print', compact('purchase', 'settings'));
    }

    public function pdf(Purchase $purchase)
    {
        abort_unless(auth()->user()->can('purchases.view'), 403);
        $purchase->load(['items', 'supplier', 'salesman']);
        $settings = BusinessSettings::all();
        $pdf = Pdf::loadView('purchases.pdf', compact('purchase', 'settings'))->setPaper('a4');

        return $pdf->download('invoice-'.$purchase->invoice_no.'.pdf');
    }

    public function edit(Purchase $purchase)
    {
        abort_unless(auth()->user()->can('purchases.manage'), 403);
        $purchase->load('items');

        return view('purchases.form', [
            'purchase' => $purchase,
            'suppliers' => Supplier::orderBy('name')->get(),
            'salesmen' => Salesman::orderBy('name')->get(),
            'products' => Product::where('is_active', true)->orderBy('name')->get(),
            'nextInvoice' => $purchase->invoice_no,
        ]);
    }

    public function update(Request $request, Purchase $purchase)
    {
        abort_unless($request->user()->can('purchases.manage'), 403);
        $data = $this->validated($request, $purchase);

        if ($request->hasFile('attachment')) {
            $data['attachment_path'] = $request->file('attachment')->store('attachments', 'local');
        }

        $this->purchases->update($purchase, $data, $data['items'], $request->user()->id);

        return redirect()->route('purchases.show', $purchase)->with('success', __('Updated successfully.'));
    }

    public function destroy(Purchase $purchase)
    {
        abort_unless(auth()->user()->can('users.manage'), 403);

        $this->purchases->delete($purchase);

        return redirect()->route('purchases.index')->with('success', __('Deleted successfully.'));
    }

    public function downloadAttachment(Purchase $purchase)
    {
        abort_unless(auth()->user()->can('purchases.view'), 403);
        abort_unless($purchase->attachment_path && \Storage::disk('local')->exists($purchase->attachment_path), 404);

        return \Storage::disk('local')->download($purchase->attachment_path);
    }

    private function validated(Request $request, ?Purchase $purchase = null): array
    {
        return $request->validate([
            'supplier_id' => ['required', 'exists:suppliers,id'],
            'salesman_id' => ['nullable', 'exists:salesmen,id'],
            'invoice_no' => [
                'nullable', 'string', 'max:80',
                Rule::unique('purchases', 'invoice_no')
                    ->where(fn ($q) => $q->where('supplier_id', $request->supplier_id)->whereNull('deleted_at'))
                    ->ignore($purchase?->id),
            ],
            'purchase_date' => ['required', 'date'],
            'due_date' => ['nullable', 'date', 'after_or_equal:purchase_date'],
            'discount' => ['nullable', 'numeric', 'min:0'],
            'tax' => ['nullable', 'numeric', 'min:0'],
            'additional_charges' => ['nullable', 'numeric', 'min:0'],
            'initial_paid' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'attachment' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png,webp', 'max:5120'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_name' => ['required', 'string', 'max:150'],
            'items.*.product_id' => ['nullable', 'exists:products,id'],
            'items.*.quantity' => ['required', 'numeric', 'gt:0'],
            'items.*.unit' => ['nullable', 'string', 'max:20'],
            'items.*.rate' => ['required', 'numeric', 'min:0'],
            'items.*.discount' => ['nullable', 'numeric', 'min:0'],
            'items.*.tax' => ['nullable', 'numeric', 'min:0'],
        ], [
            'invoice_no.unique' => __('Invoice number already exists for this supplier.'),
            'items.min' => __('At least one item is required.'),
        ]);
    }
}
