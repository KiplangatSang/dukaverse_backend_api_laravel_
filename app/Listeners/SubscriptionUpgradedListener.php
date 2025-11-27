<?php

namespace App\Listeners;

use App\Events\SubscriptionUpgraded;
use App\Notifications\SubscriptionUpgradedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SubscriptionUpgradedListener implements ShouldQueue
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
     * @param  \App\Events\SubscriptionUpgraded  $event
     * @return void
     */
    public function handle(SubscriptionUpgraded $event)
    {
        try {
            $event->subscription->user->notify(new SubscriptionUpgradedNotification(
                $event->subscription,
                $event->oldTier,
                $event->newTier,
                $event->upgradeDate,
                $event->proratedAmount
            ));
        } catch (\Exception $ex) {
            info($ex->getMessage());
            info($ex->getTraceAsString());
        }

        info("Subscription upgraded: " . $event->subscription->id . " from {$event->oldTier->name} to {$event->newTier->name}");
    }
}
