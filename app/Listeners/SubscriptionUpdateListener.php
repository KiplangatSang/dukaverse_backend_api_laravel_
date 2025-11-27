<?php

namespace App\Listeners;

use App\Events\SubscriptionUpdate;
use App\Notifications\SubscriptionsAvailableNotification;

class SubscriptionUpdateListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  \App\Events\SubscriptionUpdate  $event
     * @return void
     */
    public function handle(SubscriptionUpdate $event)
    {
        //
        info("this subcription is updated " . $event->subscription->id);

        try {
            $event->user->notify(new SubscriptionsAvailableNotification($event->subscription));
        } catch (\Exception $ex) {
            info($ex->getMessage());
            info($ex->getTraceAsString());
        }
    }
}
