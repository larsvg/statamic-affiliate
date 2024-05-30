<?php

namespace Larsvg\StatamicAffiliate;

use Larsvg\StatamicAffiliate\Commands\MakeImporter;
use Larsvg\StatamicAffiliate\Commands\PublishAffiliateStubs;
use Statamic\Facades\Collection;
use Statamic\Providers\AddonServiceProvider;
use Statamic\Statamic;

class ServiceProvider extends AddonServiceProvider
{
    public function bootAddon()
    {
        Statamic::afterInstalled(function ($command) {
            $command->call('affiliate:publish-stubs');
        });
    }

    public function boot()
    {
        //https://statamic.dev/collections#using-fields-from-related-entries
        Collection::computed('products', 'category_url', function ($entry, $value) {
            return $entry->belongs_to?->url();
        });

        if ($this->app->runningInConsole()) {
            $this->commands([
                PublishAffiliateStubs::class,
                MakeImporter::class,
            ]);
        }
    }
}
