<?php

namespace Code16\SharpFathomDashboard\Client;

use Cache;
use Code16\SharpFathomDashboard\Client\ValueObjects\Site;
use Code16\SharpFathomDashboard\Exceptions\ErrorWhileFetchingFathomAnalyticsException;
use Code16\SharpFathomDashboard\Exceptions\FathomMisconfiguredNoAuthTokenException;
use Code16\SharpFathomDashboard\Exceptions\FathomMisconfiguredNoSiteIdException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;

class FathomClient
{
    use FathomClientCache;

    protected \Carbon\Carbon|Carbon $startDate;
    protected \Carbon\Carbon|Carbon $endDate;
    protected ?string $siteId;


    /**
     * @param null|\Carbon\Carbon|Carbon $startDate
     * @param null|\Carbon\Carbon|Carbon $endDate
     * @throws FathomMisconfiguredNoAuthTokenException
     * @throws FathomMisconfiguredNoSiteIdException
     */
    public function __construct(null|\Carbon\Carbon|Carbon $startDate = null, null|\Carbon\Carbon|Carbon $endDate = null) {
        $this->startDate = $startDate ?? Carbon::today()->subDays(30);
        $this->endDate = $endDate ?? Carbon::today();
        $this->siteId = config('sharp-fathom-dashboard.fathom_site_id');

        if (empty(config('sharp-fathom-dashboard.fathom_api_key'))) {
            throw new FathomMisconfiguredNoAuthTokenException();
        }

        if (empty(config('sharp-fathom-dashboard.fathom_site_id'))) {
            throw new FathomMisconfiguredNoSiteIdException();
        }
    }

    /**
     * @param null|\Carbon\Carbon|Carbon $startDate
     * @param null|\Carbon\Carbon|Carbon $endDate
     * @return FathomClient
     * @throws FathomMisconfiguredNoAuthTokenException
     * @throws FathomMisconfiguredNoSiteIdException
     */
    public static function make(null|\Carbon\Carbon|Carbon $startDate = null, null|\Carbon\Carbon|Carbon $endDate = null): self
    {
        return new self($startDate, $endDate);
    }

    public function getStartDate(): Carbon
    {
        return $this->startDate;
    }

    public function getEndDate(): Carbon
    {
        return $this->endDate;
    }

    public function getSite(): ?Site
    {
        if (config()->boolean('sharp-fathom-dashboard.cache')) {
            $data = Cache::get($this->siteId);
            return Cache::remember($this->siteId, $this->getCacheDuration(), fn () => $this->executeGetSite());
        } else {
            return $this->executeGetSite();
        }
    }

    public function executeGetSite(): ?Site
    {
        $site = $this->http()
            ->get("/sites/".$this->siteId);

        return $site->successful()
            ? Site::fromPayload($site->json())
            : null;
    }

    private function executeGetStats(): array
    {
        $data = $this->http()->withQueryParameters([
            'entity' => 'pageview',
            'entity_id' => $this->siteId,
            'date_grouping' => 'day',
            'date_from' => $this->startDate->format('Y-m-d H:i:s'),
            'date_to' => $this->endDate->clone()->addDay()->format('Y-m-d H:i:s'),
            'timezone' => config('app.timezone'),
            'aggregates' => collect([
                'visits',
                'uniques',
                'pageviews',
                'avg_duration',
                'bounce_rate',
            ])->join(',')
        ])->get('/aggregations');

        if (!$data->successful()) {
            throw new ErrorWhileFetchingFathomAnalyticsException($data->body());
        }

        $data = $data->json();
        $days = [];

        foreach ($this->startDate->clone()->daysUntil($this->endDate) as $day) {
            $days[$day->format('Y-m-d')] =
                collect($data)->firstWhere('date', $day->format('Y-m-d')) ?? [];
        }

        return $days;
    }

    public function executeGetMostViewedPages(): array
    {
        $data = $this->http()->withQueryParameters([
            'entity' => 'pageview',
            'entity_id' => $this->siteId,
            'field_grouping' => "hostname,pathname",
            'sort_by' => 'pageviews:desc',
            'date_from' => $this->startDate->format('Y-m-d H:i:s'),
            'date_to' => $this->endDate->clone()->addDay()->format('Y-m-d H:i:s'),
            'timezone' => config('app.timezone'),
            'aggregates' => collect([
                'visits',
                'uniques',
                'pageviews',
                'avg_duration',
                'bounce_rate',
            ])->join(','),
            'limit' => 30,
        ])->get('/aggregations');

        if (!$data->successful()) {
            throw new ErrorWhileFetchingFathomAnalyticsException($data->body());
        }

        return $data->json() ?? [];
    }

    public function executeGetTopReferrers(): array
    {
        $data = $this->http()->withQueryParameters([
            'entity' => 'pageview',
            'entity_id' => $this->siteId,
            'field_grouping' => "referrer_hostname,referrer_pathname",
            'sort_by' => 'pageviews:desc',
            'date_from' => $this->startDate->format('Y-m-d H:i:s'),
            'date_to' => $this->endDate->clone()->addDay()->format('Y-m-d H:i:s'),
            'timezone' => config('app.timezone'),
            'aggregates' => collect([
                'visits',
                'uniques',
                'pageviews',
            ])->join(','),
            'limit' => 30,
        ])->get('/aggregations');

        if (!$data->successful()) {
            throw new ErrorWhileFetchingFathomAnalyticsException($data->body());
        }

        return $data->json() ?? [];
    }

    protected function http(): PendingRequest
    {
        return Http::withToken(config('sharp-fathom-dashboard.fathom_api_key'))
            ->acceptJson()
            ->baseUrl(config('sharp-fathom-dashboard.fathom_api_url'));
    }
}
