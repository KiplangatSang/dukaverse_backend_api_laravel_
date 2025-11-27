<?php

namespace App\Listeners;

use App\Events\RetailProfileUpdate;
use App\Notifications\RetailProfileUpdateNotification;

class RetailProfileUpdateListener
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
     * @param  \App\Events\RetailProfileUpdate  $event
     * @return void
     */
    public function handle(RetailProfileUpdate $event)
    {
        //

        info("this retail profile is updated " . $event->profile);

        try {
            $event->user->notify(new RetailProfileUpdateNotification($event->profile));
        } catch (\Exception $ex) {
            info($ex->getMessage());
            info($ex->getTraceAsString());

        }
    }
}
