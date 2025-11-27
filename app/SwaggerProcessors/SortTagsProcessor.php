<?php

namespace App\SwaggerProcessors;

use OpenApi\Analysis;

class SortTagsProcessor
{
    public function __invoke(Analysis $analysis)
    {
        $openapi = $analysis->openapi;

        if (isset($openapi->tags) && is_array($openapi->tags)) {
            usort($openapi->tags, function ($a, $b) {
                return strcmp($a->name, $b->name);
            });
        }
    }
}
