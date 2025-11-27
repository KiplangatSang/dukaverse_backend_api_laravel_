<?php

namespace App\Console\Commands;

use App\Models\Subscription;
use App\Notifications\SubscriptionExpiredNotification;
use Illuminate\Console\Command;
use Carbon\Carbon;

class SendExpirationNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'subscriptions:send-expiration-notifications {--days=7 : Days before expiration to send notification}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send notifications to users whose subscriptions are expiring soon';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $days = (int) $this->option('days');

        $this->info("Sending expiration notifications for subscriptions expiring in {$days} days...");

        // Get subscriptions expiring within the specified days
        $subscriptions = Subscription::expiringSoon($days)
            ->with(['user', 'tier'])
            ->get();

        $this->info("Found {$subscriptions->count()} subscriptions expiring in {$days} days.");

        $sentCount = 0;
        foreach ($subscriptions as $subscription) {
            try {
                // Check if notification was already sent for this subscription and timeframe
                $existingNotification = $subscription->user->notifications()
                    ->where('type', 'subscription_expiring')
                    ->where('data->subscription_id', $subscription->id)
                    ->where('data->days_left', $days)
                    ->where('created_at', '>=', Carbon::now()->subDays(1)) // Avoid duplicate notifications within 24 hours
                    ->first();

                if (!$existingNotification) {
                    // Send expiration notification
                    $subscription->user->notify(new SubscriptionExpiredNotification($subscription, $days));
                    $sentCount++;
                    $this->line("Sent expiration notification to {$subscription->user->email} for {$subscription->tier->name} subscription");
                } else {
                    $this->line("Skipped duplicate notification for {$subscription->user->email}");
                }
            } catch (\Exception $e) {
                $this->error("Failed to send notification to {$subscription->user->email}: {$e->getMessage()}");
            }
        }

        $this->info("Successfully sent {$sentCount} expiration notifications.");
        return Command::SUCCESS;
    }
}
