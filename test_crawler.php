<?php

require_once 'vendor/autoload.php';

use App\Helpers\WebCrawler;

// Example test
$result = WebCrawler::crawlLeads('restaurants', 1, 1); // Assume campaign id 1 exists

print_r($result);
