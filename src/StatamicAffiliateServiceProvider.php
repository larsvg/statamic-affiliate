<?php

namespace Larsvg\StatamicAffiliate;

use Spatie\LaravelPackageTools\Package;
use Statamic\Providers\AddonServiceProvider;

class StatamicAffiliateServiceProvider extends AddonServiceProvider
{
    public function configurePackage(Package $package): void
    {
        dd('init');
    }
}
