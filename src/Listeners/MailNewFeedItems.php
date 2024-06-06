<?php

namespace Larsvg\StatamicAffiliate\Listeners;

use App\Mail\MailsMailable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Larsvg\StatamicAffiliate\Mail\NewFeedItemsMailable;

class MailNewFeedItems
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
        if (empty($event->created)) {
            return;
        }

        $recipients = config('affiliate.mail_new_feed_items_to');
        $recipients = explode(',', $recipients);

        Mail::to($recipients)
            ->send(
                new NewFeedItemsMailable($event->created)
            );
    }
}
