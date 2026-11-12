@extends('layouts.app')

@php $editing = $payment->exists; @endphp

@section('title', $editing ? __('Edit Payment') : __('Add Payment'))
@section('subtitle', __('Record a payment against a supplier khata.'))

@section('content')
    <form method="POST" action="{{ $editing ? route('payments.update', $payment) : route('payments.store') }}" enctype="multipart/form-data" class="space-y-6" data-purchase-form>
        @csrf
        @if ($editing)
            @method('PUT')
        @endif

        <div class="hidden" aria-hidden="true">
            <div data-items-body></div>
            <template data-item-template><tr data-item-row></tr></template>
        </div>

        <div class="card">
            <div class="card-body space-y-4">
                <h2 class="section-title">{{ __('Payment details') }}</h2>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="supplier_id" class="label">{{ __('Supplier') }} <span class="text-rose-500">*</span></label>
                        <select name="supplier_id" id="supplier_id" class="select @error('supplier_id') border-rose-500 @enderror" data-payment-supplier required>
                            <option value="">{{ __('Select supplier') }}</option>
                            @foreach ($suppliers as $supplier)
                                <option value="{{ $supplier->id }}" @selected((string) old('supplier_id', $payment->supplier_id) === (string) $supplier->id)>{{ $supplier->name }}</option>
                            @endforeach
                        </select>
                        @error('supplier_id') <p class="mt-1 text-sm text-rose-600 dark:text-rose-400">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="purchase_id" class="label">{{ __('Purchase (optional)') }}</label>
                        <select name="purchase_id" id="purchase_id" class="select @error('purchase_id') border-rose-500 @enderror" data-payment-purchase>
                            <option value="">{{ __('General payment') }}</option>
                            @foreach ($purchases as $purchase)
                                <option value="{{ $purchase->id }}"
                                    data-supplier="{{ $purchase->supplier_id }}"
                                    data-remaining="{{ $purchase->remaining_amount }}"
                                    @selected((string) old('purchase_id', $payment->purchase_id) === (string) $purchase->id)>
                                    {{ $purchase->invoice_no }} · {{ $purchase->purchase_date?->format('d M Y') }} · {{ __('Due') }} {{ \App\Support\Money::format($purchase->remaining_amount) }}
                                </option>
                            @endforeach
                        </select>
                        @error('purchase_id') <p class="mt-1 text-sm text-rose-600 dark:text-rose-400">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="salesman_id" class="label">{{ __('Salesman') }}</label>
                        <select name="salesman_id" id="salesman_id" class="select">
                            <option value="">{{ __('No salesman') }}</option>
                            @foreach ($salesmen as $salesman)
                                <option value="{{ $salesman->id }}" @selected((string) old('salesman_id', $payment->salesman_id) === (string) $salesman->id)>{{ $salesman->name }}</option>
                            @endforeach
                        </select>
                        @error('salesman_id') <p class="mt-1 text-sm text-rose-600 dark:text-rose-400">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="payment_date" class="label">{{ __('Payment Date') }} <span class="text-rose-500">*</span></label>
                        <input id="payment_date" name="payment_date" type="date" value="{{ old('payment_date', $payment->payment_date?->format('Y-m-d') ?? now()->toDateString()) }}" class="input @error('payment_date') border-rose-500 @enderror" required>
                        @error('payment_date') <p class="mt-1 text-sm text-rose-600 dark:text-rose-400">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="amount" class="label">{{ __('Amount') }} <span class="text-rose-500">*</span></label>
                        <input id="amount" name="amount" type="number" step="0.01" min="0.01" value="{{ old('amount', $payment->amount) }}" class="input text-end @error('amount') border-rose-500 @enderror" required>
                        @error('amount') <p class="mt-1 text-sm text-rose-600 dark:text-rose-400">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="method" class="label">{{ __('Method') }} <span class="text-rose-500">*</span></label>
                        <select name="method" id="method" class="select @error('method') border-rose-500 @enderror" required>
                            @foreach (['cash' => __('Cash'), 'bank' => __('Bank'), 'cheque' => __('Cheque'), 'online' => __('Online'), 'other' => __('Other')] as $value => $label)
                                <option value="{{ $value }}" @selected(old('method', $payment->method ?? 'cash') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('method') <p class="mt-1 text-sm text-rose-600 dark:text-rose-400">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="reference_no" class="label">{{ __('Reference No') }}</label>
                        <input id="reference_no" name="reference_no" type="text" value="{{ old('reference_no', $payment->reference_no) }}" class="input" placeholder="{{ __('Cheque / transaction reference') }}">
                        @error('reference_no') <p class="mt-1 text-sm text-rose-600 dark:text-rose-400">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="receipt" class="label">{{ __('Receipt') }}</label>
                        <input id="receipt" name="receipt" type="file" accept=".pdf,.jpg,.jpeg,.png,.webp" class="input">
                        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ __('PDF or image up to 5 MB.') }}</p>
                        @error('receipt') <p class="mt-1 text-sm text-rose-600 dark:text-rose-400">{{ $message }}</p> @enderror
                    </div>

                    <div class="sm:col-span-2">
                        <label for="notes" class="label">{{ __('Notes') }}</label>
                        <textarea id="notes" name="notes" rows="3" class="textarea">{{ old('notes', $payment->notes) }}</textarea>
                        @error('notes') <p class="mt-1 text-sm text-rose-600 dark:text-rose-400">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>
        </div>

        <div class="flex items-center justify-end gap-3">
            <a href="{{ $editing ? route('payments.show', $payment) : route('payments.index') }}" class="btn-secondary">{{ __('Cancel') }}</a>
            <button type="submit" class="btn-primary">{{ $editing ? __('Update Payment') : __('Save Payment') }}</button>
        </div>
    </form>
@endsection
