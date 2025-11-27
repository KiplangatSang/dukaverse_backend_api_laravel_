<?php

namespace App\Notifications;

use App\Repositories\TransactionsRepository;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PaymentMadeNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    protected $transaction = null;

    protected $message = null;
    public function __construct($transaction,$message)
    {
        //
        $this->message =$message;
        $this->transaction = $transaction;
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
        $transaction = $this->transaction;
        $transactiondata['transaction'] =  $transaction;
        $transactiondata['message'] = $this->message;
        return (new MailMessage)
            ->view('emails.paymentdone', compact('transactiondata'));
    }
    public function toDatabase($notifiable)
    {
        return  [
            'link' =>  "/client/transactions/show/" . $this->transaction->id,
            'message' => "$this->message",
            'data' => json_encode($this->transaction),
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
