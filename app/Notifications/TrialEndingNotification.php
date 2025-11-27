<?php

namespace App\Notifications;

use App\Models\Subscription;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Carbon\Carbon;

class TrialEndingNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $subscription;
    public $daysLeft;

    /**
     * Create a new notification instance.
     */
    public function __construct(Subscription $subscription, int $daysLeft = 3)
    {
        $this->subscription = $subscription;
        $this->daysLeft = $daysLeft;
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
        $trialEndDate = Carbon::parse($this->subscription->trial_end_date)->format('M j, Y');

        return (new MailMessage)
            ->subject("Your {$tier->name} Trial Ends Soon")
            ->greeting("Hi {$notifiable->name},")
            ->line("Your trial period for {$tier->name} is ending in {$this->daysLeft} days.")
            ->line("Trial End Date: {$trialEndDate}")
            ->line("Don't lose access to your subscription benefits. Upgrade now to continue enjoying all features.")
            ->action('Upgrade Now', url('/subscriptions/upgrade'))
            ->line('Thank you for using our service!')
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
            'type' => 'trial_ending',
            'subscription_id' => $this->subscription->id,
            'tier_name' => $this->subscription->tier->name,
            'days_left' => $this->daysLeft,
            'trial_end_date' => $this->subscription->trial_end_date,
            'message' => "Your {$this->subscription->tier->name} trial ends in {$this->daysLeft} days.",
        ];
    }
}
