<?php

namespace Larsvg\StatamicAffiliate;

use Larsvg\StatamicAffiliate\Commands\MakeImporter;
use Larsvg\StatamicAffiliate\Commands\PublishAffiliateStubs;
use Larsvg\StatamicAffiliate\Events\FeedImported;
use Larsvg\StatamicAffiliate\Listeners\LogNewFeedItems;
use Statamic\Providers\AddonServiceProvider;
use Statamic\Statamic;

class ServiceProvider extends AddonServiceProvider
{
    protected $listen = [
        FeedImported::class => [
            LogNewFeedItems::class,
        ]
    ];


    public function bootAddon()
    {
        Statamic::afterInstalled(function ($command) {
            $command->call('affiliate:publish-stubs');
        });

    }

    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                PublishAffiliateStubs::class,
                MakeImporter::class,
            ]);
        }
    }
}
