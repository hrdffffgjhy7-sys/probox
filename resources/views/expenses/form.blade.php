@extends('layouts.app')

@php $editing = $expense->exists; @endphp

@section('title', $editing ? __('Edit Expense') : __('Add Expense'))
@section('subtitle', __('Record a business expense.'))

@section('content')
    <form method="POST" action="{{ $editing ? route('expenses.update', $expense) : route('expenses.store') }}" enctype="multipart/form-data" class="space-y-6">
        @csrf
        @if ($editing)
            @method('PUT')
        @endif

        <div class="card">
            <div class="card-body grid gap-4 sm:grid-cols-2">
                <div>
                    <label for="expense_date" class="label">{{ __('Date') }} <span class="text-rose-500">*</span></label>
                    <input id="expense_date" name="expense_date" type="date" value="{{ old('expense_date', optional($expense->expense_date)->format('Y-m-d') ?? now()->toDateString()) }}" required class="input @error('expense_date') border-rose-500 @enderror">
                    @error('expense_date') <p class="mt-1 text-sm text-rose-600 dark:text-rose-400">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="category" class="label">{{ __('Category') }} <span class="text-rose-500">*</span></label>
                    <select name="category" id="category" required class="select @error('category') border-rose-500 @enderror">
                        @foreach ($categories as $value => $label)
                            <option value="{{ $value }}" @selected(old('category', $expense->category) === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('category') <p class="mt-1 text-sm text-rose-600 dark:text-rose-400">{{ $message }}</p> @enderror
                </div>
                <div class="sm:col-span-2">
                    <label for="title" class="label">{{ __('Title') }} <span class="text-rose-500">*</span></label>
                    <input id="title" name="title" type="text" value="{{ old('title', $expense->title) }}" required class="input @error('title') border-rose-500 @enderror">
                    @error('title') <p class="mt-1 text-sm text-rose-600 dark:text-rose-400">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="amount" class="label">{{ __('Amount') }} <span class="text-rose-500">*</span></label>
                    <input id="amount" name="amount" type="number" step="0.01" min="0.01" value="{{ old('amount', $expense->amount) }}" required class="input @error('amount') border-rose-500 @enderror">
                    @error('amount') <p class="mt-1 text-sm text-rose-600 dark:text-rose-400">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="method" class="label">{{ __('Method') }} <span class="text-rose-500">*</span></label>
                    <select name="method" id="method" required class="select @error('method') border-rose-500 @enderror">
                        @foreach ($methods as $value => $label)
                            <option value="{{ $value }}" @selected(old('method', $expense->method) === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('method') <p class="mt-1 text-sm text-rose-600 dark:text-rose-400">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="reference_no" class="label">{{ __('Reference No') }}</label>
                    <input id="reference_no" name="reference_no" type="text" value="{{ old('reference_no', $expense->reference_no) }}" class="input">
                </div>
                <div>
                    <label for="attachment" class="label">{{ __('Attachment') }}</label>
                    <input id="attachment" name="attachment" type="file" accept=".pdf,.jpg,.jpeg,.png,.webp" class="input">
                    @if ($editing && $expense->attachment_path)
                        <p class="mt-1 text-xs text-slate-500">{{ __('An attachment is already saved.') }}</p>
                    @endif
                </div>
                <div class="sm:col-span-2">
                    <label for="notes" class="label">{{ __('Notes') }}</label>
                    <textarea id="notes" name="notes" rows="3" class="textarea">{{ old('notes', $expense->notes) }}</textarea>
                </div>
            </div>
        </div>

        <div class="flex items-center justify-end gap-3">
            <a href="{{ route('expenses.index') }}" class="btn-secondary">{{ __('Cancel') }}</a>
            <button type="submit" class="btn-primary">{{ $editing ? __('Update') : __('Save') }}</button>
        </div>
    </form>
@endsection
