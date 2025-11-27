<?php

namespace App\Listeners;

use App\Events\SubscriptionReactivated;
use App\Notifications\SubscriptionUpgradedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SubscriptionReactivatedListener implements ShouldQueue
{
    use InteractsWithQueue;

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
     * @param  \App\Events\SubscriptionReactivated  $event
     * @return void
     */
    public function handle(SubscriptionReactivated $event)
    {
        try {
            $event->subscription->user->notify(new SubscriptionUpgradedNotification(
                $event->subscription,
                $event->subscription->tier, // old tier (same as new for reactivation)
                $event->subscription->tier, // new tier (same as old for reactivation)
                now(),
                0 // no prorated amount for reactivation
            ));
        } catch (\Exception $ex) {
            info($ex->getMessage());
            info($ex->getTraceAsString());
        }

        info("Subscription reactivated: " . $event->subscription->id);
    }
}
