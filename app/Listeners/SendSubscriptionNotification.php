<?php

namespace App\Listeners;

use App\Events\SubscriptionCreated;
use App\Notifications\SubscriptionsAvailableNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendSubscriptionNotification implements ShouldQueue
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
    public function handle(SubscriptionCreated $event): void
    {
        // Notify the user about the new subscription
        if ($event->subscription->user) {
            $event->subscription->user->notify(new SubscriptionsAvailableNotification($event->subscription));
        }
    }
}
