<?php

namespace App\Listeners;

use App\Events\SubscriptionCancelled;
use App\Notifications\SubscriptionCancelledNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SubscriptionCancelledListener implements ShouldQueue
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
    public function handle(SubscriptionCancelled $event): void
    {
        // Notify the user about the subscription cancellation
        if ($event->subscription->user) {
            $event->subscription->user->notify(new SubscriptionCancelledNotification($event->subscription, $event->reason));
        }
    }
}
