<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EmployeeUpdatedNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    protected $empdata = null;
    public function __construct($empdata)
    {
        //
        $this->empdata = $empdata;
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

        $empdata = $this->empdata;
        return (new MailMessage)
            ->line('Helloo ' . $notifiable->username)
            ->action('Notification Action', url('/'))
            ->view('employeeupdate', compact('empdata'))
            ->line('Thank you for being  a DukaVerse Member!');
    }
    public function toDatabase($notifiable)
    {
        return  [
            'link' =>  "/client/employees/show/" . $this->empdata->id,
            'message' => "Employees data updated",
            'data' => json_encode($this->empdata),
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
