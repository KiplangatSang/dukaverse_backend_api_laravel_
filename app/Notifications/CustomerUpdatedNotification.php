<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CustomerUpdatedNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    protected $customer = null;
    public function __construct($customer)
    {
        //
        $this->customer = $customer;
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

        $customerdata = $this->customerdata;
        return (new MailMessage)
            ->line('Helloo ' . $notifiable->username)
            ->action('Notification Action', url('/'))
            ->view('emails.customerupdate', compact('customerdata'))
            ->line('Thank you for being  a DukaVerse Member!');
    }


    public function toDatabase($notifiable)
    {
        return  [
            'link' =>  "/client/customers/show/" . $this->customerdata->id,
            'message' => "Customer data updated",
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
