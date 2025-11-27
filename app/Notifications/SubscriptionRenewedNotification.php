<?php

namespace App\Notifications;

use App\Models\Subscription;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Carbon\Carbon;

class SubscriptionRenewedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $subscription;
    public $renewalDate;
    public $nextBillingDate;

    /**
     * Create a new notification instance.
     */
    public function __construct(Subscription $subscription, Carbon $renewalDate, ?Carbon $nextBillingDate = null)
    {
        $this->subscription = $subscription;
        $this->renewalDate = $renewalDate;
        $this->nextBillingDate = $nextBillingDate ?? $this->calculateNextBillingDate();
    }

    /**
     * Calculate next billing date based on tier's billing duration
     */
    private function calculateNextBillingDate(): Carbon
    {
        $tier = $this->subscription->tier;
        $interval = $tier->getBillingIntervalDays();

        return $this->renewalDate->copy()->addDays($interval);
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
        $renewalDate = $this->renewalDate->format('M j, Y');
        $nextBilling = $this->nextBillingDate->format('M j, Y');

        return (new MailMessage)
            ->subject("Your {$tier->name} Subscription Has Been Renewed")
            ->greeting("Hi {$notifiable->name},")
            ->line("Great news! Your {$tier->name} subscription has been successfully renewed.")
            ->line("Renewal Date: {$renewalDate}")
            ->line("Next Billing Date: {$nextBilling}")
            ->line("Amount Charged: $" . number_format($this->subscription->discounted_price ?? $tier->price, 2))
            ->action('View Subscription Details', url('/subscriptions/' . $this->subscription->id))
            ->line('Thank you for continuing with our service!')
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
            'type' => 'subscription_renewed',
            'subscription_id' => $this->subscription->id,
            'tier_name' => $this->subscription->tier->name,
            'renewal_date' => $this->renewalDate->toISOString(),
            'next_billing_date' => $this->nextBillingDate->toISOString(),
            'amount' => $this->subscription->discounted_price ?? $this->subscription->tier->price,
            'message' => "Your {$this->subscription->tier->name} subscription has been renewed successfully.",
        ];
    }
}
