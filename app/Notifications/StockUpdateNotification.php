<?php

namespace App\Notifications;

use App\Mail\StockUpdatedMail;
use App\Repositories\StockRepository;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class StockUpdateNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */

    public $account;
    public $stockdata;

    public function __construct($account)
    {
        //

        $this->account = $account;
        $stockrepo = new StockRepository($account);
        $this->stockdata = $stockrepo->stockMailData();

    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new StockUpdatedMail($this->account, $this->stockdata))
            ->to($notifiable->email)
            ->subject('Your Recent Sales Transactions');

    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toDatabase($notifiable)
    {
        return [
            'stock_data' => $this->stockdata, // Store the sales data you want in the database
            'created_at' => now(),
        ];
    }

    public function databaseType(object $notifiable): string
    {
        return 'stock_mail';
    }
}
