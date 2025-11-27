<?php

namespace App\Notifications;

use App\Models\Subscription;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Carbon\Carbon;

class PaymentFailedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $subscription;
    public $attemptCount;
    public $nextRetryDate;

    /**
     * Create a new notification instance.
     */
    public function __construct(Subscription $subscription, int $attemptCount = 1, ?Carbon $nextRetryDate = null)
    {
        $this->subscription = $subscription;
        $this->attemptCount = $attemptCount;
        $this->nextRetryDate = $nextRetryDate ?? Carbon::now()->addDays(3);
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $tier = $this->subscription->tier;
        $nextRetry = $this->nextRetryDate->format('M j, Y');

        return (new MailMessage)
            ->subject("Payment Failed for {$tier->name} Subscription")
            ->greeting("Hi {$notifiable->name},")
            ->line("We were unable to process the payment for your {$tier->name} subscription.")
            ->line("Attempt: {$this->attemptCount}")
            ->line("Next retry date: {$nextRetry}")
            ->line("Please update your payment method to avoid service interruption.")
            ->action('Update Payment Method', url('/subscriptions/payment-method'))
            ->line('If you have any questions, please contact our support team.')
            ->salutation('Best regards, Dukaverse Team');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'payment_failed',
            'subscription_id' => $this->subscription->id,
            'tier_name' => $this->subscription->tier->name,
            'attempt_count' => $this->attemptCount,
            'next_retry_date' => $this->nextRetryDate->toISOString(),
            'message' => "Payment failed for your {$this->subscription->tier->name} subscription. Attempt {$this->attemptCount}.",
        ];
    }
}
