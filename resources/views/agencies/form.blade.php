@extends('layouts.app')

@php $editing = $agency->exists; @endphp

@section('title', $editing ? __('Edit Agency') : __('Add Agency'))
@section('subtitle', __('Agencies group salesmen and suppliers for reporting.'))

@section('content')
    <form method="POST" action="{{ $editing ? route('agencies.update', $agency) : route('agencies.store') }}" class="mx-auto max-w-3xl space-y-6">
        @csrf
        @if ($editing)
            @method('PUT')
        @endif

        <div class="card">
            <div class="card-body space-y-4">
                <h2 class="section-title">{{ __('Agency details') }}</h2>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <label for="name" class="label">{{ __('Agency Name') }} <span class="text-rose-500">*</span></label>
                        <input id="name" name="name" type="text" value="{{ old('name', $agency->name) }}" required class="input @error('name') border-rose-500 @enderror" placeholder="{{ __('Enter agency name') }}">
                        @error('name') <p class="mt-1 text-sm text-rose-600 dark:text-rose-400">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="phone" class="label">{{ __('Phone') }}</label>
                        <input id="phone" name="phone" type="text" value="{{ old('phone', $agency->phone) }}" class="input" placeholder="{{ __('e.g. 03001234567') }}">
                        @error('phone') <p class="mt-1 text-sm text-rose-600 dark:text-rose-400">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="whatsapp" class="label">{{ __('WhatsApp') }}</label>
                        <input id="whatsapp" name="whatsapp" type="text" value="{{ old('whatsapp', $agency->whatsapp) }}" class="input" placeholder="{{ __('e.g. 03001234567') }}">
                        @error('whatsapp') <p class="mt-1 text-sm text-rose-600 dark:text-rose-400">{{ $message }}</p> @enderror
                    </div>

                    <div class="sm:col-span-2">
                        <label for="address" class="label">{{ __('Address') }}</label>
                        <input id="address" name="address" type="text" value="{{ old('address', $agency->address) }}" class="input" placeholder="{{ __('Street, area, city') }}">
                        @error('address') <p class="mt-1 text-sm text-rose-600 dark:text-rose-400">{{ $message }}</p> @enderror
                    </div>

                    <div class="sm:col-span-2">
                        <label for="notes" class="label">{{ __('Notes') }}</label>
                        <textarea id="notes" name="notes" rows="3" class="textarea" placeholder="{{ __('Add note about this agency') }}">{{ old('notes', $agency->notes) }}</textarea>
                        @error('notes') <p class="mt-1 text-sm text-rose-600 dark:text-rose-400">{{ $message }}</p> @enderror
                    </div>
                </div>

                <label class="flex items-center gap-2 text-sm text-slate-700 dark:text-slate-300">
                    <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $agency->is_active ?? true)) class="h-4 w-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500 dark:border-slate-700 dark:bg-slate-900">
                    {{ __('Active') }}
                </label>
            </div>
        </div>

        <div class="flex items-center justify-end gap-3">
            <a href="{{ $editing ? route('agencies.show', $agency) : route('agencies.index') }}" class="btn-secondary">{{ __('Cancel') }}</a>
            <button type="submit" class="btn-primary">{{ $editing ? __('Update') : __('Save') }}</button>
        </div>
    </form>
@endsection
