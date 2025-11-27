<?php

namespace App\Listeners;

use App\Events\CustomerUpdated;
use App\Notifications\CustomerUpdatedNotification;

class CustomerUpdatedListener
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
     * @param  object  $event
     * @return void
     */
    public function handle(CustomerUpdated $event)
    {
        //

        try {
            $event->user->notify(new CustomerUpdatedNotification($event->customer));
        } catch (\Exception $ex) {
            info($ex->getMessage());
            info($ex->getTraceAsString());

        }
        info("this customer is updated " . $event->order->id);
    }
}
