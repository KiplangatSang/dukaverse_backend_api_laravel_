<?php

namespace App\Listeners;

use App\Events\SalesUpdate;
use App\Notifications\SalesNotification;

class SalesUpdateListener
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
     * @param  \App\Events\SalesUpdate  $event
     * @return void
     */
    public function handle(SalesUpdate $event)
    {
        //

        try {
            $event->user->notify(new SalesNotification($event->sale));
        } catch (\Exception $ex) {
            info($ex->getMessage());
            info($ex->getTraceAsString());

        }
        info("this sales item is updated " . $event->sale->id);
    }
}
