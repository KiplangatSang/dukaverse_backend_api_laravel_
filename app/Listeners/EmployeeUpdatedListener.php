<?php

namespace App\Listeners;

use App\Events\EmployeeUpdated;
use App\Notifications\EmployeeUpdatedNotification;

class EmployeeUpdatedListener
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
     * @param  \App\Events\=EmployeeUpdated  $event
     * @return void
     */
    public function handle(EmployeeUpdated $event)
    {
        //
        //
        $event->user->notify(new EmployeeUpdatedNotification($event->employee));

        try {
        } catch (\Exception $ex) {
            info($ex->getMessage());
            info($ex->getTraceAsString());
        }info("this employee is updated " . $event->employee->id);

    }
}
