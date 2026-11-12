<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Supplier;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->input('q'));
        $results = [
            'suppliers' => collect(),
            'purchases' => collect(),
            'payments' => collect(),
            'products' => collect(),
        ];

        if ($q !== '') {
            $results['suppliers'] = Supplier::with(['agency', 'salesman'])
                ->where(function ($w) use ($q) {
                    $w->where('name', 'like', "%$q%")
                        ->orWhere('phone', 'like', "%$q%")
                        ->orWhere('whatsapp', 'like', "%$q%")
                        ->orWhereHas('agency', fn ($a) => $a->where('name', 'like', "%$q%"));
                })->limit(20)->get();

            $results['purchases'] = Purchase::with('supplier')
                ->where(function ($w) use ($q) {
                    $w->where('invoice_no', 'like', "%$q%")
                        ->orWhereHas('supplier', fn ($s) => $s->where('name', 'like', "%$q%"));
                })->orderByDesc('purchase_date')->limit(20)->get();

            $results['payments'] = Payment::with('supplier')
                ->where(function ($w) use ($q) {
                    $w->where('reference_no', 'like', "%$q%")
                        ->orWhereHas('supplier', fn ($s) => $s->where('name', 'like', "%$q%"));
                })->orderByDesc('payment_date')->limit(20)->get();

            $results['products'] = Product::where('name', 'like', "%$q%")
                ->orWhere('sku', 'like', "%$q%")->limit(20)->get();
        }

        return view('search.index', compact('q', 'results'));
    }
}
