<?php

namespace Code16\SharpFathomDashboard\Client;

use Cache;
use Code16\SharpFathomDashboard\Client\ValueObjects\Site;

trait FathomClientCache
{
    protected function getCacheKey(): string
    {
        return sprintf(
            '%s-%s-%s',
            $this->startDate?->format('Y-m-d'),
            $this->endDate?->format('Y-m-d'),
            $this->siteId
        );
    }

    protected function getCacheDuration(): int
    {
        return config('sharp-fathom-dashboard.cache_ttl') * 60; //minutes to seconds
    }

    public function getStats(): array
    {
        if (config()->boolean('sharp-fathom-dashboard.cache')) {
            $stats = Cache::get($this->getCacheKey());
            if($stats === null){
                $stats = $this->executeGetStats();
                Cache::put($this->getCacheKey(), $stats, $this->getCacheDuration());
            }

            return $stats;
        } else {
            return $this->executeGetStats();
        }
    }

    public function getSite(): ?Site
    {
        if (config()->boolean('sharp-fathom-dashboard.cache')) {
            $site = Cache::get($this->siteId);
            if($site === null){
                try{
                    $site = $this->executeGetSite();
                    Cache::put($this->siteId, $site, $this->getCacheDuration());
                }catch (\Throwable $e) {
                    report($e);
                    return null;
                }
            }
            return $site;
        } else {
            return $this->executeGetSite();
        }
    }

    public function getMostViewedPages(): array
    {
        if (config()->boolean('sharp-fathom-dashboard.cache')) {
            $pages = Cache::get(sprintf('%s-pages', $this->getCacheKey()));
            if($pages === null){
                $pages = $this->executeGetMostViewedPages();
                Cache::put(sprintf('%s-pages', $this->getCacheKey()), $pages, $this->getCacheDuration());
            }
            return $pages;
        } else {
            return $this->executeGetMostViewedPages();
        }
    }

    public function getTopReferrers(): array
    {
        if (config()->boolean('sharp-fathom-dashboard.cache')) {
            $referrers = Cache::get(sprintf('%s-referrers', $this->getCacheKey()));
            if($referrers === null){
                $referrers = $this->executeGetTopReferrers();
                Cache::put(sprintf('%s-referrers', $this->getCacheKey()), $referrers, $this->getCacheDuration());
            }
            return $referrers;
        } else {
            return $this->executeGetTopReferrers();
        }
    }
}
