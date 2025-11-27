<?php

namespace App\Notifications;

use App\Models\Subscription;
use App\Models\Tier;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Carbon\Carbon;

class SubscriptionUpgradedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $subscription;
    public $oldTier;
    public $newTier;
    public $upgradeDate;
    public $proratedAmount;

    /**
     * Create a new notification instance.
     */
    public function __construct(Subscription $subscription, Tier $oldTier, Tier $newTier, Carbon $upgradeDate, ?float $proratedAmount = null)
    {
        $this->subscription = $subscription;
        $this->oldTier = $oldTier;
        $this->newTier = $newTier;
        $this->upgradeDate = $upgradeDate;
        $this->proratedAmount = $proratedAmount;
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
        $upgradeDate = $this->upgradeDate->format('M j, Y');
        $amountText = $this->proratedAmount
            ? "Prorated amount charged: $" . number_format($this->proratedAmount, 2)
            : "No additional charge for this upgrade period.";

        return (new MailMessage)
            ->subject("Subscription Upgraded to {$this->newTier->name}")
            ->greeting("Hi {$notifiable->name},")
            ->line("Congratulations! Your subscription has been successfully upgraded.")
            ->line("From: {$this->oldTier->name} (${$this->oldTier->price})")
            ->line("To: {$this->newTier->name} (${$this->newTier->price})")
            ->line("Upgrade Date: {$upgradeDate}")
            ->line($amountText)
            ->line("You now have access to all {$this->newTier->name} features and benefits.")
            ->action('View Subscription Details', url('/subscriptions/' . $this->subscription->id))
            ->line('Thank you for upgrading with us!')
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
            'type' => 'subscription_upgraded',
            'subscription_id' => $this->subscription->id,
            'old_tier_name' => $this->oldTier->name,
            'new_tier_name' => $this->newTier->name,
            'old_tier_price' => $this->oldTier->price,
            'new_tier_price' => $this->newTier->price,
            'upgrade_date' => $this->upgradeDate->toISOString(),
            'prorated_amount' => $this->proratedAmount,
            'message' => "Your subscription has been upgraded from {$this->oldTier->name} to {$this->newTier->name}.",
        ];
    }
}
