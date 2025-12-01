<?php

namespace Code16\SharpFathomDashboard\Sharp\Filters;

use Code16\Sharp\Filters\DateRangeRequiredFilter;

class FathomAnalyticsDateFilter extends DateRangeRequiredFilter
{
    public function buildFilterConfig(): void
    {
        $this->configureDateFormat("DD/MM/YYYY")
            ->configureMondayFirst(true)
            ->configureShowPresets();
    }

    public function defaultValue(): array
    {
        return [
            'start' => now()->subDays(29)->format('Y-m-d'),
            'end' => now()->format('Y-m-d'),
        ];
    }
}
