<?php

namespace App\Listeners;

use App\Events\MarketUpdate;
use App\Notifications\MarketUpdateNotification;

class MarketUpdateListener
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
     * @param  \App\Events\MarketUpdate  $event
     * @return void
     */
    public function handle(MarketUpdate $event)
    {
        //

        try {
            $event->user->notify(new MarketUpdateNotification($event->supply));
        } catch (\Exception $ex) {
            info($ex->getMessage());
            info($ex->getTraceAsString());
        }info("this marketdata is updated " . $event->supply->id);

    }
}
