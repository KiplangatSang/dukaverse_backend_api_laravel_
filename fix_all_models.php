<?php

$dir = 'app/Models';
$files = glob($dir . '/*.php');

foreach ($files as $file) {
    $content = file_get_contents($file);
    // Remove extra { and }
    $content = preg_replace('/(class \w+ extends \w+)\n\{\n\n\{\n/', "$1\n\{\n", $content);
    $content = preg_replace('/\n\}\n\n\}/', "\n}", $content);
    file_put_contents($file, $content);
}

echo "Fixed all model files.\n";
