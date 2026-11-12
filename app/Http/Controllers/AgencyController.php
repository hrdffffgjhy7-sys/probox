<?php

namespace App\Http\Controllers;

use App\Models\Agency;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AgencyController extends Controller
{
    public function __construct(private AuditService $audit)
    {
    }

    public function index(Request $request)
    {
        abort_unless($request->user()->can('agencies.manage'), 403, __('Access denied'));

        $agencies = Agency::withCount(['salesmen', 'suppliers'])
            ->when($request->filled('q'), function ($q) use ($request) {
                $q->where(function ($w) use ($request) {
                    $w->where('name', 'like', '%'.$request->q.'%')
                        ->orWhere('phone', 'like', '%'.$request->q.'%');
                });
            })
            ->when($request->filled('status'), fn ($q) => $request->status === 'archived'
                ? $q->where('is_active', false)
                : $q->where('is_active', true))
            ->orderBy('name')
            ->paginate(15)->withQueryString();

        return view('agencies.index', compact('agencies'));
    }

    public function create()
    {
        abort_unless(auth()->user()->can('agencies.manage'), 403);

        return view('agencies.form', ['agency' => new Agency(['is_active' => true])]);
    }

    public function store(Request $request)
    {
        abort_unless($request->user()->can('agencies.manage'), 403);
        $data = $this->validated($request);
        $data['created_by'] = $request->user()->id;
        $agency = Agency::create($data);
        $this->audit->log('created', 'agencies', $agency, 'Agency created', [], $agency->toArray());

        return redirect()->route('agencies.index')->with('success', __('Saved successfully.'));
    }

    public function show(Agency $agency)
    {
        abort_unless(auth()->user()->can('agencies.manage'), 403);
        $agency->load(['salesmen', 'suppliers']);

        return view('agencies.show', compact('agency'));
    }

    public function edit(Agency $agency)
    {
        abort_unless(auth()->user()->can('agencies.manage'), 403);

        return view('agencies.form', compact('agency'));
    }

    public function update(Request $request, Agency $agency)
    {
        abort_unless($request->user()->can('agencies.manage'), 403);
        $old = $agency->toArray();
        $agency->update($this->validated($request));
        $this->audit->log('updated', 'agencies', $agency, 'Agency updated', $old, $agency->fresh()->toArray());

        return redirect()->route('agencies.index')->with('success', __('Updated successfully.'));
    }

    public function archive(Agency $agency)
    {
        abort_unless(auth()->user()->can('agencies.manage'), 403);
        $agency->update(['is_active' => false]);
        $this->audit->log('archived', 'agencies', $agency, 'Agency archived');

        return back()->with('success', __('Archived successfully.'));
    }

    public function restore(Agency $agency)
    {
        abort_unless(auth()->user()->can('agencies.manage'), 403);
        $agency->restore();
        $agency->update(['is_active' => true]);
        $this->audit->log('restored', 'agencies', $agency, 'Agency restored');

        return back()->with('success', __('Restored successfully.'));
    }

    public function destroy(Agency $agency)
    {
        abort_unless(auth()->user()->isAdmin(), 403);
        if ($agency->suppliers()->exists() || $agency->salesmen()->exists()) {
            return back()->withErrors(['delete' => __('This agency is linked to suppliers or salesmen.')]);
        }
        DB::transaction(function () use ($agency) {
            $this->audit->log('deleted', 'agencies', $agency, 'Agency deleted', $agency->toArray(), []);
            $agency->forceDelete();
        });

        return redirect()->route('agencies.index')->with('success', __('Deleted successfully.'));
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'phone' => ['nullable', 'string', 'max:30'],
            'whatsapp' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'is_active' => ['nullable', 'boolean'],
        ]) + ['is_active' => $request->boolean('is_active', true)];
    }
}
