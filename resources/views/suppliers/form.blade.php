@extends('layouts.app')

@php $editing = $supplier->exists; @endphp

@section('title', $editing ? __('Edit Supplier') : __('Add Supplier'))
@section('subtitle', __('Opening balance is posted to the ledger automatically.'))

@section('content')
    <form method="POST" action="{{ $editing ? route('suppliers.update', $supplier) : route('suppliers.store') }}" class="mx-auto max-w-3xl space-y-6">
        @csrf
        @if ($editing)
            @method('PUT')
        @endif

        <div class="card">
            <div class="card-body space-y-4">
                <h2 class="section-title">{{ __('Supplier account') }}</h2>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <label for="name" class="label">{{ __('Supplier Name') }} <span class="text-rose-500">*</span></label>
                        <input id="name" name="name" type="text" value="{{ old('name', $supplier->name) }}" required class="input @error('name') border-rose-500 @enderror" placeholder="{{ __('Enter name') }}">
                        @error('name') <p class="mt-1 text-sm text-rose-600 dark:text-rose-400">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="agency_id" class="label">{{ __('Agency') }}</label>
                        <select name="agency_id" id="agency_id" class="select">
                            <option value="">{{ __('No agency') }}</option>
                            @foreach ($agencies as $agency)
                                <option value="{{ $agency->id }}" @selected((string) old('agency_id', $supplier->agency_id) === (string) $agency->id)>{{ $agency->name }}</option>
                            @endforeach
                        </select>
                        @error('agency_id') <p class="mt-1 text-sm text-rose-600 dark:text-rose-400">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="salesman_id" class="label">{{ __('Salesman') }}</label>
                        <select name="salesman_id" id="salesman_id" class="select">
                            <option value="">{{ __('No salesman') }}</option>
                            @foreach ($salesmen as $salesman)
                                <option value="{{ $salesman->id }}" @selected((string) old('salesman_id', $supplier->salesman_id) === (string) $salesman->id)>{{ $salesman->name }}</option>
                            @endforeach
                        </select>
                        @error('salesman_id') <p class="mt-1 text-sm text-rose-600 dark:text-rose-400">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="phone" class="label">{{ __('Phone') }}</label>
                        <input id="phone" name="phone" type="text" value="{{ old('phone', $supplier->phone) }}" class="input" placeholder="{{ __('e.g. 03001234567') }}">
                        @error('phone') <p class="mt-1 text-sm text-rose-600 dark:text-rose-400">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="whatsapp" class="label">{{ __('WhatsApp') }}</label>
                        <input id="whatsapp" name="whatsapp" type="text" value="{{ old('whatsapp', $supplier->whatsapp) }}" class="input" placeholder="{{ __('e.g. 03001234567') }}">
                        @error('whatsapp') <p class="mt-1 text-sm text-rose-600 dark:text-rose-400">{{ $message }}</p> @enderror
                    </div>

                    <div class="sm:col-span-2">
                        <label for="address" class="label">{{ __('Address') }}</label>
                        <input id="address" name="address" type="text" value="{{ old('address', $supplier->address) }}" class="input" placeholder="{{ __('Street, area, city') }}">
                        @error('address') <p class="mt-1 text-sm text-rose-600 dark:text-rose-400">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body space-y-4">
                <h2 class="section-title">{{ __('Opening Balance') }}</h2>
                <p class="muted">{{ __('Receivable opening balances are stored as credits.') }}</p>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="opening_balance" class="label">{{ __('Amount') }}</label>
                        <input id="opening_balance" name="opening_balance" type="number" step="0.01" min="0" value="{{ old('opening_balance', $supplier->opening_balance ?? 0) }}" class="input @error('opening_balance') border-rose-500 @enderror">
                        @error('opening_balance') <p class="mt-1 text-sm text-rose-600 dark:text-rose-400">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="opening_balance_type" class="label">{{ __('Opening Balance Type') }}</label>
                        <select name="opening_balance_type" id="opening_balance_type" class="select">
                            <option value="payable" @selected(old('opening_balance_type', $supplier->opening_balance_type ?? 'payable') === 'payable')>{{ __('Payable (we owe supplier)') }}</option>
                            <option value="receivable" @selected(old('opening_balance_type', $supplier->opening_balance_type) === 'receivable')>{{ __('Receivable (supplier owes us)') }}</option>
                        </select>
                        @error('opening_balance_type') <p class="mt-1 text-sm text-rose-600 dark:text-rose-400">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body space-y-4">
                <div>
                    <label for="notes" class="label">{{ __('Notes') }}</label>
                    <textarea id="notes" name="notes" rows="3" class="textarea" placeholder="{{ __('Add note about this entry') }}">{{ old('notes', $supplier->notes) }}</textarea>
                    @error('notes') <p class="mt-1 text-sm text-rose-600 dark:text-rose-400">{{ $message }}</p> @enderror
                </div>

                <label class="flex items-center gap-2 text-sm text-slate-700 dark:text-slate-300">
                    <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $supplier->is_active ?? true)) class="h-4 w-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500 dark:border-slate-700 dark:bg-slate-900">
                    {{ __('Active User') }}
                </label>
            </div>
        </div>

        <div class="flex items-center justify-end gap-3">
            <a href="{{ $editing ? route('suppliers.show', $supplier) : route('suppliers.index') }}" class="btn-secondary">{{ __('Cancel') }}</a>
            <button type="submit" class="btn-primary">{{ $editing ? __('Update') : __('Save') }}</button>
        </div>
    </form>
@endsection
