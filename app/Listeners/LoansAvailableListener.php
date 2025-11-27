<?php

namespace App\Listeners;

use App\Events\LoansAvailable;
use App\Notifications\LoansAvailableNotification;

class LoansAvailableListener
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
     * @param  \App\Events\LoansAvailable  $event
     * @return void
     */
    public function handle(LoansAvailable $event)
    {
        //

        try {
            $event->user->notify(new LoansAvailableNotification($event->loan));
        } catch (\Exception $ex) {
            info($ex->getMessage());
            info($ex->getTraceAsString());
        }info("this  is a LoansAvailableNotification " . $event->loan->id);
    }
}
