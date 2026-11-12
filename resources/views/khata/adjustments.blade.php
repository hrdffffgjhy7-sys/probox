@extends('layouts.app')

@section('title', __('Adjustments'))
@section('subtitle', __('Manual ledger adjustments.'))

@section('content')
    @php $money = fn ($value) => \App\Support\Money::format($value); @endphp

    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <a href="{{ route('khata.index') }}" class="btn-ghost btn-sm">{{ __('Back to Khata') }}</a>
        </div>

        <div class="card overflow-hidden">
            <div class="overflow-x-auto">
                <table class="table">
                    <thead class="bg-slate-50 dark:bg-slate-800/50">
                        <tr>
                            <th class="th">{{ __('Date') }}</th>
                            <th class="th">{{ __('Supplier') }}</th>
                            <th class="th">{{ __('Direction') }}</th>
                            <th class="th">{{ __('Category') }}</th>
                            <th class="th">{{ __('Reason') }}</th>
                            <th class="th">{{ __('Reference') }}</th>
                            <th class="th text-end">{{ __('Amount') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @forelse ($rows as $row)
                            <tr>
                                <td class="td">{{ $row->adjustment_date?->format('d M Y') }}</td>
                                <td class="td">
                                    @if ($row->supplier)
                                        <a href="{{ route('khata.show', $row->supplier) }}" class="link">{{ $row->supplier->name }}</a>
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="td">
                                    <span class="{{ $row->direction === 'increase' ? 'badge-warning' : 'badge-success' }}">{{ __(ucfirst($row->direction)) }}</span>
                                </td>
                                <td class="td">{{ __(ucfirst($row->category)) }}</td>
                                <td class="td">{{ $row->reason }}</td>
                                <td class="td">{{ $row->reference_no ?? '—' }}</td>
                                <td class="td text-end font-medium">{{ $money($row->amount) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="td py-12 text-center text-slate-500">{{ __('No adjustments found.') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($rows->hasPages())
                <div class="border-t border-slate-200 px-4 py-3 dark:border-slate-800">
                    {{ $rows->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
