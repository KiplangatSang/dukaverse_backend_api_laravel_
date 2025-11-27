<?php

namespace App\Jobs;

use App\Models\EmailConfig;
use App\Services\EmailService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class CheckEmailsJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(EmailService $emailService): void
    {
        $configs = EmailConfig::where('active', true)->get();

        foreach ($configs as $config) {
            try {
                $emailService->readEmails($config);
            } catch (\Exception $e) {
                Log::error("Error checking emails for config {$config->client_name}: " . $e->getMessage());
            }
        }
    }
}
