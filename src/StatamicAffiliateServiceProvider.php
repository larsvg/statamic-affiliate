<?php

namespace Larsvg\StatamicAffiliate;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class StatamicAffiliateServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('statamic-affiliate')
            ->hasConfigFile()
            ->hasViews()
            ->hasMigration('create_statamic-affiliate_table');
    }
}
