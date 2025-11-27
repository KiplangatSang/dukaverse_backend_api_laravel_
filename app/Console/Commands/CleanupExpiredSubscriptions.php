<?php

namespace App\Console\Commands;

use App\Models\Subscription;
use App\Events\SubscriptionExpired;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class CleanupExpiredSubscriptions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'subscriptions:cleanup-expired {--dry-run : Run without making actual changes} {--grace-period-days=7 : Grace period days before deactivation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Deactivate subscriptions that have expired beyond the grace period';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $dryRun = $this->option('dry-run');
        $gracePeriodDays = (int) $this->option('grace-period-days');

        if ($dryRun) {
            $this->warn('Running in DRY-RUN mode. No actual changes will be made.');
        }

        $this->info("Cleaning up expired subscriptions (grace period: {$gracePeriodDays} days)...");

        // Get subscriptions that have expired beyond the grace period
        $expiredSubscriptions = Subscription::expired()
            ->with(['user', 'tier'])
            ->get();

        $this->info("Found {$expiredSubscriptions->count()} expired subscriptions to process.");

        $deactivatedCount = 0;
        $errorCount = 0;

        foreach ($expiredSubscriptions as $subscription) {
            try {
                $this->line("Processing expired subscription for {$subscription->user->email} - {$subscription->tier->name}");

                if (!$dryRun) {
                    DB::beginTransaction();

                    // Update subscription status to expired
                    $subscription->update([
                        'status' => 'expired',
                        'is_active' => false,
                        'auto_renewal' => false,
                    ]);

                    // Fire expired event to trigger notifications
                    event(new SubscriptionExpired($subscription));

                    DB::commit();

                    $this->line("✓ Deactivated expired subscription for {$subscription->user->email}");
                    $deactivatedCount++;
                } else {
                    $this->line("[DRY-RUN] Would deactivate subscription for {$subscription->user->email}");
                    $deactivatedCount++;
                }

            } catch (\Exception $e) {
                if (!$dryRun) {
                    DB::rollBack();
                }

                $this->error("✗ Error processing expired subscription for {$subscription->user->email}: {$e->getMessage()}");
                Log::error("Subscription cleanup error", [
                    'subscription_id' => $subscription->id,
                    'error' => $e->getMessage(),
                ]);
                $errorCount++;
            }
        }

        $this->info("Cleanup processing complete:");
        $this->info("✓ Deactivated: {$deactivatedCount}");
        if ($errorCount > 0) {
            $this->error("✗ Errors: {$errorCount}");
        }

        return Command::SUCCESS;
    }
}
