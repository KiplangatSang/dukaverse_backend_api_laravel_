<?php

namespace App\Listeners;

use App\Events\SubscriptionExpired;
use App\Notifications\SubscriptionExpiredNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SubscriptionExpiredListener implements ShouldQueue
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
    public function handle(SubscriptionExpired $event): void
    {
        // Notify the user about the subscription expiration
        $event->subscription->ownerable->notify(new SubscriptionExpiredNotification($event->subscription));
    }
}
