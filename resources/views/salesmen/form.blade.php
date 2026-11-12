@extends('layouts.app')

@php $editing = $salesman->exists; @endphp

@section('title', $editing ? __('Edit Salesman') : __('Add Salesman'))
@section('subtitle', __('Salesmen can be linked to agencies and suppliers.'))

@section('content')
    <form method="POST" action="{{ $editing ? route('salesmen.update', $salesman) : route('salesmen.store') }}" class="mx-auto max-w-3xl space-y-6">
        @csrf
        @if ($editing)
            @method('PUT')
        @endif

        <div class="card">
            <div class="card-body space-y-4">
                <h2 class="section-title">{{ __('Salesman details') }}</h2>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <label for="name" class="label">{{ __('Salesman Name') }} <span class="text-rose-500">*</span></label>
                        <input id="name" name="name" type="text" value="{{ old('name', $salesman->name) }}" required class="input @error('name') border-rose-500 @enderror" placeholder="{{ __('Enter name') }}">
                        @error('name') <p class="mt-1 text-sm text-rose-600 dark:text-rose-400">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="agency_id" class="label">{{ __('Agency') }}</label>
                        <select name="agency_id" id="agency_id" class="select">
                            <option value="">{{ __('No agency') }}</option>
                            @foreach ($agencies as $agency)
                                <option value="{{ $agency->id }}" @selected((string) old('agency_id', $salesman->agency_id) === (string) $agency->id)>{{ $agency->name }}</option>
                            @endforeach
                        </select>
                        @error('agency_id') <p class="mt-1 text-sm text-rose-600 dark:text-rose-400">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="phone" class="label">{{ __('Phone') }}</label>
                        <input id="phone" name="phone" type="text" value="{{ old('phone', $salesman->phone) }}" class="input" placeholder="{{ __('e.g. 03001234567') }}">
                        @error('phone') <p class="mt-1 text-sm text-rose-600 dark:text-rose-400">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="whatsapp" class="label">{{ __('WhatsApp') }}</label>
                        <input id="whatsapp" name="whatsapp" type="text" value="{{ old('whatsapp', $salesman->whatsapp) }}" class="input" placeholder="{{ __('e.g. 03001234567') }}">
                        @error('whatsapp') <p class="mt-1 text-sm text-rose-600 dark:text-rose-400">{{ $message }}</p> @enderror
                    </div>

                    <div class="sm:col-span-2">
                        <label for="address" class="label">{{ __('Address') }}</label>
                        <input id="address" name="address" type="text" value="{{ old('address', $salesman->address) }}" class="input" placeholder="{{ __('Street, area, city') }}">
                        @error('address') <p class="mt-1 text-sm text-rose-600 dark:text-rose-400">{{ $message }}</p> @enderror
                    </div>

                    <div class="sm:col-span-2">
                        <label for="notes" class="label">{{ __('Notes') }}</label>
                        <textarea id="notes" name="notes" rows="3" class="textarea" placeholder="{{ __('Add note about this salesman') }}">{{ old('notes', $salesman->notes) }}</textarea>
                        @error('notes') <p class="mt-1 text-sm text-rose-600 dark:text-rose-400">{{ $message }}</p> @enderror
                    </div>
                </div>

                <label class="flex items-center gap-2 text-sm text-slate-700 dark:text-slate-300">
                    <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $salesman->is_active ?? true)) class="h-4 w-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500 dark:border-slate-700 dark:bg-slate-900">
                    {{ __('Active') }}
                </label>
            </div>
        </div>

        <div class="flex items-center justify-end gap-3">
            <a href="{{ $editing ? route('salesmen.show', $salesman) : route('salesmen.index') }}" class="btn-secondary">{{ __('Cancel') }}</a>
            <button type="submit" class="btn-primary">{{ $editing ? __('Update') : __('Save') }}</button>
        </div>
    </form>
@endsection
