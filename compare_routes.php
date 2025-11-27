<?php

// Function to extract routes from api.php
function extractRoutes($file) {
    $content = file_get_contents($file);
    $routes = [];
    $prefix = '';

    // Find prefix
    if (preg_match('/Route::prefix\(\'([^\']+)\'\)->group/', $content, $match)) {
        $prefix = '/' . $match[1];
    }

    // Find all Route:: lines
    preg_match_all('/Route::(get|post|put|delete|patch|resource)\(\s*[\'"]([^\'"]+)[\'"]\s*,/i', $content, $matches);

    foreach ($matches[0] as $i => $line) {
        $method = strtolower($matches[1][$i]);
        $path = $matches[2][$i];

        if ($method === 'resource') {
            // Expand resource to standard routes
            $resourceRoutes = [
                'get' => ['', '/create', '/{id}', '/{id}/edit'],
                'post' => [''],
                'put' => ['/{id}'],
                'delete' => ['/{id}']
            ];
            foreach ($resourceRoutes as $m => $paths) {
                foreach ($paths as $p) {
                    $fullPath = $prefix . $path . $p;
                    $routes[] = $m . ' ' . $fullPath;
                }
            }
        } else {
            $fullPath = $prefix . $path;
            $routes[] = $method . ' ' . $fullPath;
        }
    }

    return array_unique($routes);
}

// Function to extract swagger paths from controllers
function extractSwaggerPaths($dir) {
    $paths = [];
    $files = glob($dir . '/*.php');
    foreach ($files as $file) {
        $content = file_get_contents($file);
        // Find @OA\Get, @OA\Post, etc. with path
        preg_match_all('/@OA\\\(Get|Post|Put|Delete|Patch)\(\s*[^}]*path="([^"]+)"/i', $content, $matches);
        foreach ($matches[2] as $path) {
            $paths[] = $path;
        }
    }
    return array_unique($paths);
}

// Main
$apiRoutes = extractRoutes('routes/api.php');
$swaggerPaths = extractSwaggerPaths('app/Http/Controllers');

echo "Routes from api.php:\n";
foreach ($apiRoutes as $route) {
    echo $route . "\n";
}

echo "\nSwagger paths from controllers:\n";
foreach ($swaggerPaths as $path) {
    echo $path . "\n";
}

// Compare
$routePaths = array_map(function($r) { return explode(' ', $r)[1]; }, $apiRoutes);
$swaggerPathsAdjusted = array_map(function($p) { return substr($p, 4); }, $swaggerPaths); // remove /api
$missingInSwagger = array_diff($routePaths, $swaggerPathsAdjusted);
$extraInSwagger = array_diff($swaggerPathsAdjusted, $routePaths);

echo "\nRoutes in api.php but not in swagger:\n";
foreach ($missingInSwagger as $m) {
    echo $m . "\n";
}

echo "\nSwagger paths not in api.php:\n";
foreach ($extraInSwagger as $e) {
    echo $e . "\n";
}
?>
