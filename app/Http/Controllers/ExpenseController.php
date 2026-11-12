<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Services\AuditService;
use App\Support\DateRange;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ExpenseController extends Controller
{
    public function __construct(private AuditService $audit)
    {
    }

    public function index(Request $request)
    {
        abort_unless($request->user()->can('expenses.view'), 403);
        $range = DateRange::resolve($request);

        $expenses = Expense::with('creator')
            ->when($request->filled('q'), function ($q) use ($request) {
                $q->where(function ($w) use ($request) {
                    $w->where('title', 'like', '%'.$request->q.'%')
                        ->orWhere('reference_no', 'like', '%'.$request->q.'%')
                        ->orWhere('notes', 'like', '%'.$request->q.'%');
                });
            })
            ->when($request->filled('category'), fn ($q) => $q->where('category', $request->category))
            ->when($request->filled('method'), fn ($q) => $q->where('method', $request->method))
            ->tap(fn ($q) => DateRange::apply($q, $range, 'expense_date'))
            ->orderByDesc('expense_date')->orderByDesc('id')
            ->paginate(20)->withQueryString();

        $total = (float) Expense::when($range[0], fn ($q) => $q->where('expense_date', '>=', $range[0]))
            ->when($range[1], fn ($q) => $q->where('expense_date', '<=', $range[1]))->sum('amount');

        return view('expenses.index', [
            'expenses' => $expenses,
            'range' => $range,
            'total' => $total,
            'categories' => $this->categories(),
            'methods' => $this->methods(),
        ]);
    }

    public function create()
    {
        abort_unless(auth()->user()->can('expenses.manage'), 403);

        return view('expenses.form', [
            'expense' => new Expense(['expense_date' => now()->toDateString(), 'category' => 'other', 'method' => 'cash']),
            'categories' => $this->categories(),
            'methods' => $this->methods(),
        ]);
    }

    public function store(Request $request)
    {
        abort_unless($request->user()->can('expenses.manage'), 403);
        $data = $this->validated($request);
        $data['created_by'] = $request->user()->id;
        if ($request->hasFile('attachment')) {
            $data['attachment_path'] = $request->file('attachment')->store('expenses', 'local');
        }
        $expense = Expense::create($data);
        $this->audit->log('created', 'expenses', $expense, 'Expense recorded', [], $expense->toArray());

        return redirect()->route('expenses.index')->with('success', __('Saved successfully.'));
    }

    public function edit(Expense $expense)
    {
        abort_unless(auth()->user()->can('expenses.manage'), 403);

        return view('expenses.form', [
            'expense' => $expense,
            'categories' => $this->categories(),
            'methods' => $this->methods(),
        ]);
    }

    public function update(Request $request, Expense $expense)
    {
        abort_unless($request->user()->can('expenses.manage'), 403);
        $old = $expense->toArray();
        $data = $this->validated($request);
        if ($request->hasFile('attachment')) {
            $data['attachment_path'] = $request->file('attachment')->store('expenses', 'local');
        }
        $expense->update($data);
        $this->audit->log('updated', 'expenses', $expense, 'Expense updated', $old, $expense->fresh()->toArray());

        return redirect()->route('expenses.index')->with('success', __('Updated successfully.'));
    }

    public function destroy(Expense $expense)
    {
        abort_unless(auth()->user()->can('users.manage'), 403);
        $old = $expense->toArray();
        $expense->delete();
        $this->audit->log('deleted', 'expenses', $expense, 'Expense deleted', $old, []);

        return redirect()->route('expenses.index')->with('success', __('Deleted successfully.'));
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'expense_date' => ['required', 'date'],
            'category' => ['required', Rule::in(array_keys($this->categories()))],
            'title' => ['required', 'string', 'max:150'],
            'amount' => ['required', 'numeric', 'gt:0'],
            'method' => ['required', Rule::in(array_keys($this->methods()))],
            'reference_no' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'attachment' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png,webp', 'max:5120'],
        ]);
    }

    private function categories(): array
    {
        return [
            'transport' => __('Transport'),
            'utilities' => __('Utilities'),
            'rent' => __('Rent'),
            'salary' => __('Salary'),
            'maintenance' => __('Maintenance'),
            'office' => __('Office'),
            'other' => __('Other'),
        ];
    }

    private function methods(): array
    {
        return [
            'cash' => __('Cash'),
            'bank' => __('Bank'),
            'cheque' => __('Cheque'),
            'online' => __('Online'),
            'other' => __('Other'),
        ];
    }
}
