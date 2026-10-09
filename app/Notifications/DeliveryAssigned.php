<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DeliveryAssigned extends Notification implements ShouldQueue
{
    use Queueable;

    protected $delivery;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($delivery)
    {
        $this->delivery = $delivery;
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

        return (new MailMessage)
            ->subject('New Delivery Assignment')
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('You have been assigned a new delivery.')
            ->line('Order ID: ' . $delivery->order->id ?? 'N/A')
            ->line('Pickup Location: ' . ($delivery->pickup_location['address'] ?? 'N/A'))
            ->line('Delivery Location: ' . ($delivery->delivery_location['address'] ?? 'N/A'))
            ->line('Estimated Distance: ' . ($delivery->estimated_distance ?? 'N/A') . ' km')
            ->line('Fee: KES ' . $delivery->fee)
            ->action('View Delivery Details', url('/deliveries/' . $delivery->id))
            ->line('Please confirm your acceptance as soon as possible.')
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
            'pickup_location' => $this->delivery->pickup_location,
            'delivery_location' => $this->delivery->delivery_location,
            'fee' => $this->delivery->fee,
            'message' => 'You have been assigned a new delivery. Please check the details and confirm.',
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
            'order_id' => $this->delivery->order->id ?? null,
        ];
    }
}
