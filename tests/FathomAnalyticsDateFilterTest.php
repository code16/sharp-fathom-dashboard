<?php

use Code16\SharpFathomDashboard\Sharp\Filters\FathomAnalyticsDateFilter;
use Illuminate\Support\Carbon;

it('FathomAnalyticsDateFilter defaultValue returns last 30 days range', function () {
    Carbon::setTestNow(Carbon::create(2025, 10, 23, 12, 0, 0));

    $filter = new FathomAnalyticsDateFilter();

    $default = $filter->defaultValue();

    expect($default)
        ->toBeArray()
        ->and($default['start'])->toBe('2025-09-23')
        ->and($default['end'])->toBe('2025-10-23');

    Carbon::setTestNow(); // clear
});
