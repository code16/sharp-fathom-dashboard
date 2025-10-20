<?php

namespace Code16\SharpFathomDashboard\Sharp;

use Code16\Sharp\Dashboard\Layout\DashboardLayout;
use Code16\Sharp\Dashboard\Layout\DashboardLayoutRow;
use Code16\Sharp\Dashboard\Layout\DashboardLayoutSection;
use Code16\Sharp\Dashboard\SharpDashboard;
use Code16\Sharp\Dashboard\Widgets\SharpFigureWidget;
use Code16\Sharp\Dashboard\Widgets\WidgetsContainer;

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
            );
    }

    protected function buildDashboardLayout(DashboardLayout $dashboardLayout): void
    {
        $dashboardLayout
            ->addSection('', function (DashboardLayoutSection $section) {
                $section
                    ->addRow(function (DashboardLayoutRow $row) {
                        $row
                            ->addWidget(4, 'unique_visitors')
                            ->addWidget(4, 'pageviews')
                            ->addWidget(4, 'avg_time_on_site');
                    });
            });
    }

    protected function buildWidgetsData(): void
    {
        $this
            ->setFigureData('unique_visitors', 6)
            ->setFigureData('pageviews', 12)
            ->setFigureData('avg_time_on_site', "1m30s");
    }
}
