<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LoansAvailableNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    protected $loansdata;
    public function __construct($loansdata)
    {
        //
        $this->loansdata = $loansdata;
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
        $loansdata = $this->loansdata;
        return (new MailMessage)
            ->line('Hello ' . $notifiable->username)
            ->line('Check out our loans well suited for you. Get the finance to expand')
            ->action('Notification Action', url('/'))
            ->view('emails.loansavailable', compact('loansdata'))
            ->line('Thank you for being  a DukaVerse Member!');
    }

    public function toDatabase($notifiable)
    {
        return  [
            'link' =>  "/client/loans/show/" . $this->loansdata->id,
            'message' => "Hello $notifiable->username checkout loans available for you.",
            'data' => json_encode($this->loansdata),
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
