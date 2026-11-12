@extends('layouts.app')

@php
    $editing = $purchase->exists;
    $autoPaid = $editing ? (float) $purchase->payments()->where('is_auto', true)->sum('amount') : 0;
@endphp

@section('title', $editing ? __('Edit Purchase') : __('Add Purchase'))
@section('subtitle', __('Purchase invoices update supplier khata and stock automatically.'))

@section('content')
    <form method="POST" action="{{ $editing ? route('purchases.update', $purchase) : route('purchases.store') }}" enctype="multipart/form-data" class="space-y-6" data-purchase-form>
        @csrf
        @if ($editing)
            @method('PUT')
        @endif

        <div class="card">
            <div class="card-body space-y-4">
                <h2 class="section-title">{{ __('Invoice details') }}</h2>

                <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                    <div class="sm:col-span-2">
                        <label for="supplier_id" class="label">{{ __('Supplier') }} <span class="text-rose-500">*</span></label>
                        <select name="supplier_id" id="supplier_id" class="select @error('supplier_id') border-rose-500 @enderror" required>
                            <option value="">{{ __('Select supplier') }}</option>
                            @foreach ($suppliers as $supplier)
                                <option value="{{ $supplier->id }}" @selected((string) old('supplier_id', $purchase->supplier_id) === (string) $supplier->id)>{{ $supplier->name }}</option>
                            @endforeach
                        </select>
                        @error('supplier_id') <p class="mt-1 text-sm text-rose-600 dark:text-rose-400">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="salesman_id" class="label">{{ __('Salesman') }}</label>
                        <select name="salesman_id" id="salesman_id" class="select">
                            <option value="">{{ __('No salesman') }}</option>
                            @foreach ($salesmen as $salesman)
                                <option value="{{ $salesman->id }}" @selected((string) old('salesman_id', $purchase->salesman_id) === (string) $salesman->id)>{{ $salesman->name }}</option>
                            @endforeach
                        </select>
                        @error('salesman_id') <p class="mt-1 text-sm text-rose-600 dark:text-rose-400">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="invoice_no" class="label">{{ __('Invoice No') }}</label>
                        <input id="invoice_no" name="invoice_no" type="text" value="{{ old('invoice_no', $editing ? $purchase->invoice_no : $nextInvoice) }}" class="input @error('invoice_no') border-rose-500 @enderror">
                        @error('invoice_no') <p class="mt-1 text-sm text-rose-600 dark:text-rose-400">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="purchase_date" class="label">{{ __('Purchase Date') }} <span class="text-rose-500">*</span></label>
                        <input id="purchase_date" name="purchase_date" type="date" value="{{ old('purchase_date', $purchase->purchase_date?->format('Y-m-d') ?? now()->toDateString()) }}" class="input @error('purchase_date') border-rose-500 @enderror" required>
                        @error('purchase_date') <p class="mt-1 text-sm text-rose-600 dark:text-rose-400">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="due_date" class="label">{{ __('Due Date') }}</label>
                        <input id="due_date" name="due_date" type="date" value="{{ old('due_date', $purchase->due_date?->format('Y-m-d')) }}" class="input">
                        @error('due_date') <p class="mt-1 text-sm text-rose-600 dark:text-rose-400">{{ $message }}</p> @enderror
                    </div>

                    <div class="sm:col-span-2">
                        <label for="attachment" class="label">{{ __('Attachment') }}</label>
                        <input id="attachment" name="attachment" type="file" accept=".pdf,.jpg,.jpeg,.png,.webp" class="input">
                        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ __('PDF or image up to 5 MB.') }}</p>
                        @error('attachment') <p class="mt-1 text-sm text-rose-600 dark:text-rose-400">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>
        </div>

        <div class="card overflow-hidden">
            <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4 dark:border-slate-800">
                <h2 class="section-title">{{ __('Items') }}</h2>
                <button type="button" class="btn-secondary btn-sm" data-add-item>{{ __('Add Item') }}</button>
            </div>
            <div class="overflow-x-auto">
                <table class="table">
                    <thead class="bg-slate-50 dark:bg-slate-800/50">
                        <tr>
                            <th class="th min-w-52">{{ __('Product') }}</th>
                            <th class="th">{{ __('Qty') }}</th>
                            <th class="th">{{ __('Unit') }}</th>
                            <th class="th">{{ __('Rate') }}</th>
                            <th class="th">{{ __('Discount') }}</th>
                            <th class="th">{{ __('Tax') }}</th>
                            <th class="th text-end">{{ __('Line Total') }}</th>
                            <th class="th"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800" data-items-body data-next-index="{{ $editing ? $purchase->items->count() : 1 }}">
                        @if ($editing && $purchase->items->isNotEmpty())
                            @foreach ($purchase->items as $index => $item)
                                @include('purchases._item-row', ['index' => $index, 'item' => $item])
                            @endforeach
                        @else
                            @include('purchases._item-row', ['index' => 0, 'item' => null])
                        @endif
                    </tbody>
                </table>
            </div>
            <template data-item-template>
                @include('purchases._item-row', ['index' => '__INDEX__', 'item' => null])
            </template>
            @error('items') <p class="px-5 pb-4 text-sm text-rose-600 dark:text-rose-400">{{ $message }}</p> @enderror
            @error('items.*') <p class="px-5 pb-4 text-sm text-rose-600 dark:text-rose-400">{{ $message }}</p> @enderror
        </div>

        <div class="grid gap-6 xl:grid-cols-3">
            <div class="card xl:col-span-2">
                <div class="card-body space-y-4">
                    <div>
                        <label for="notes" class="label">{{ __('Notes') }}</label>
                        <textarea id="notes" name="notes" rows="3" class="textarea" placeholder="{{ __('Add note about this purchase') }}">{{ old('notes', $purchase->notes) }}</textarea>
                        @error('notes') <p class="mt-1 text-sm text-rose-600 dark:text-rose-400">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-body space-y-3">
                    <h2 class="section-title">{{ __('Summary') }}</h2>

                    <div class="flex items-center justify-between text-sm">
                        <span class="text-slate-500 dark:text-slate-400">{{ __('Subtotal') }}</span>
                        <span class="font-semibold" data-output="subtotal">0.00</span>
                    </div>

                    <div>
                        <label for="discount" class="label">{{ __('Discount') }}</label>
                        <input id="discount" name="discount" type="number" step="0.01" min="0" value="{{ old('discount', $purchase->discount ?? 0) }}" class="input text-end" data-summary="discount">
                        @error('discount') <p class="mt-1 text-sm text-rose-600 dark:text-rose-400">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="tax" class="label">{{ __('Tax') }}</label>
                        <input id="tax" name="tax" type="number" step="0.01" min="0" value="{{ old('tax', $purchase->tax ?? 0) }}" class="input text-end" data-summary="tax">
                        @error('tax') <p class="mt-1 text-sm text-rose-600 dark:text-rose-400">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="additional_charges" class="label">{{ __('Additional charges') }}</label>
                        <input id="additional_charges" name="additional_charges" type="number" step="0.01" min="0" value="{{ old('additional_charges', $purchase->additional_charges ?? 0) }}" class="input text-end" data-summary="additional">
                        @error('additional_charges') <p class="mt-1 text-sm text-rose-600 dark:text-rose-400">{{ $message }}</p> @enderror
                    </div>

                    <div class="flex items-center justify-between border-t border-slate-200 pt-3 text-sm dark:border-slate-800">
                        <span class="font-semibold">{{ __('Total') }}</span>
                        <span class="text-lg font-bold" data-output="grand">0.00</span>
                    </div>

                    <div>
                        <label for="initial_paid" class="label">{{ __('Paid with purchase') }}</label>
                        <input id="initial_paid" name="initial_paid" type="number" step="0.01" min="0" value="{{ old('initial_paid', $autoPaid) }}" class="input text-end" data-summary="paid">
                        @error('initial_paid') <p class="mt-1 text-sm text-rose-600 dark:text-rose-400">{{ $message }}</p> @enderror
                    </div>

                    <div class="flex items-center justify-between border-t border-slate-200 pt-3 text-sm dark:border-slate-800">
                        <span class="text-slate-500 dark:text-slate-400">{{ __('Remaining') }}</span>
                        <span class="font-bold text-rose-600 dark:text-rose-400" data-output="remaining">0.00</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="flex items-center justify-end gap-3">
            <a href="{{ $editing ? route('purchases.show', $purchase) : route('purchases.index') }}" class="btn-secondary">{{ __('Cancel') }}</a>
            <button type="submit" class="btn-primary">{{ $editing ? __('Update Purchase') : __('Save Purchase') }}</button>
        </div>
    </form>
@endsection
