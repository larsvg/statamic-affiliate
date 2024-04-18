<?php

namespace Larsvg\StatamicAffiliate;

use Larsvg\StatamicAffiliate\Commands\PublishAffiliateStubs;
use Statamic\Providers\AddonServiceProvider;
use Statamic\Statamic;

class ServiceProvider extends AddonServiceProvider
{
    protected $commands = [
        PublishAffiliateStubs::class,
    ];

    public function bootAddon()
    {
//        Statamic::afterInstalled(function ($command) {
//            $command->call('affiliate:publish-stubs');
//        });

        $this->registerPublishableViews();
    }

    protected function registerPublishableViews()
    {
        $this->publishes([
            __DIR__ . '/../resources/views' => resource_path('views/vendor/statamic-affiliate'),
        ], 'statamic-affiliate-views');
    }
}
