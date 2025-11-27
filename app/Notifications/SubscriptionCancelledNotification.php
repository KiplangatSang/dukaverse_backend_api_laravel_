<?php

namespace App\Notifications;

use App\Models\Subscription;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Carbon\Carbon;

class SubscriptionCancelledNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $subscription;
    public $cancellationDate;
    public $effectiveDate;
    public $reason;

    /**
     * Create a new notification instance.
     */
    public function __construct(Subscription $subscription, Carbon $cancellationDate, ?Carbon $effectiveDate = null, ?string $reason = null)
    {
        $this->subscription = $subscription;
        $this->cancellationDate = $cancellationDate;
        $this->effectiveDate = $effectiveDate ?? $cancellationDate->copy();
        $this->reason = $reason;
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
        $cancelDate = $this->cancellationDate->format('M j, Y');
        $effectiveDate = $this->effectiveDate->format('M j, Y');

        $subject = "Subscription Cancellation Confirmation";
        $message = (new MailMessage)
            ->subject($subject)
            ->greeting("Hi {$notifiable->name},")
            ->line("Your {$tier->name} subscription has been cancelled.")
            ->line("Cancellation Date: {$cancelDate}")
            ->line("Access until: {$effectiveDate}");

        if ($this->reason) {
            $message->line("Reason: {$this->reason}");
        }

        $message->line("We're sorry to see you go. You can reactivate your subscription at any time.")
            ->action('Reactivate Subscription', url('/subscriptions/reactivate'))
            ->line('If you have any feedback, please let us know.')
            ->salutation('Best regards, Dukaverse Team');

        return $message;
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'subscription_cancelled',
            'subscription_id' => $this->subscription->id,
            'tier_name' => $this->subscription->tier->name,
            'cancellation_date' => $this->cancellationDate->toISOString(),
            'effective_date' => $this->effectiveDate->toISOString(),
            'reason' => $this->reason,
            'message' => "Your {$this->subscription->tier->name} subscription has been cancelled.",
        ];
    }
}
