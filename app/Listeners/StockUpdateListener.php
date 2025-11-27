<?php

namespace App\Listeners;

use App\Events\StockUpdate;
use App\Notifications\StockUpdateNotification;

class StockUpdateListener
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
     * @param  \App\Events\StockUpdate  $event
     * @return void
     */
    public function handle(StockUpdate $event)
    {
        //

        try {
            $event->user->notify(new StockUpdateNotification($event->stock));
        } catch (\Exception $ex) {
            info($ex->getMessage());
            info($ex->getTraceAsString());

        }
        info("this stock item is updated " . $event->stock->id);
    }
}
