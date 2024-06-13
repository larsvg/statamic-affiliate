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

    private function hasItemsToReportOn(object $event): bool
    {
        if (!empty($event->created) && config('affiliate.mail_on_new_items')) {
            return true;
        }

        if (!empty($event->updated) && config('affiliate.mail_on_updated_item')) {
            return true;
        }

        if (!empty($event->deleted) && config('affiliate.mail_on_deleted_item')) {
            return true;
        }

        return false;
    }

}
