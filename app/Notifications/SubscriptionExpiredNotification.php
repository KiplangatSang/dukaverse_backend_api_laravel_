<?php

namespace App\Notifications;

use App\Models\Subscription;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Carbon\Carbon;

class SubscriptionExpiredNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $subscription;
    public $gracePeriodDays;

    /**
     * Create a new notification instance.
     */
    public function __construct(Subscription $subscription, int $gracePeriodDays = 7)
    {
        $this->subscription = $subscription;
        $this->gracePeriodDays = $gracePeriodDays;
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
        $expiredDate = Carbon::parse($this->subscription->expires_at)->format('M j, Y');
        $graceEndDate = Carbon::parse($this->subscription->expires_at)->addDays($this->gracePeriodDays)->format('M j, Y');

        return (new MailMessage)
            ->subject("Your {$tier->name} Subscription Has Expired")
            ->greeting("Hi {$notifiable->name},")
            ->line("Your {$tier->name} subscription expired on {$expiredDate}.")
            ->line("You have a grace period until {$graceEndDate} to renew and regain access to all features.")
            ->line("During the grace period, you may experience limited functionality.")
            ->action('Renew Subscription', url('/subscriptions/renew'))
            ->line('Renew now to avoid any service interruption.')
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
            'type' => 'subscription_expired',
            'subscription_id' => $this->subscription->id,
            'tier_name' => $this->subscription->tier->name,
            'expired_date' => $this->subscription->expires_at,
            'grace_period_days' => $this->gracePeriodDays,
            'message' => "Your {$this->subscription->tier->name} subscription has expired. Renew within {$this->gracePeriodDays} days to avoid service interruption.",
        ];
    }
}
