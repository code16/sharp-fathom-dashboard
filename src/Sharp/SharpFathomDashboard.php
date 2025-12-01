<?php

namespace Code16\SharpFathomDashboard\Sharp;

use Carbon\Carbon;
use Carbon\CarbonInterval;
use Carbon\CarbonPeriod;
use Code16\Sharp\Dashboard\Layout\DashboardLayout;
use Code16\Sharp\Dashboard\Layout\DashboardLayoutRow;
use Code16\Sharp\Dashboard\Layout\DashboardLayoutSection;
use Code16\Sharp\Dashboard\SharpDashboard;
use Code16\Sharp\Dashboard\Widgets\SharpFigureWidget;
use Code16\Sharp\Dashboard\Widgets\SharpGraphWidgetDataSet;
use Code16\Sharp\Dashboard\Widgets\SharpLineGraphWidget;
use Code16\Sharp\Dashboard\Widgets\SharpPanelWidget;
use Code16\Sharp\Dashboard\Widgets\WidgetsContainer;
use Code16\Sharp\Exceptions\Form\SharpApplicativeException;
use Code16\SharpFathomDashboard\Client\FathomClient;
use Code16\SharpFathomDashboard\Exceptions\ErrorWhileFetchingFathomAnalyticsException;
use Code16\SharpFathomDashboard\Exceptions\FathomMisconfiguredNoAuthTokenException;
use Code16\SharpFathomDashboard\Exceptions\FathomMisconfiguredNoSiteIdException;
use Code16\SharpFathomDashboard\Sharp\Commands\OpenFathomSharpCommand;
use Code16\SharpFathomDashboard\Sharp\Filters\FathomAnalyticsDateFilter;
use Illuminate\Support\Number;
use Throwable;

class SharpFathomDashboard extends SharpDashboard {

    protected function buildWidgets(WidgetsContainer $widgetsContainer): void
    {
        $widgetsContainer
            ->addWidget(
                SharpFigureWidget::make("unique_visitors")
                    ->setTitle(__('Unique visitors'))
            )
            ->addWidget(
                SharpFigureWidget::make("pageviews")
                    ->setTitle(__('Page views'))
            )
            ->addWidget(
                SharpFigureWidget::make("avg_time_on_site")
                    ->setTitle(__('Average time on site'))
            )
            ->addWidget(
                SharpFigureWidget::make('bounce_rate')
                    ->setTitle(__('Bounce rate'))
            )
            ->addWidget(
                SharpLineGraphWidget::make('daily_analytics')
                    ->setTitle(__('Visits'))
                    ->setHeight(350)
                    ->setDisplayHorizontalAxisAsTimeline()
            )
            ->addWidget(
                SharpPanelWidget::make('most_viewed_pages')
                    ->setTitle(__('Most viewed pages'))
                    ->setTemplate(view("sharp-fathom-dashboard::most-viewed-pages"))
            )
            ->addWidget(
                SharpPanelWidget::make('top_referrers')
                    ->setTitle(__('Top Referrers'))
                    ->setTemplate(view("sharp-fathom-dashboard::top-referrers"))
            );
    }

    protected function buildDashboardLayout(DashboardLayout $dashboardLayout): void
    {
        $dashboardLayout
            ->addSection('', function (DashboardLayoutSection $section) {
                $section
                    ->addRow(function (DashboardLayoutRow $row) {
                        $row
                            ->addWidget(3, 'unique_visitors')
                            ->addWidget(3, 'pageviews')
                            ->addWidget(3, 'avg_time_on_site')
                            ->addWidget(3, 'bounce_rate');
                    });
            })
            ->addSection('', function (DashboardLayoutSection $section) {
                $section
                    ->addRow(function (DashboardLayoutRow $row) {
                        $row
                            ->addWidget(12, 'daily_analytics');
                    });
            })
            ->addSection('', function (DashboardLayoutSection $section) {
                $section
                    ->addRow(function (DashboardLayoutRow $row) {
                        $row
                            ->addWidget(6, 'most_viewed_pages')
                            ->addWidget(6, 'top_referrers');
                    });
            });
    }

    public function getFilters(): ?array
    {
        return [
            FathomAnalyticsDateFilter::class,
        ];
    }

    public function getDashboardCommands(): ?array
    {
        return [
            OpenFathomSharpCommand::class,
        ];
    }

    protected function buildWidgetsData(): void
    {
        $period = $this->queryParams->filterFor(FathomAnalyticsDateFilter::class);

        try {
            $fathom = app(FathomClient::class)->make($period->getStart(), $period->getEnd());
            $stats = collect($fathom->getStats())->filter();

            $mostViewedPages = collect($fathom->getMostViewedPages());

            $topReferrers = collect($fathom->getTopReferrers());
        } catch(Throwable $e) {
            $message = null;
            if($e instanceof ErrorWhileFetchingFathomAnalyticsException){
                $message = "Error while fetching analytics data from fathom: " . $e->getMessage();
            }
            if($e instanceof FathomMisconfiguredNoAuthTokenException){
                $message = "Fathom API key is not configured";
            }
            if($e instanceof FathomMisconfiguredNoSiteIdException){
                $message = "Fathom site ID is not configured";
            }

            throw new SharpApplicativeException($message ?? $e->getMessage());
        }


        $this
            ->setFigureData('unique_visitors', Number::format($stats->sum('visits'), locale: app()->getLocale()))
            ->setFigureData('pageviews', Number::format($stats->sum('pageviews'), locale: app()->getLocale()))
            ->setFigureData(
                'avg_time_on_site',
                CarbonInterval::seconds($stats->avg('avg_duration'))->cascade()->forHumans(short: true)
            )
            ->setFigureData(
                'bounce_rate',
                (round($stats->avg('bounce_rate'), precision: 2) * 100) . '%'
            );

        if (in_array('pageviews', config()->array('sharp-fathom-dashboard.chart.datasets'))) {
            $this->addGraphDataSet(
                'daily_analytics',
                SharpGraphWidgetDataSet::make(
                    $stats
                        ->mapWithKeys(fn($day) => [Carbon::parse($day['date'])->format('Y-m-d') => (int) $day['pageviews'] ?? 0])
                        ->filter()
                )->setLabel(__("Page views"))->setColor('blue')
            );
        }

        if (in_array('unique_visitors', config()->array('sharp-fathom-dashboard.chart.datasets'))) {
            $this->addGraphDataSet(
                'daily_analytics',
                SharpGraphWidgetDataSet::make(
                    $stats
                        ->filter()
                        ->mapWithKeys(fn($day) => [Carbon::parse($day['date'])->format('d/m/Y') => (int) $day['visits'] ?? 0])
                )->setLabel(__("Unique visitors"))->setColor('green')
            );
        }

        if (in_array('unique_pageviews', config()->array('sharp-fathom-dashboard.chart.datasets'))) {
            $this ->addGraphDataSet(
                'daily_analytics',
                SharpGraphWidgetDataSet::make(
                    $stats
                        ->filter()
                        ->mapWithKeys(fn($day) => [Carbon::parse($day['date'])->format('d/m/Y') => (int) $day['uniques'] ?? 0])
                )->setLabel(__("Unique pages viewed"))->setColor('purple')
            );
        }

        $this
            ->setPanelData(
                'most_viewed_pages',
                [
                    'pages' => $mostViewedPages,
                    'total' => $mostViewedPages->sum('pageviews')
                ]
            )
            ->setPanelData(
                'top_referrers',
                [
                    'referrers' => $topReferrers,
                    'total' => $topReferrers->sum('pageviews')
                ]
            );
    }
}
