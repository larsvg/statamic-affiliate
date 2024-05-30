<?php

namespace Larsvg\StatamicAffiliate;

use Larsvg\StatamicAffiliate\Commands\MakeImporter;
use Larsvg\StatamicAffiliate\Commands\PublishAffiliateStubs;
use Statamic\Providers\AddonServiceProvider;
use Statamic\Statamic;

class ServiceProvider extends AddonServiceProvider
{
    public function bootAddon()
    {
        Statamic::afterInstalled(function ($command) {
            $command->call('affiliate:publish-stubs');
        });

        //https://statamic.dev/collections#using-fields-from-related-entries
        Collection::computed('products', 'category_url', function ($entry, $value) {
            return $entry->belongs_to?->url();
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
