<?php

namespace App\Console\Commands;

use App\Models\Subscription;
use App\Models\Transaction;
use App\Notifications\SubscriptionRenewedNotification;
use App\Notifications\PaymentFailedNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ProcessSubscriptionRenewals extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'subscriptions:process-renewals {--dry-run : Run without making actual changes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process automatic subscription renewals for active subscriptions';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $dryRun = $this->option('dry-run');

        if ($dryRun) {
            $this->warn('Running in DRY-RUN mode. No actual changes will be made.');
        }

        $this->info('Processing subscription renewals...');

        // Get subscriptions that need renewal (expires today or in the past, auto_renewal = true, active status)
        $subscriptions = Subscription::where('auto_renewal', true)
            ->where('status', 'active')
            ->where('expires_at', '<=', Carbon::now())
            ->with(['user', 'tier'])
            ->get();

        $this->info("Found {$subscriptions->count()} subscriptions eligible for renewal.");

        $renewedCount = 0;
        $failedCount = 0;

        foreach ($subscriptions as $subscription) {
            try {
                DB::beginTransaction();

                $this->line("Processing renewal for {$subscription->user->email} - {$subscription->tier->name}");

                // Attempt payment processing (this would integrate with your payment gateway)
                $paymentSuccessful = $this->processPayment($subscription);

                if ($paymentSuccessful) {
                    if (!$dryRun) {
                        // Calculate new expiration date
                        $newExpirationDate = $this->calculateNewExpirationDate($subscription);

                        // Update subscription
                        $subscription->update([
                            'expires_at' => $newExpirationDate,
                            'status' => 'active',
                        ]);

                        // Create transaction record
                        Transaction::create([
                            'subscription_id' => $subscription->id,
                            'user_id' => $subscription->user->id,
                            'amount' => $subscription->discounted_price ?? $subscription->tier->price,
                            'currency' => 'USD',
                            'status' => 'completed',
                            'type' => 'renewal',
                            'description' => "Auto-renewal for {$subscription->tier->name}",
                        ]);

                        // Send renewal notification
                        $subscription->user->notify(new SubscriptionRenewedNotification(
                            $subscription,
                            Carbon::now(),
                            $newExpirationDate
                        ));

                        $this->line("✓ Successfully renewed subscription for {$subscription->user->email}");
                        $renewedCount++;
                    } else {
                        $this->line("[DRY-RUN] Would renew subscription for {$subscription->user->email}");
                        $renewedCount++;
                    }
                } else {
                    // Payment failed - mark as payment_failed and notify user
                    if (!$dryRun) {
                        $subscription->update(['status' => 'payment_failed']);

                        // Send payment failed notification
                        $subscription->user->notify(new PaymentFailedNotification($subscription));

                        Log::warning("Payment failed for subscription renewal", [
                            'subscription_id' => $subscription->id,
                            'user_id' => $subscription->user->id,
                            'tier' => $subscription->tier->name,
                        ]);
                    }

                    $this->error("✗ Payment failed for {$subscription->user->email}");
                    $failedCount++;
                }

                DB::commit();

            } catch (\Exception $e) {
                DB::rollBack();
                $this->error("✗ Error processing renewal for {$subscription->user->email}: {$e->getMessage()}");
                Log::error("Subscription renewal error", [
                    'subscription_id' => $subscription->id,
                    'error' => $e->getMessage(),
                ]);
                $failedCount++;
            }
        }

        $this->info("Renewal processing complete:");
        $this->info("✓ Renewed: {$renewedCount}");
        $this->info("✗ Failed: {$failedCount}");

        return Command::SUCCESS;
    }

    /**
     * Process payment for subscription renewal
     * This is a placeholder - integrate with your actual payment gateway
     */
    private function processPayment(Subscription $subscription): bool
    {
        // TODO: Integrate with actual payment gateway (Stripe, PayPal, etc.)
        // For now, simulate 95% success rate
        return rand(1, 100) <= 95;
    }

    /**
     * Calculate new expiration date based on tier's billing interval
     */
    private function calculateNewExpirationDate(Subscription $subscription): Carbon
    {
        $interval = $subscription->tier->getBillingIntervalDays();
        return Carbon::now()->addDays($interval);
    }
}
