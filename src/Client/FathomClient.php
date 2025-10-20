<?php

namespace Code16\SharpFathomDashboard\Client;

use Code16\SharpFathomDashboard\Client\ValueObjects\Site;
use Code16\SharpFathomDashboard\Exceptions\ErrorWhileFetchingFathomAnalyticsException;
use Code16\SharpFathomDashboard\Exceptions\FathomMisconfiguredNoAuthTokenException;
use Code16\SharpFathomDashboard\Exceptions\FathomMisconfiguredNoSiteIdException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;

class FathomClient
{
    protected Carbon $startDate;
    protected Carbon $endDate;
    protected ?string $siteId;


    /**
     * @param ?Carbon $startDate
     * @param ?Carbon $endDate
     * @throws FathomMisconfiguredNoAuthTokenException
     * @throws FathomMisconfiguredNoSiteIdException
     */
    public function __construct(?Carbon $startDate = null, ?Carbon $endDate = null) {
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
     * @param ?Carbon $startDate
     * @param ?Carbon $endDate
     * @return FathomClient
     * @throws FathomMisconfiguredNoAuthTokenException
     * @throws FathomMisconfiguredNoSiteIdException
     */
    public static function make(?Carbon $startDate = null, ?Carbon $endDate = null): self
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
        $site = $this->http()
            ->get("/sites/".$this->siteId);

        return $site->successful()
            ? Site::fromPayload($site->json())
            : null;
    }

    public function getStats(): array
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

    protected function http(): PendingRequest
    {
        return Http::withToken(config('sharp-fathom-dashboard.fathom_api_key'))
            ->acceptJson()
            ->baseUrl(config('sharp-fathom-dashboard.fathom_api_url'));
    }
}
