<?php

namespace Larsvg\StatamicAffiliate\Listeners;

use Illuminate\Support\Facades\Log;

class LogNewFeedItems
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(object $event): void
    {
        foreach ($event->created as $item) {
            Log::channel('affiliate')->info('Product added: '.$item->title.'. Url: '.config('app.url').'/cp/collections/products/entries/'.$item->id);
        }
    }
}
