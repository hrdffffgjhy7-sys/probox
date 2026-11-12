@php
    $selectedPeriod = request('period', $range[3] ?? 'all');
@endphp
<div class="flex flex-wrap items-end gap-2">
    <div>
        <label class="label" for="period">{{ __('Date Range') }}</label>
        <select name="period" id="period" class="select" data-period-select>
            <option value="all" @selected($selectedPeriod === 'all')>{{ __('All time') }}</option>
            <option value="today" @selected($selectedPeriod === 'today')>{{ __('Today') }}</option>
            <option value="yesterday" @selected($selectedPeriod === 'yesterday')>{{ __('Yesterday') }}</option>
            <option value="week" @selected($selectedPeriod === 'week')>{{ __('This Week') }}</option>
            <option value="month" @selected($selectedPeriod === 'month')>{{ __('This Month') }}</option>
            <option value="year" @selected($selectedPeriod === 'year')>{{ __('Year') }}</option>
            <option value="custom" @selected($selectedPeriod === 'custom')>{{ __('Custom Range') }}</option>
        </select>
    </div>
    <div class="hidden flex-wrap items-end gap-2" data-period-custom>
        <div>
            <label class="label" for="from">{{ __('From') }}</label>
            <input type="date" name="from" id="from" value="{{ request('from') }}" class="input">
        </div>
        <div>
            <label class="label" for="to">{{ __('To') }}</label>
            <input type="date" name="to" id="to" value="{{ request('to') }}" class="input">
        </div>
    </div>
</div>
