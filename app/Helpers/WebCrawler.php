<?php

namespace App\Helpers;

use App\Models\Lead;
use App\Models\Campaign;
use Illuminate\Support\Facades\Http;
use DOMDocument;
use DOMXPath;

class WebCrawler
{
    /**
     * Crawl the web for leads based on a search category and populate the leads table.
     *
     * @param string $category The search category (e.g., 'restaurants', 'plumbers')
     * @param int $campaignId The ID of the campaign to associate leads with
     * @param int|null $userId The ID of the user (optional)
     * @return array Summary of crawled leads
     */
    public static function crawlLeads(string $category, int $campaignId, int $userId = null): array
    {
        // Check if campaign exists
        $campaign = Campaign::find($campaignId);
        if (!$campaign) {
            return ['error' => 'Campaign not found'];
        }

        // Example: Use Yellow Pages or similar directory site
        // Note: In production, use official APIs to avoid TOS violations
        $searchUrl = "https://www.yellowpages.com/search?search_terms=" . urlencode($category) . "&geo_location_terms=New+York%2C+NY"; // Example location

        try {
            $response = Http::timeout(30)->get($searchUrl);

            if (!$response->successful()) {
                return ['error' => 'Failed to fetch search results'];
            }

            $html = $response->body();

            // Parse HTML
            $dom = new DOMDocument();
            @$dom->loadHTML($html); // Suppress warnings
            $xpath = new DOMXPath($dom);

            // Example selectors for Yellow Pages (may change, use inspect element to find actual selectors)
            $businessNodes = $xpath->query("//div[contains(@class, 'business-name')]"); // Adjust based on actual site

            $leadsCreated = 0;
            foreach ($businessNodes as $node) {
                $name = trim($node->textContent);

                // Find phone number (example selector)
                $phoneNode = $xpath->query(".//div[contains(@class, 'phone')]", $node)->item(0);
                $phone = $phoneNode ? trim($phoneNode->textContent) : null;

                // Find email if available (rare on directories)
                $email = null; // Directories usually don't list emails publicly

                // Skip if no name or phone
                if (!$name || !$phone) {
                    continue;
                }

                // Create lead
                Lead::create([
                    'leadable_type' => Campaign::class,
                    'leadable_id' => $campaignId,
                    'ownerable_type' => Campaign::class, // Assuming owner is the campaign
                    'ownerable_id' => $campaignId,
                    'campaign_id' => $campaignId,
                    'user_id' => $userId,
                    'name' => $name,
                    'phone_number' => $phone,
                    'email' => $email,
                    'status' => Lead::PENDING_LEAD,
                ]);

                $leadsCreated++;
            }

            return [
                'success' => true,
                'leads_created' => $leadsCreated,
                'category' => $category,
                'campaign_id' => $campaignId,
            ];

        } catch (\Exception $e) {
            return ['error' => 'Crawl failed: ' . $e->getMessage()];
        }
    }
}
