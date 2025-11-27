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

class ProcessFailedPayments extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'subscriptions:process-failed-payments {--dry-run : Run without making actual changes} {--max-retries=3 : Maximum retry attempts}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Retry failed subscription renewal payments';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $dryRun = $this->option('dry-run');
        $maxRetries = (int) $this->option('max-retries');

        if ($dryRun) {
            $this->warn('Running in DRY-RUN mode. No actual changes will be made.');
        }

        $this->info("Processing failed payments (max retries: {$maxRetries})...");

        // Get subscriptions with failed payments that haven't exceeded max retries
        $failedSubscriptions = Subscription::where('status', 'payment_failed')
            ->where('auto_renewal', true)
            ->where('retry_count', '<', $maxRetries)
            ->with(['user', 'tier'])
            ->get();

        $this->info("Found {$failedSubscriptions->count()} failed subscriptions eligible for retry.");

        $recoveredCount = 0;
        $stillFailedCount = 0;
        $errorCount = 0;

        foreach ($failedSubscriptions as $subscription) {
            try {
                DB::beginTransaction();

                $this->line("Retrying payment for {$subscription->user->email} - {$subscription->tier->name} (attempt " . ($subscription->retry_count + 1) . "/{$maxRetries})");

                // Attempt payment processing again
                $paymentSuccessful = $this->processPayment($subscription);

                if ($paymentSuccessful) {
                    if (!$dryRun) {
                        // Calculate new expiration date
                        $newExpirationDate = $this->calculateNewExpirationDate($subscription);

                        // Update subscription
                        $subscription->update([
                            'expires_at' => $newExpirationDate,
                            'status' => 'active',
                            'retry_count' => 0, // Reset retry count on success
                        ]);

                        // Create transaction record
                        Transaction::create([
                            'subscription_id' => $subscription->id,
                            'user_id' => $subscription->user->id,
                            'amount' => $subscription->discounted_price ?? $subscription->tier->price,
                            'currency' => 'USD',
                            'status' => 'completed',
                            'type' => 'retry_renewal',
                            'description' => "Retry renewal for {$subscription->tier->name}",
                        ]);

                        // Send renewal notification
                        $subscription->user->notify(new SubscriptionRenewedNotification(
                            $subscription,
                            Carbon::now(),
                            $newExpirationDate
                        ));

                        $this->line("✓ Successfully recovered payment for {$subscription->user->email}");
                        $recoveredCount++;
                    } else {
                        $this->line("[DRY-RUN] Would recover payment for {$subscription->user->email}");
                        $recoveredCount++;
                    }
                } else {
                    // Payment still failed - increment retry count
                    if (!$dryRun) {
                        $newRetryCount = $subscription->retry_count + 1;

                        if ($newRetryCount >= $maxRetries) {
                            // Max retries reached - disable auto-renewal
                            $subscription->update([
                                'retry_count' => $newRetryCount,
                                'auto_renewal' => false,
                                'status' => 'payment_failed_max_retries',
                            ]);

                            // Send final failure notification
                            $subscription->user->notify(new PaymentFailedNotification($subscription, true));

                            Log::warning("Payment failed permanently for subscription", [
                                'subscription_id' => $subscription->id,
                                'user_id' => $subscription->user->id,
                                'tier' => $subscription->tier->name,
                                'max_retries_reached' => true,
                            ]);
                        } else {
                            // Increment retry count and send retry notification
                            $subscription->update(['retry_count' => $newRetryCount]);

                            // Send retry notification
                            $subscription->user->notify(new PaymentFailedNotification($subscription, false, $newRetryCount));
                        }

                        $this->line("✗ Payment still failed for {$subscription->user->email} (attempt " . $newRetryCount . "/{$maxRetries})");
                        $stillFailedCount++;
                    } else {
                        $this->line("[DRY-RUN] Would increment retry count for {$subscription->user->email}");
                        $stillFailedCount++;
                    }
                }

                DB::commit();

            } catch (\Exception $e) {
                DB::rollBack();
                $this->error("✗ Error processing failed payment for {$subscription->user->email}: {$e->getMessage()}");
                Log::error("Failed payment retry error", [
                    'subscription_id' => $subscription->id,
                    'error' => $e->getMessage(),
                ]);
                $errorCount++;
            }
        }

        $this->info("Failed payment processing complete:");
        $this->info("✓ Recovered: {$recoveredCount}");
        $this->info("✗ Still failed: {$stillFailedCount}");
        if ($errorCount > 0) {
            $this->error("✗ Errors: {$errorCount}");
        }

        return Command::SUCCESS;
    }

    /**
     * Process payment for subscription renewal retry
     * This is a placeholder - integrate with your actual payment gateway
     */
    private function processPayment(Subscription $subscription): bool
    {
        // TODO: Integrate with actual payment gateway (Stripe, PayPal, etc.)
        // For now, simulate recovery rate based on retry count
        $recoveryRate = max(0.3, 1 - ($subscription->retry_count * 0.2)); // Decreasing success rate with retries
        return rand(1, 100) <= ($recoveryRate * 100);
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
