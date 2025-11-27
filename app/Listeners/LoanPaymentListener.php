<?php

namespace App\Listeners;

use App\Events\LoanPayment;
use App\Notifications\LoanPaymentNotification;

class LoanPaymentListener
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
     * @param  \App\Events\LoanPayment  $event
     * @return void
     */
    public function handle(LoanPayment $event)
    {
        //

        try {
            $event->user->notify(new LoanPaymentNotification($event->loan));
        } catch (\Exception $ex) {
            info($ex->getMessage());
            info($ex->getTraceAsString());
        }info("this is a LoanPaymentNotification " . $event->loan);
    }
}
