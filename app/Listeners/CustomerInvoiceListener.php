<?php
namespace App\Listeners;

use App\Events\CustomerInvoice;
use App\Models\User;
use App\Notifications\CustomerInvoiceNotification;
use App\Repositories\CustomersRepository;

class CustomerInvoiceListener extends BaseListener
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
     * @param  object  $event
     * @return void
     */
    public function handle(CustomerInvoice $event)
    {
        //

        $custRepo = new CustomersRepository($this->retail());
        $custdata = $custRepo->getCustomersCredit($event->customerCredit->id);
        $user     = User::where('id', Auth::id())->first();
        $user->notify(new CustomerInvoiceNotification($custdata));
        info("This customer is updated " . $event->customer->name);

    }
}
