<?php

namespace Larsvg\StatamicAffiliate\Listeners;

use App\Mail\MailsMailable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Larsvg\StatamicAffiliate\Mail\NewFeedItemsMailable;

class MailFeedItemUpdates
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

        $recipients = config('affiliate.mail_feed_item_updates_to');
        $recipients = explode(',', $recipients);

        if (empty($recipients)) {
            return;
        }

        if (!$this->hasItemsToReportOn($event)) {
            return;
        }



        Mail::to($recipients)
            ->send(
                new NewFeedItemsMailable($event->created)
            );
    }

    private function hasItemsToReportOn(): bool
    {




        return false;
    }

}
