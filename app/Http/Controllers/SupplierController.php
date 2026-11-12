<?php

namespace App\Http\Controllers;

use App\Models\Agency;
use App\Models\LedgerEntry;
use App\Models\Purchase;
use App\Models\Salesman;
use App\Models\Supplier;
use App\Services\AuditService;
use App\Services\LedgerService;
use App\Services\SupplierService;
use App\Services\WhatsAppService;
use App\Support\BusinessSettings;
use App\Support\DateRange;
use App\Support\XlsxWriter;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class SupplierController extends Controller
{
    public function __construct(
        private SupplierService $suppliers,
        private LedgerService $ledger,
        private WhatsAppService $whatsapp,
        private AuditService $audit
    ) {
    }

    public function index(Request $request)
    {
        $this->authorizePermission('suppliers.view');

        $query = Supplier::with(['agency', 'salesman']);

        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function ($w) use ($q) {
                $w->where('name', 'like', "%$q%")
                    ->orWhere('phone', 'like', "%$q%")
                    ->orWhere('whatsapp', 'like', "%$q%")
                    ->orWhereHas('agency', fn ($a) => $a->where('name', 'like', "%$q%"));
            });
        }

        if ($request->filled('agency_id')) {
            $query->where('agency_id', $request->agency_id);
        }
        if ($request->filled('salesman_id')) {
            $query->where('salesman_id', $request->salesman_id);
        }
        if ($request->filled('status')) {
            $request->status === 'archived'
                ? $query->whereNotNull('archived_at')
                : $query->whereNull('archived_at')->where('is_active', true);
        }

        $suppliers = $query->orderBy('name')->paginate(15)->withQueryString();
        $agencies = Agency::orderBy('name')->get();
        $salesmen = Salesman::orderBy('name')->get();

        $totals = [
            'payable' => (float) Supplier::where('current_balance', '>', 0)->sum('current_balance'),
            'receivable' => abs((float) Supplier::where('current_balance', '<', 0)->sum('current_balance')),
            'active' => Supplier::whereNull('archived_at')->where('is_active', true)->count(),
            'archived' => Supplier::whereNotNull('archived_at')->count(),
        ];

        return view('suppliers.index', compact('suppliers', 'agencies', 'salesmen', 'totals'));
    }

    public function create()
    {
        $this->authorizePermission('suppliers.manage');

        return view('suppliers.form', [
            'supplier' => new Supplier(['opening_balance' => 0, 'opening_balance_type' => 'payable', 'is_active' => true]),
            'agencies' => Agency::orderBy('name')->get(),
            'salesmen' => Salesman::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $this->authorizePermission('suppliers.manage');
        $data = $this->validated($request);

        $supplier = $this->suppliers->create($data, $request->user()->id);

        return redirect()->route('suppliers.index')->with('success', __('Saved successfully.').' '.__('Supplier ledger opened'));
    }

    public function show(Supplier $supplier)
    {
        $this->authorizePermission('suppliers.view');
        $supplier->load(['agency', 'salesman']);
        $recent = $supplier->ledger()->orderByDesc('entry_date')->orderByDesc('id')->limit(10)->get();
        $purchases = $supplier->purchases()->orderByDesc('purchase_date')->limit(5)->get();
        $payments = $supplier->payments()->orderByDesc('payment_date')->limit(5)->get();
        $totals = [
            'purchases' => (float) $supplier->purchases()->sum('total'),
            'payments' => (float) $supplier->payments()->sum('amount'),
            'returns' => (float) $supplier->returns()->sum('amount'),
            'debit' => (float) $supplier->ledger()->sum('debit'),
            'credit' => (float) $supplier->ledger()->sum('credit'),
        ];

        return view('suppliers.show', compact('supplier', 'recent', 'purchases', 'payments', 'totals'));
    }

    public function edit(Supplier $supplier)
    {
        $this->authorizePermission('suppliers.manage');

        return view('suppliers.form', [
            'supplier' => $supplier,
            'agencies' => Agency::orderBy('name')->get(),
            'salesmen' => Salesman::orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Supplier $supplier)
    {
        $this->authorizePermission('suppliers.manage');
        $data = $this->validated($request, $supplier);

        $this->suppliers->update($supplier, $data);

        return redirect()->route('suppliers.show', $supplier)->with('success', __('Updated successfully.'));
    }

    public function archive(Supplier $supplier)
    {
        $this->authorizePermission('suppliers.delete');
        $this->suppliers->archive($supplier);

        return back()->with('success', __('Archived successfully.'));
    }

    public function restore(Supplier $supplier)
    {
        $this->authorizePermission('suppliers.delete');
        $this->suppliers->restore($supplier);

        return back()->with('success', __('Restored successfully.'));
    }

    public function destroy(Request $request, Supplier $supplier)
    {
        $this->authorizePermission('suppliers.delete');

        $hasHistory = LedgerEntry::where('supplier_id', $supplier->id)->exists()
            || Purchase::where('supplier_id', $supplier->id)->exists()
            || $supplier->payments()->exists()
            || $supplier->returns()->exists()
            || $supplier->adjustments()->exists();

        if ($hasHistory) {
            return back()->withErrors(['delete' => __('This supplier has financial history and cannot be permanently deleted.')]);
        }

        $request->validate(['confirm' => ['accepted']]);

        DB::transaction(function () use ($supplier) {
            $this->audit->log('force_deleted', 'suppliers', $supplier, 'Supplier permanently deleted', $supplier->toArray(), []);
            $supplier->forceDelete();
        });

        return redirect()->route('suppliers.index')->with('success', __('Deleted successfully.'));
    }

    public function statement(Request $request, Supplier $supplier)
    {
        $this->authorizePermission('ledger.view');
        $range = DateRange::resolve($request);
        $entries = $this->statementEntries($supplier, $range);
        $opening = $this->openingBefore($supplier, $range[0]);
        $closing = $entries->last()?->running_balance ?? $opening;

        if ($request->boolean('print')) {
            return view('suppliers.statement-print', compact('supplier', 'entries', 'opening', 'closing', 'range'));
        }

        return view('suppliers.statement', compact('supplier', 'entries', 'opening', 'closing', 'range'));
    }

    public function statementPdf(Request $request, Supplier $supplier)
    {
        $this->authorizePermission('ledger.view');
        $range = DateRange::resolve($request);
        $entries = $this->statementEntries($supplier, $range);
        $opening = $this->openingBefore($supplier, $range[0]);
        $closing = $entries->last()?->running_balance ?? $opening;
        $settings = BusinessSettings::all();

        $pdf = Pdf::loadView('suppliers.statement-pdf', compact('supplier', 'entries', 'opening', 'closing', 'range', 'settings'))
            ->setPaper('a4');

        return $pdf->download('statement-'.$supplier->id.'-'.now()->format('Ymd').'.pdf');
    }

    public function statementExcel(Request $request, Supplier $supplier)
    {
        $this->authorizePermission('ledger.view');
        $range = DateRange::resolve($request);
        $entries = $this->statementEntries($supplier, $range);
        $opening = $this->openingBefore($supplier, $range[0]);

        $rows = [];
        $rows[] = [__('Opening Balance'), '', __('Debit'), __('Credit'), __('Balance')];
        $rows[] = [optional($range[0])->format('Y-m-d') ?? '', __('Opening Balance'), '', '', $opening];
        foreach ($entries as $entry) {
            $rows[] = [
                $entry->entry_date->format('Y-m-d'),
                $entry->description ?: __($entry->type),
                $entry->debit > 0 ? $entry->debit : '',
                $entry->credit > 0 ? $entry->credit : '',
                $entry->running_balance,
            ];
        }
        $rows[] = ['', __('Closing Balance'), '', '', $entries->last()?->running_balance ?? $opening];

        $headers = [__('Date'), __('Description'), __('Debit'), __('Credit'), __('Running Balance')];
        $content = XlsxWriter::build($headers, $rows, __('Supplier Statement'));

        return response($content, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="statement-'.$supplier->id.'.xlsx"',
        ]);
    }

    public function whatsapp(Request $request, Supplier $supplier)
    {
        $this->authorizePermission('suppliers.view');
        $template = $request->input('template', 'statement');
        $map = [
            'payment' => 'payment_confirmation',
            'purchase' => 'purchase_summary',
        ];
        $type = $map[$template] ?? $template;
        $message = $this->whatsapp->message($type, $supplier, $locale = app()->getLocale(), null, null, $request->input('custom', ''));
        $phone = $this->whatsapp->normalizePhone($supplier->whatsapp ?: $supplier->phone);
        $link = $phone ? $this->whatsapp->url($phone, $message) : null;

        return view('suppliers.whatsapp', compact('supplier', 'template', 'message', 'link', 'phone'));
    }

    private function statementEntries(Supplier $supplier, array $range)
    {
        return DateRange::apply($supplier->ledger()->getQuery(), $range, 'entry_date')
            ->orderBy('entry_date')->orderBy('id')->get();
    }

    private function openingBefore(Supplier $supplier, $start): string
    {
        if (! $start) {
            return '0.00';
        }

        $debit = $supplier->ledger()->where('entry_date', '<', $start)->sum('debit');
        $credit = $supplier->ledger()->where('entry_date', '<', $start)->sum('credit');

        return bcsub((string) $debit, (string) $credit, 2);
    }

    private function validated(Request $request, ?Supplier $supplier = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'agency_id' => ['nullable', 'exists:agencies,id'],
            'salesman_id' => ['nullable', 'exists:salesmen,id'],
            'phone' => ['nullable', 'string', 'max:30'],
            'whatsapp' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string', 'max:255'],
            'opening_balance' => ['nullable', 'numeric', 'min:0'],
            'opening_balance_type' => ['nullable', Rule::in(['payable', 'receivable'])],
            'notes' => ['nullable', 'string', 'max:2000'],
            'is_active' => ['nullable', 'boolean'],
        ]) + ['is_active' => $request->boolean('is_active', true)];
    }

    private function authorizePermission(string $permission): void
    {
        abort_unless(auth()->user()->can($permission), 403, __('Access denied'));
    }
}
