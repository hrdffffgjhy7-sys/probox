@extends('layouts.app')

@php $editing = $product->exists; @endphp

@section('title', $editing ? __('Edit Product') : __('Add Product'))
@section('subtitle', __('Maintain product rates and stock information.'))

@section('content')
    <form method="POST" action="{{ $editing ? route('products.update', $product) : route('products.store') }}" class="space-y-6">
        @csrf
        @if ($editing)
            @method('PUT')
        @endif

        <div class="card">
            <div class="card-body grid gap-4 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <label for="name" class="label">{{ __('Name') }} <span class="text-rose-500">*</span></label>
                    <input id="name" name="name" type="text" value="{{ old('name', $product->name) }}" required class="input @error('name') border-rose-500 @enderror">
                    @error('name') <p class="mt-1 text-sm text-rose-600 dark:text-rose-400">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="sku" class="label">{{ __('SKU') }}</label>
                    <input id="sku" name="sku" type="text" value="{{ old('sku', $product->sku) }}" class="input @error('sku') border-rose-500 @enderror">
                    @error('sku') <p class="mt-1 text-sm text-rose-600 dark:text-rose-400">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="unit" class="label">{{ __('Unit') }} <span class="text-rose-500">*</span></label>
                    <input id="unit" name="unit" type="text" value="{{ old('unit', $product->unit) }}" required class="input @error('unit') border-rose-500 @enderror">
                    @error('unit') <p class="mt-1 text-sm text-rose-600 dark:text-rose-400">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="purchase_rate" class="label">{{ __('Purchase Rate') }}</label>
                    <input id="purchase_rate" name="purchase_rate" type="number" step="0.01" min="0" value="{{ old('purchase_rate', $product->purchase_rate) }}" class="input">
                </div>
                <div>
                    <label for="sale_rate" class="label">{{ __('Sale Rate') }}</label>
                    <input id="sale_rate" name="sale_rate" type="number" step="0.01" min="0" value="{{ old('sale_rate', $product->sale_rate) }}" class="input">
                </div>
                <div>
                    <label for="stock_qty" class="label">{{ __('Stock Quantity') }}</label>
                    <input id="stock_qty" name="stock_qty" type="number" step="0.001" min="0" value="{{ old('stock_qty', $product->stock_qty) }}" class="input">
                </div>
                <div>
                    <label for="low_stock_alert" class="label">{{ __('Low Stock Alert') }}</label>
                    <input id="low_stock_alert" name="low_stock_alert" type="number" step="0.001" min="0" value="{{ old('low_stock_alert', $product->low_stock_alert) }}" class="input">
                </div>
                <div class="sm:col-span-2">
                    <label for="notes" class="label">{{ __('Notes') }}</label>
                    <textarea id="notes" name="notes" rows="3" class="textarea">{{ old('notes', $product->notes) }}</textarea>
                </div>
                <div class="sm:col-span-2">
                    <label class="flex items-center gap-2 text-sm">
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $product->is_active ?? true)) class="checkbox">
                        <span>{{ __('Active') }}</span>
                    </label>
                </div>
            </div>
        </div>

        <div class="flex items-center justify-end gap-3">
            <a href="{{ route('products.index') }}" class="btn-secondary">{{ __('Cancel') }}</a>
            <button type="submit" class="btn-primary">{{ $editing ? __('Update') : __('Save') }}</button>
        </div>
    </form>
@endsection
