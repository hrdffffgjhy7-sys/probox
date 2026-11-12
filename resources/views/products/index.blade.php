@extends('layouts.app')

@section('title', __('Products'))
@section('subtitle', __('Product catalogue and stock levels.'))

@section('content')
    @php $money = fn ($value) => \App\Support\Money::format($value); @endphp

    <div class="space-y-6">
        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <div class="card card-body">
                <p class="muted">{{ __('Total Products') }}</p>
                <p class="stat-value mt-2">{{ $stats['total'] }}</p>
            </div>
            <div class="card card-body">
                <p class="muted">{{ __('Active') }}</p>
                <p class="stat-value mt-2 text-emerald-600 dark:text-emerald-400">{{ $stats['active'] }}</p>
            </div>
            <div class="card card-body">
                <p class="muted">{{ __('Low Stock') }}</p>
                <p class="stat-value mt-2 text-amber-600 dark:text-amber-400">{{ $stats['low'] }}</p>
            </div>
            <div class="card card-body">
                <p class="muted">{{ __('Stock Value') }}</p>
                <p class="stat-value mt-2">{{ $money($stats['stock_value']) }}</p>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <form method="GET" action="{{ route('products.index') }}" class="flex flex-wrap items-end gap-3">
                    <div class="min-w-48 flex-1">
                        <label for="q" class="label">{{ __('Search') }}</label>
                        <input type="search" name="q" id="q" value="{{ request('q') }}" placeholder="{{ __('Name or SKU') }}" class="input">
                    </div>
                    <div>
                        <label for="status" class="label">{{ __('Status') }}</label>
                        <select name="status" id="status" class="select">
                            <option value="">{{ __('All') }}</option>
                            <option value="active" @selected(request('status') === 'active')>{{ __('Active') }}</option>
                            <option value="inactive" @selected(request('status') === 'inactive')>{{ __('Inactive') }}</option>
                            <option value="low" @selected(request('status') === 'low')>{{ __('Low stock') }}</option>
                        </select>
                    </div>
                    <button type="submit" class="btn-primary">{{ __('Filter') }}</button>
                    <a href="{{ route('products.index') }}" class="btn-secondary">{{ __('Reset') }}</a>
                    @can('products.manage')
                        <a href="{{ route('products.create') }}" class="btn-primary ms-auto">{{ __('Add Product') }}</a>
                    @endcan
                </form>
            </div>
        </div>

        <div class="card overflow-hidden">
            <div class="overflow-x-auto">
                <table class="table">
                    <thead class="bg-slate-50 dark:bg-slate-800/50">
                        <tr>
                            <th class="th">{{ __('Product') }}</th>
                            <th class="th">{{ __('SKU') }}</th>
                            <th class="th">{{ __('Unit') }}</th>
                            <th class="th text-end">{{ __('Purchase Rate') }}</th>
                            <th class="th text-end">{{ __('Sale Rate') }}</th>
                            <th class="th text-end">{{ __('Stock') }}</th>
                            <th class="th">{{ __('Status') }}</th>
                            <th class="th text-end">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @forelse ($products as $product)
                            <tr>
                                <td class="td font-medium">{{ $product->name }}</td>
                                <td class="td">{{ $product->sku ?? '—' }}</td>
                                <td class="td">{{ $product->unit }}</td>
                                <td class="td text-end">{{ $money($product->purchase_rate) }}</td>
                                <td class="td text-end">{{ $money($product->sale_rate) }}</td>
                                <td class="td text-end">
                                    <span class="{{ (float) $product->stock_qty <= (float) $product->low_stock_alert ? 'text-amber-600 dark:text-amber-400 font-semibold' : '' }}">{{ (float) $product->stock_qty }}</span>
                                </td>
                                <td class="td">
                                    @if ($product->is_active)
                                        <span class="badge-success">{{ __('Active') }}</span>
                                    @else
                                        <span class="badge-neutral">{{ __('Inactive') }}</span>
                                    @endif
                                </td>
                                <td class="td">
                                    <div class="flex items-center justify-end gap-1">
                                        @can('products.manage')
                                            <a href="{{ route('products.edit', $product) }}" class="btn-ghost btn-sm">{{ __('Edit') }}</a>
                                        @endcan
                                        @can('users.manage')
                                            <form method="POST" action="{{ route('products.destroy', $product) }}" data-confirm="{{ __('Delete this product?') }}">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn-ghost btn-sm text-rose-600 dark:text-rose-400">{{ __('Delete') }}</button>
                                            </form>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="td py-12 text-center text-slate-500">{{ __('No products found.') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($products->hasPages())
                <div class="border-t border-slate-200 px-4 py-3 dark:border-slate-800">
                    {{ $products->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
