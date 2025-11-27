<?php

namespace App\Console\Commands;

use App\Models\Subscription;
use App\Notifications\TrialEndingNotification;
use Illuminate\Console\Command;
use Carbon\Carbon;

class SendTrialEndingNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'subscriptions:send-trial-ending-notifications {--days=3 : Days before trial ends to send notification}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send notifications to users whose trial periods are ending soon';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $days = (int) $this->option('days');

        $this->info("Sending trial ending notifications for subscriptions ending in {$days} days...");

        // Get subscriptions in trial that are ending within the specified days
        $subscriptions = Subscription::trialEndingSoon($days)
            ->with(['user', 'tier'])
            ->get();

        $this->info("Found {$subscriptions->count()} subscriptions with trials ending in {$days} days.");

        $sentCount = 0;
        foreach ($subscriptions as $subscription) {
            try {
                // Check if notification was already sent for this subscription and timeframe
                $existingNotification = $subscription->user->notifications()
                    ->where('type', 'trial_ending')
                    ->where('data->subscription_id', $subscription->id)
                    ->where('data->days_left', $days)
                    ->where('created_at', '>=', Carbon::now()->subDays(1)) // Avoid duplicate notifications within 24 hours
                    ->first();

                if (!$existingNotification) {
                    $subscription->user->notify(new TrialEndingNotification($subscription, $days));
                    $sentCount++;
                    $this->line("Sent notification to {$subscription->user->email} for {$subscription->tier->name} trial");
                } else {
                    $this->line("Skipped duplicate notification for {$subscription->user->email}");
                }
            } catch (\Exception $e) {
                $this->error("Failed to send notification to {$subscription->user->email}: {$e->getMessage()}");
            }
        }

        $this->info("Successfully sent {$sentCount} trial ending notifications.");
        return Command::SUCCESS;
    }
}
