<?php

use Code16\SharpFathomDashboard\Client\FathomClient;
use Code16\SharpFathomDashboard\Exceptions\FathomMisconfiguredNoAuthTokenException;
use Code16\SharpFathomDashboard\Exceptions\FathomMisconfiguredNoSiteIdException;
use Code16\SharpFathomDashboard\Exceptions\ErrorWhileFetchingFathomAnalyticsException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

beforeEach(function () {
    // sane defaults for config
    config()->set('sharp-fathom-dashboard.fathom_api_key', 'test-key');
    config()->set('sharp-fathom-dashboard.fathom_site_id', 'SITE_123');
    config()->set('sharp-fathom-dashboard.fathom_api_url', 'https://api.example.test/v1');
    config()->set('sharp-fathom-dashboard.cache', false);
});

it('throws if API key is missing', function () {
    config()->set('sharp-fathom-dashboard.fathom_api_key', null);

    new FathomClient();
})->throws(FathomMisconfiguredNoAuthTokenException::class);

it('throws if site id is missing', function () {
    config()->set('sharp-fathom-dashboard.fathom_site_id', null);

    new FathomClient();
})->throws(FathomMisconfiguredNoSiteIdException::class);

it('getSite returns Site on 200 and null on non-200', function () {
    Http::fakeSequence()
        ->push([
            'id' => 'SITE_123',
            'name' => 'My Site',
            'sharing' => 'private',
            'created_at' => '2025-01-15T12:34:56Z',
        ], 200)
        ->push([], 500);

    $client = new FathomClient();

    $site = $client->getSite();
    expect($site)->not()->toBeNull()
        ->and($site->id)->toBe('SITE_123')
        ->and($site->name)->toBe('My Site');


    $site = $client->getSite();
    expect($site)->toBeNull();
});

it('getSite uses cache when enabled', function () {
    // enable cache
    config()->set('sharp-fathom-dashboard.cache', true);
    config()->set('sharp-fathom-dashboard.cache_ttl', 60);

    // ensure cache is empty
    Cache::forget(config('sharp-fathom-dashboard.fathom_site_id'));

    Http::fakeSequence()
        ->push([
            'id' => 'SITE_123',
            'name' => 'My Site',
            'sharing' => 'private',
            'created_at' => '2025-01-15T12:34:56Z',
        ], 200)
        ->push([], 500);

    $client = new FathomClient();

    $first = $client->getSite();

    $second = $client->getSite();

    expect($first)->not()->toBeNull()
        ->and($second)->not()->toBeNull()
        ->and($second->id)->toBe('SITE_123');
});

it('getStats returns day-keyed array and handles 200', function () {
    $startDate = \Illuminate\Support\Carbon::parse('2025-01-01');
    $endDate = \Illuminate\Support\Carbon::parse('2025-01-02');

    Http::fake([
        '*/aggregations*' => Http::response([
            ['date' => '2025-01-01', 'visits' => 10, 'pageviews' => 20],
            ['date' => '2025-01-02', 'visits' => 15, 'pageviews' => 25],
        ], 200),
    ]);

    $client = new FathomClient($startDate, $endDate);
    $stats = $client->getStats();

    expect($stats)->toBeArray()->toHaveCount(2)
        ->and($stats)->toHaveKeys(['2025-01-01', '2025-01-02'])
        ->and($stats['2025-01-01']['visits'])->toBe(10);
});

it('getStats uses cache when enabled', function () {
    config()->set('sharp-fathom-dashboard.cache', true);
    config()->set('sharp-fathom-dashboard.cache_ttl', 60);

    Http::fakeSequence()
        ->push([
            ['date' => '2025-01-01', 'visits' => 10],
        ], 200)
        ->push([], 500);

    $startDate = \Illuminate\Support\Carbon::parse('2025-01-01');
    $client = new FathomClient($startDate, $startDate);

    $first = $client->getStats();
    $second = $client->getStats();

    expect($first)->toBeArray()
        ->and($second)->toBe($first);
});

it('getMostViewedPages uses cache when enabled', function () {
    config()->set('sharp-fathom-dashboard.cache', true);
    config()->set('sharp-fathom-dashboard.cache_ttl', 60);

    Http::fakeSequence()
        ->push([
            ['pathname' => '/', 'pageviews' => 10],
        ], 200)
        ->push([], 500);

    $client = new FathomClient();
    $first = $client->getMostViewedPages();
    $second = $client->getMostViewedPages();

    expect($first)->toBeArray()
        ->and($second)->toBe($first);
});

it('getTopReferrers uses cache when enabled', function () {
    config()->set('sharp-fathom-dashboard.cache', true);
    config()->set('sharp-fathom-dashboard.cache_ttl', 60);

    Http::fakeSequence()
        ->push([
            ['referrer_hostname' => 'google.com', 'pageviews' => 10],
        ], 200)
        ->push([], 500);

    $client = new FathomClient();
    $first = $client->getTopReferrers();
    $second = $client->getTopReferrers();

    expect($first)->toBeArray()
        ->and($second)->toBe($first);
});

it('executeGetMostViewedPages returns array on 200 and throws on error', function () {
    Http::fakeSequence()
        ->push([
            ['hostname' => 'ex.test', 'pathname' => '/', 'pageviews' => 42],
        ], 200)
        ->push([], 500);

    $client = new FathomClient();

    $result = $client->executeGetMostViewedPages();
    expect($result)->toBeArray()->and($result)->toHaveCount(1);

    $client->executeGetMostViewedPages();
})->throws(ErrorWhileFetchingFathomAnalyticsException::class);

it('executeGetTopReferrers returns array on 200 and throws on error', function () {
    Http::fakeSequence()
        ->push(
            [['referrer_hostname' => 'google.com', 'referrer_pathname' => '/', 'pageviews' => 10]],
            200,
        )
        ->push([], 500);

    $client = new FathomClient();

    $result = $client->executeGetTopReferrers();
    expect($result)->toBeArray()->and($result)->toHaveCount(1);

    $client->executeGetTopReferrers();
})->throws(ErrorWhileFetchingFathomAnalyticsException::class);
