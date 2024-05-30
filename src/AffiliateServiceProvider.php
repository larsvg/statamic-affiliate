<?php

namespace Larsvg\StatamicAffiliate;

use Illuminate\Support\ServiceProvider;
use Statamic\Facades\Collection;

class AffiliateServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //https://statamic.dev/collections#using-fields-from-related-entries
        Collection::computed('products', 'category_url', function ($entry, $value) {
            return $entry->belongs_to?->url();
        });
    }
}
