<?php

namespace App\Http\Controllers;

use App\Models\Agency;
use App\Models\Salesman;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SalesmanController extends Controller
{
    public function __construct(private AuditService $audit)
    {
    }

    public function index(Request $request)
    {
        abort_unless($request->user()->can('salesmen.manage'), 403, __('Access denied'));

        $salesmen = Salesman::with('agency')->withCount('suppliers')
            ->when($request->filled('q'), function ($q) use ($request) {
                $q->where(function ($w) use ($request) {
                    $w->where('name', 'like', '%'.$request->q.'%')
                        ->orWhere('phone', 'like', '%'.$request->q.'%');
                });
            })
            ->when($request->filled('agency_id'), fn ($q) => $q->where('agency_id', $request->agency_id))
            ->when($request->filled('status'), fn ($q) => $request->status === 'archived'
                ? $q->where('is_active', false)
                : $q->where('is_active', true))
            ->orderBy('name')->paginate(15)->withQueryString();

        $agencies = Agency::orderBy('name')->get();

        return view('salesmen.index', compact('salesmen', 'agencies'));
    }

    public function create()
    {
        abort_unless(auth()->user()->can('salesmen.manage'), 403);

        return view('salesmen.form', [
            'salesman' => new Salesman(['is_active' => true]),
            'agencies' => Agency::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        abort_unless($request->user()->can('salesmen.manage'), 403);
        $data = $this->validated($request);
        $data['created_by'] = $request->user()->id;
        $salesman = Salesman::create($data);
        $this->audit->log('created', 'salesmen', $salesman, 'Salesman created', [], $salesman->toArray());

        return redirect()->route('salesmen.index')->with('success', __('Saved successfully.'));
    }

    public function show(Salesman $salesman)
    {
        abort_unless(auth()->user()->can('salesmen.manage'), 403);
        $salesman->load(['agency', 'suppliers']);
        $purchases = $salesman->purchases()->with('supplier')->orderByDesc('purchase_date')->limit(10)->get();
        $payments = $salesman->payments()->with('supplier')->orderByDesc('payment_date')->limit(10)->get();
        $totals = [
            'purchases' => (float) $salesman->purchases()->sum('total'),
            'payments' => (float) $salesman->payments()->sum('amount'),
            'outstanding' => (float) $salesman->suppliers()->sum('current_balance'),
        ];

        return view('salesmen.show', compact('salesman', 'purchases', 'payments', 'totals'));
    }

    public function edit(Salesman $salesman)
    {
        abort_unless(auth()->user()->can('salesmen.manage'), 403);

        return view('salesmen.form', [
            'salesman' => $salesman,
            'agencies' => Agency::orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Salesman $salesman)
    {
        abort_unless($request->user()->can('salesmen.manage'), 403);
        $old = $salesman->toArray();
        $salesman->update($this->validated($request));
        $this->audit->log('updated', 'salesmen', $salesman, 'Salesman updated', $old, $salesman->fresh()->toArray());

        return redirect()->route('salesmen.index')->with('success', __('Updated successfully.'));
    }

    public function archive(Salesman $salesman)
    {
        abort_unless(auth()->user()->can('salesmen.manage'), 403);
        $salesman->update(['is_active' => false]);
        $this->audit->log('archived', 'salesmen', $salesman, 'Salesman archived');

        return back()->with('success', __('Archived successfully.'));
    }

    public function restore(Salesman $salesman)
    {
        abort_unless(auth()->user()->can('salesmen.manage'), 403);
        $salesman->restore();
        $salesman->update(['is_active' => true]);
        $this->audit->log('restored', 'salesmen', $salesman, 'Salesman restored');

        return back()->with('success', __('Restored successfully.'));
    }

    public function destroy(Salesman $salesman)
    {
        abort_unless(auth()->user()->isAdmin(), 403);
        if ($salesman->suppliers()->exists() || $salesman->purchases()->exists() || $salesman->payments()->exists()) {
            return back()->withErrors(['delete' => __('This salesman has linked records.')]);
        }
        DB::transaction(function () use ($salesman) {
            $this->audit->log('deleted', 'salesmen', $salesman, 'Salesman deleted', $salesman->toArray(), []);
            $salesman->forceDelete();
        });

        return redirect()->route('salesmen.index')->with('success', __('Deleted successfully.'));
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'agency_id' => ['nullable', 'exists:agencies,id'],
            'phone' => ['nullable', 'string', 'max:30'],
            'whatsapp' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'is_active' => ['nullable', 'boolean'],
        ]) + ['is_active' => $request->boolean('is_active', true)];
    }
}
