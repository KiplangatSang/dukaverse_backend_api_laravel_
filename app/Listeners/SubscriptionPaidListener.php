<?php

namespace App\Listeners;

use App\Events\SubscriptionPaid;
use App\Notifications\SubscriptionUpdateNotification;
use App\Subscriptions\Subscription;

class SubscriptionPaidListener
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
     * @param  \App\Events\SubscriptionPaid  $event
     * @return void
     */
    public function handle(SubscriptionPaid $event)
    {
        //

        try {
            $event->user->notify(new SubscriptionUpdateNotification($event->subscription));
        } catch (\Exception $ex) {
            info($ex->getMessage());
            info($ex->getTraceAsString());

        }info("this subscription item is paid " . $event->subscription->id);

    }
}
