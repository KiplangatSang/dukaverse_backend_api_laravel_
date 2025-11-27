<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LoanProcessedNotification extends Notification
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
            ->line('Congratulations ' . $notifiable->username)
            ->line('Your loan request has been successfully processed')
            ->action('Notification Action', url('/'))
            ->view('emails.loanprocessed', compact('loansdata'))
            ->line('Thank you for being  a DukaVerse Member!');
    }

    public function toDatabase($notifiable)
    {
        return  [
            'link' =>  "/client/loans/show/" . $this->loansdata->id,
            'message' => "Congracts!! Loan request has been processed successfully",
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
