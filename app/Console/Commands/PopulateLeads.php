<?php

namespace App\Console\Commands;

use App\Helpers\WebCrawler;
use Illuminate\Console\Command;

class PopulateLeads extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'populate:leads {--category=restaurants : The search category} {--campaign_id=1 : The campaign ID} {--user_id= : The user ID (optional)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Populate leads table by crawling web for a given category';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $category = $this->option('category');
        $campaignId = (int) $this->option('campaign_id');
        $userId = $this->option('user_id') ? (int) $this->option('user_id') : null;

        $this->info("Starting to populate leads for category: {$category}, campaign ID: {$campaignId}");

        $result = WebCrawler::crawlLeads($category, $campaignId, $userId);

        if (isset($result['error'])) {
            $this->error("Error: {$result['error']}");
            return Command::FAILURE;
        }

        $this->info("Success: {$result['leads_created']} leads created for category {$result['category']}");

        return Command::SUCCESS;
    }
}
