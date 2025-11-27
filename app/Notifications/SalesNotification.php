<?php
namespace App\Notifications;

use App\Mail\SalesMail;
use App\Repositories\SalesRepository;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Mail;

class SalesNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */

    public $account;
    public $salesdata;

    public function __construct($account)
    {
        //
        $this->account   = $account;
        $salesrepo       = new SalesRepository($account);
        $this->salesdata = $salesrepo->salesMailData();

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

        return (new SalesMail($this->salesdata, $notifiable))
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
            'sales_data' => $this->salesdata, // Store the sales data you want in the database
            'created_at' => now(),
        ];
    }

    public function databaseType(object $notifiable): string
    {
        return 'sales_mail';
    }
}
