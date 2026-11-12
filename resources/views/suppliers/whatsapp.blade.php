@extends('layouts.app')

@section('title', __('WhatsApp').' · '.$supplier->name)
@section('subtitle', __('Send a prepared message to the supplier.'))

@section('content')
    <div class="space-y-6">
        <div class="card card-body">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <h2 class="text-lg font-bold text-slate-900 dark:text-white">{{ $supplier->name }}</h2>
                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                        {{ __('WhatsApp') }}: {{ $supplier->whatsapp ?: ($supplier->phone ?: __('Not available')) }}
                        @if ($phone)
                            <span class="badge-success ms-2">{{ __('Ready') }}</span>
                        @else
                            <span class="badge-warning ms-2">{{ __('No valid number') }}</span>
                        @endif
                    </p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('suppliers.show', $supplier) }}" class="btn-secondary btn-sm">{{ __('Profile') }}</a>
                    <a href="{{ route('khata.show', $supplier) }}" class="btn-secondary btn-sm">{{ __('Khata') }}</a>
                </div>
            </div>
        </div>

        <div class="grid gap-6 xl:grid-cols-3">
            <div class="card xl:col-span-2">
                <div class="border-b border-slate-200 px-5 py-4 dark:border-slate-800">
                    <h2 class="section-title">{{ __('Message preview') }}</h2>
                </div>
                <div class="card-body space-y-4">
                    <div class="flex flex-wrap gap-2">
                        @foreach (['statement' => __('Statement'), 'payment' => __('Payment'), 'purchase' => __('Purchase')] as $value => $label)
                            <a href="{{ route('suppliers.whatsapp', ['supplier' => $supplier, 'template' => $value]) }}"
                               class="{{ $template === $value ? 'btn-primary' : 'btn-secondary' }} btn-sm">{{ $label }}</a>
                        @endforeach
                    </div>

                    <div class="rounded-xl border border-slate-200 bg-slate-50 p-4 text-sm whitespace-pre-line text-slate-700 dark:border-slate-700 dark:bg-slate-800/60 dark:text-slate-200">{{ $message }}</div>

                    <form method="GET" action="{{ route('suppliers.whatsapp', $supplier) }}" class="space-y-3">
                        <input type="hidden" name="template" value="{{ $template }}">
                        <div>
                            <label for="custom" class="label">{{ __('Add a custom note') }}</label>
                            <textarea id="custom" name="custom" rows="3" class="textarea" placeholder="{{ __('Optional extra text appended to the message') }}">{{ request('custom') }}</textarea>
                        </div>
                        <button type="submit" class="btn-secondary">{{ __('Regenerate message') }}</button>
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="border-b border-slate-200 px-5 py-4 dark:border-slate-800">
                    <h2 class="section-title">{{ __('Send') }}</h2>
                </div>
                <div class="card-body space-y-4">
                    @if ($link)
                        <a href="{{ $link }}" target="_blank" rel="noopener" class="btn-primary w-full justify-center">{{ __('Open WhatsApp') }}</a>
                        <p class="text-xs text-slate-500 dark:text-slate-400">{{ __('WhatsApp Web or the app will open with the message pre-filled. Review before sending.') }}</p>
                    @else
                        <p class="text-sm text-slate-500 dark:text-slate-400">{{ __('Add a phone or WhatsApp number to this supplier to enable sending.') }}</p>
                        @can('suppliers.manage')
                            <a href="{{ route('suppliers.edit', $supplier) }}" class="btn-secondary w-full justify-center">{{ __('Edit supplier') }}</a>
                        @endcan
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
