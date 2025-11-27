<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CustomerInvoiceNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public $customerdata = null;
    public function __construct($customerdata)
    {
        //
        $this->customerdata = $customerdata;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {

        $credits = $this->customerdata;
        $customerdata['credits'] = $credits;
        // dd($customerdata);
        return (new MailMessage)
            ->view('emails.customerinvoice', compact('customerdata'))
            ->line('Thank you for being  a DukaVerse Member!');
    }

    public function toDatabase($notifiable)
    {
        return  [
            'link' =>  "/client/customers/show/" . $this->customerdata->customers->id,
            'message' => "Customer " . $this->customerdata->customers->name . " has been invoiced ",
            'data' => json_encode($this->customerdata),
        ];
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
