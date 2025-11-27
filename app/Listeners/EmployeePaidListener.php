<?php

namespace App\Listeners;

use App\Events\EmployeePaid;
use App\Notifications\EmployeeUpdatedNotification;

class EmployeePaidListener
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
     * @param  \App\Events\EmployeePaid  $event
     * @return void
     */
    public function handle(EmployeePaid $event)
    {
        //

        try {
            $event->user->notify(new EmployeeUpdatedNotification($event->employee));
        } catch (\Exception $ex) {
            info($ex->getMessage());
            info($ex->getTraceAsString());
        }
        info("this employee is updated " . $event->employee->id);

    }
}
