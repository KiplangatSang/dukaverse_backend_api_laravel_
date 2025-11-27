<?php

namespace App\Console;

use App\Jobs\CheckEmailsJob;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Schedule the populate:leads command to run daily at 9 AM
        $schedule->command('populate:leads')->dailyAt('09:00');

        // Schedule email checking every 5 minutes
        $schedule->job(CheckEmailsJob::class)->everyFiveMinutes();

        // Subscription management commands
        // Process subscription renewals daily at 2 AM
        $schedule->command('subscriptions:process-renewals')
            ->dailyAt('02:00')
            ->withoutOverlapping()
            ->runInBackground();

        // Send trial ending notifications daily at 10 AM
        $schedule->command('subscriptions:send-trial-ending-notifications --days=3')
            ->dailyAt('10:00');

        // Send expiration notifications daily at 11 AM
        $schedule->command('subscriptions:send-expiration-notifications --days=7')
            ->dailyAt('11:00');

        // Process failed payments daily at 3 AM
        $schedule->command('subscriptions:process-failed-payments --max-retries=3')
            ->dailyAt('03:00')
            ->withoutOverlapping()
            ->runInBackground();

        // Cleanup expired subscriptions daily at 4 AM
        $schedule->command('subscriptions:cleanup-expired --grace-period-days=7')
            ->dailyAt('04:00')
            ->withoutOverlapping()
            ->runInBackground();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
