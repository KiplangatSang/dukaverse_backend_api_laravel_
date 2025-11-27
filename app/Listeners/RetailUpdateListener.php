<?php

namespace App\Listeners;

use App\Events\RetailUpdate;
use App\Notifications\RetailUpdateNotification;

class RetailUpdateListener
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
     * @param  \App\Events\RetailUpdate  $event
     * @return void
     */
    public function handle(RetailUpdate $event)
    {
        //
        info("this retail  is updated " . $event->retail->id);

        try {
            $event->user->notify(new RetailUpdateNotification($event->retail));
        } catch (\Exception $ex) {
            info($ex->getMessage());
            info($ex->getTraceAsString());

        }
    }
}
