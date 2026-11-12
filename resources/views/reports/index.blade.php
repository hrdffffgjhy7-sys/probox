@extends('layouts.app')

@section('title', __('Reports'))
@section('subtitle', __('Business reports and exports.'))

@section('content')
    @php $query = request()->query(); @endphp

    <div class="space-y-6">
        <div class="card">
            <div class="card-body">
                <form method="GET" action="{{ route('reports.index') }}" class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
                    <div>
                        <label for="type" class="label">{{ __('Report') }}</label>
                        <select name="type" id="type" class="select">
                            @foreach ($types as $value => $label)
                                <option value="{{ $value }}" @selected($type === $value)>{{ __($label) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="supplier_id" class="label">{{ __('Supplier') }}</label>
                        <select name="supplier_id" id="supplier_id" class="select">
                            <option value="">{{ __('All suppliers') }}</option>
                            @foreach ($suppliers as $supplier)
                                <option value="{{ $supplier->id }}" @selected((string) request('supplier_id') === (string) $supplier->id)>{{ $supplier->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="salesman_id" class="label">{{ __('Salesman') }}</label>
                        <select name="salesman_id" id="salesman_id" class="select">
                            <option value="">{{ __('All salesmen') }}</option>
                            @foreach ($salesmen as $salesman)
                                <option value="{{ $salesman->id }}" @selected((string) request('salesman_id') === (string) $salesman->id)>{{ $salesman->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex items-end gap-2">
                        @include('partials.date-range')
                    </div>
                    <div class="flex flex-wrap items-end gap-2 sm:col-span-2 xl:col-span-4">
                        <button type="submit" class="btn-primary">{{ __('Generate') }}</button>
                        <a href="{{ route('reports.index') }}" class="btn-secondary">{{ __('Reset') }}</a>
                        <div class="ms-auto flex gap-2">
                            <a href="{{ route('reports.print', $query) }}" target="_blank" class="btn-secondary btn-sm">{{ __('Print') }}</a>
                            <a href="{{ route('reports.pdf', $query) }}" class="btn-secondary btn-sm">{{ __('PDF') }}</a>
                            <a href="{{ route('reports.excel', $query) }}" class="btn-secondary btn-sm">{{ __('Excel') }}</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        @if ($report['note'])
            <div class="card border-amber-200 bg-amber-50 dark:border-amber-500/30 dark:bg-amber-500/10">
                <div class="card-body text-sm text-amber-800 dark:text-amber-200">{{ $report['note'] }}</div>
            </div>
        @endif

        <div class="card overflow-hidden">
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-200 px-5 py-4 dark:border-slate-800">
                <h2 class="section-title">{{ __($report['title']) }}</h2>
                <p class="text-sm text-slate-500 dark:text-slate-400">
                    {{ $range[0]?->format('d M Y') ?? __('All time') }}
                    @if ($range[1]) &ndash; {{ $range[1]->format('d M Y') }} @endif
                </p>
            </div>
            <div class="overflow-x-auto">
                <table class="table">
                    <thead class="bg-slate-50 dark:bg-slate-800/50">
                        <tr>
                            @foreach ($report['headers'] as $header)
                                <th class="th {{ $loop->first ? '' : 'text-end' }}">{{ $header }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @forelse ($report['rows'] as $row)
                            <tr>
                                @foreach ($row as $cell)
                                    <td class="td {{ $loop->first ? '' : 'text-end' }}">{{ $cell }}</td>
                                @endforeach
                            </tr>
                        @empty
                            <tr><td colspan="{{ count($report['headers']) }}" class="td py-12 text-center text-slate-500">{{ __('No data for this report.') }}</td></tr>
                        @endforelse
                    </tbody>
                    @if (! empty($report['totals']))
                        <tfoot class="border-t border-slate-200 bg-slate-50 dark:border-slate-800 dark:bg-slate-800/50">
                            <tr>
                                @foreach ($report['headers'] as $i => $header)
                                    <td class="td font-semibold {{ $i === 0 ? '' : 'text-end' }}">
                                        {{ $i === 0 ? __('Totals') : ($report['totals'][$i] ?? '') }}
                                    </td>
                                @endforeach
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>
        </div>
    </div>
@endsection
