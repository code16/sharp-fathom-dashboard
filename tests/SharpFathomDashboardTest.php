<?php

use Code16\SharpFathomDashboard\Sharp\Filters\FathomAnalyticsDateFilter;
use Code16\SharpFathomDashboard\Sharp\SharpFathomDashboard;
use Code16\SharpFathomDashboard\Client\FathomClient;
use Illuminate\Support\Facades\Http;
use Code16\Sharp\Dashboard\Widgets\WidgetsContainer;
use Code16\Sharp\Dashboard\Layout\DashboardLayout;

beforeEach(function () {
    config()->set('sharp-fathom-dashboard.fathom_api_key', 'test-key');
    config()->set('sharp-fathom-dashboard.fathom_site_id', 'SITE_123');
    config()->set('sharp-fathom-dashboard.chart.datasets', ['pageviews', 'unique_visitors']);
});

it('can build widgets', function () {
    $dashboard = new SharpFathomDashboard();

    expect($dashboard->widgets())->toHaveCount(7)
        ->and($dashboard->widgets())->toHaveKeys([
            'unique_visitors',
            'pageviews',
            'avg_time_on_site',
            'bounce_rate',
            'daily_analytics',
            'most_viewed_pages',
            'top_referrers'
        ]);
});

it('can build layout', function () {
    $dashboard = new SharpFathomDashboard();

    $layout = $dashboard->widgetsLayout();

    expect($layout['sections'])->toHaveCount(3);
});

it('can build widgets data', function () {
    Http::fake([
        '*/sites/*' => Http::response(['id' => 'SITE_123', 'name' => 'Test Site'], 200),
        '*/aggregations*' => Http::response([
            [
                'hostname' => 'ex.test',
                'pathname' => '/',
                'pageviews' => 10,
                'date' => now()->subDay()->format('Y-m-d'),
                'visits' => 10,
                'uniques' => 5,
                'avg_duration' => 60,
                'bounce_rate' => 0.5,
                'referrer_hostname' => 'google.com',
                'referrer_pathname' => '/'
            ],
        ], 200),
    ]);

    $dashboard = new SharpFathomDashboard();
    $dashboard->initQueryParams([
        FathomAnalyticsDateFilter::class => '2025-01-01 - 2025-01-31'
    ]);

    $data = $dashboard->data();

    expect($data)->toHaveKeys([
        'unique_visitors',
        'pageviews',
        'avg_time_on_site',
        'bounce_rate',
        'daily_analytics',
        'most_viewed_pages',
        'top_referrers'
    ]);

    expect($data['unique_visitors']['data']['figure'])->toBe('10')
        ->and($data['pageviews']['data']['figure'])->toBe('10');
});
