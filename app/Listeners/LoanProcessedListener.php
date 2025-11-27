<?php

namespace App\Listeners;

use App\Events\LoanProcessed;
use App\Notifications\LoanProcessedNotification;

class LoanProcessedListener
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
     * @param  \App\Events\LoanProcessed  $event
     * @return void
     */
    public function handle(LoanProcessed $event)
    {
        //

        try {
            $event->user->notify(new LoanProcessedNotification($event->loan));
        } catch (\Exception $ex) {
            info($ex->getMessage());
            info($ex->getTraceAsString());
        }info("this is a LoanProcessedNotification " . $event->loan->id);

    }
}
