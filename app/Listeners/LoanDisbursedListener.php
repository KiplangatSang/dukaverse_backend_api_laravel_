<?php

namespace App\Listeners;

use App\Events\LoanDisbursed;
use App\Notifications\LoanDisbursedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class LoanDisbursedListener
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
     * @param  \App\Events\LoanDisbursed  $event
     * @return void
     */
    public function handle(LoanDisbursed $event)
    {
        //


        try {
            $event->user->notify(new LoanDisbursedNotification($event->loan));
        } catch (\Exception $ex) {
            info($ex->getMessage());
            info($ex->getTraceAsString());
        }info("this loan is updated " . $event->loan->id);

    }
}
