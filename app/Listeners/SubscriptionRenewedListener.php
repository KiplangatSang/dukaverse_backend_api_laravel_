<?php

namespace App\Listeners;

use App\Events\SubscriptionRenewed;
use App\Notifications\SubscriptionRenewedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SubscriptionRenewedListener implements ShouldQueue
{
    use InteractsWithQueue;

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
    public function handle(SubscriptionRenewed $event): void
    {
        // Notify the user about the subscription renewal
        $event->subscription->ownerable->notify(new SubscriptionRenewedNotification($event->subscription, $event->renewalDate, $event->nextBillingDate));
    }
}
