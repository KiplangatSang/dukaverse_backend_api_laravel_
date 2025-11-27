<?php

namespace App\Listeners;

use App\Events\RequiredStockUpdate;
use App\Notifications\RequiredStockNotification;

class RequiredStockUpdateListener
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
     * @param  \App\Events\RequiredStockUpdate  $event
     * @return void
     */
    public function handle(RequiredStockUpdate $event)
    {
        //
        try {
            $event->user->notify(new RequiredStockNotification($event->requiredItem));
        } catch (\Exception $ex) {
            info($ex->getMessage());
            info($ex->getTraceAsString());
        }
    }
}
