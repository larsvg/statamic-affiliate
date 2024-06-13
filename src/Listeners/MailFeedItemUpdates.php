<?php

namespace Larsvg\StatamicAffiliate\Listeners;

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
        $recipients = config('affiliate.mail_feed_item_updates_to');
        $recipients = explode(',', $recipients);

        if (empty($recipients)) {
            return;
        }

        if (! $this->hasItemsToReportOn($event)) {
            return;
        }

        Mail::to($recipients)
            ->send(
                new NewFeedItemsMailable($event->feedName, $event->created, $event->updated, $event->deleted)
            );
    }

    private function hasItemsToReportOn(object $event): bool
    {
        if (count($event->created) > 0 && config('affiliate.mail_on_new_items')) {
            return true;
        }

        if (count($event->updated) > 0 && config('affiliate.mail_on_updated_item')) {
            return true;
        }

        if (count($event->deleted) > 0 && config('affiliate.mail_on_deleted_item')) {
            return true;
        }

        return false;
    }
}
