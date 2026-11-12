<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    public function __construct(private AuditService $audit)
    {
    }

    public function index(Request $request)
    {
        abort_unless($request->user()->can('products.manage'), 403);

        $products = Product::when($request->filled('q'), function ($q) use ($request) {
            $q->where(function ($w) use ($request) {
                $w->where('name', 'like', '%'.$request->q.'%')
                    ->orWhere('sku', 'like', '%'.$request->q.'%');
            });
        })
            ->when($request->filled('status'), function ($q) use ($request) {
                $request->status === 'low'
                    ? $q->whereColumn('stock_qty', '<=', 'low_stock_alert')
                    : $q->where('is_active', $request->status === 'active');
            })
            ->orderBy('name')->paginate(20)->withQueryString();

        $stats = [
            'total' => Product::count(),
            'active' => Product::where('is_active', true)->count(),
            'low' => Product::whereColumn('stock_qty', '<=', 'low_stock_alert')->count(),
            'stock_value' => (float) Product::sum(\DB::raw('stock_qty * purchase_rate')),
        ];

        return view('products.index', compact('products', 'stats'));
    }

    public function create()
    {
        abort_unless(auth()->user()->can('products.manage'), 403);

        return view('products.form', ['product' => new Product(['unit' => 'pcs', 'is_active' => true, 'stock_qty' => 0, 'low_stock_alert' => 0])]);
    }

    public function store(Request $request)
    {
        abort_unless($request->user()->can('products.manage'), 403);
        $product = Product::create($this->validated($request));
        $this->audit->log('created', 'products', $product, 'Product created', [], $product->toArray());

        return redirect()->route('products.index')->with('success', __('Saved successfully.'));
    }

    public function edit(Product $product)
    {
        abort_unless(auth()->user()->can('products.manage'), 403);

        return view('products.form', compact('product'));
    }

    public function update(Request $request, Product $product)
    {
        abort_unless($request->user()->can('products.manage'), 403);
        $old = $product->toArray();
        $product->update($this->validated($request, $product));
        $this->audit->log('updated', 'products', $product, 'Product updated', $old, $product->fresh()->toArray());

        return redirect()->route('products.index')->with('success', __('Updated successfully.'));
    }

    public function destroy(Product $product)
    {
        abort_unless(auth()->user()->can('users.manage'), 403);
        if ($product->purchases()->exists()) {
            $product->update(['is_active' => false]);

            return redirect()->route('products.index')->with('success', __('Product deactivated because it is used in purchases.'));
        }

        $old = $product->toArray();
        $product->delete();
        $this->audit->log('deleted', 'products', $product, 'Product deleted', $old, []);

        return redirect()->route('products.index')->with('success', __('Deleted successfully.'));
    }

    private function validated(Request $request, ?Product $product = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'sku' => ['nullable', 'string', 'max:60', Rule::unique('products', 'sku')->ignore($product?->id)],
            'unit' => ['required', 'string', 'max:20'],
            'purchase_rate' => ['nullable', 'numeric', 'min:0'],
            'sale_rate' => ['nullable', 'numeric', 'min:0'],
            'stock_qty' => ['nullable', 'numeric', 'min:0'],
            'low_stock_alert' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'is_active' => ['nullable', 'boolean'],
        ]) + ['is_active' => $request->boolean('is_active', true)];
    }
}
