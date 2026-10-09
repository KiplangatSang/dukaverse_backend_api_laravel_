<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DeliveryStatusChanged extends Notification implements ShouldQueue
{
    use Queueable;

    protected $delivery;
    protected $oldStatus;
    protected $newStatus;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($delivery, $oldStatus, $newStatus)
    {
        $this->delivery = $delivery;
        $this->oldStatus = $oldStatus;
        $this->newStatus = $newStatus;
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
        $delivery = $this->delivery;
        $oldStatus = $this->oldStatus;
        $newStatus = $this->newStatus;

        return (new MailMessage)
            ->subject('Delivery Status Update')
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('Your delivery status has been updated.')
            ->line('Order ID: ' . $delivery->order->id ?? 'N/A')
            ->line('Previous Status: ' . ucfirst($oldStatus))
            ->line('New Status: ' . ucfirst($newStatus))
            ->line('Thank you for using our delivery service!')
            ->salutation('Best regards, Dukaverse Team');
    }

    /**
     * Get the database representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toDatabase($notifiable)
    {
        return [
            'delivery_id' => $this->delivery->id,
            'order_id' => $this->delivery->order->id ?? null,
            'old_status' => $this->oldStatus,
            'new_status' => $this->newStatus,
            'message' => 'Your delivery status has changed from ' . ucfirst($this->oldStatus) . ' to ' . ucfirst($this->newStatus),
            'link' => '/deliveries/' . $this->delivery->id,
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
            'delivery_id' => $this->delivery->id,
            'old_status' => $this->oldStatus,
            'new_status' => $this->newStatus,
        ];
    }
}
