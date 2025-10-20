<?php

namespace Code16\SharpFathomDashboard;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Code16\SharpFathomDashboard\Commands\SharpFathomDashboardCommand;

class SharpFathomDashboardServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('sharp-fathom-dashboard')
            ->hasConfigFile()
            ->hasTranslations();
    }
}
