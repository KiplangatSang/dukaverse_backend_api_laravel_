<?php

$dir = 'app/Models';
$files = glob($dir . '/*.php');

foreach ($files as $file) {
    $content = file_get_contents($file);
    // Fix the {\n to {
    $content = str_replace('{\n', "{\n", $content);
    file_put_contents($file, $content);
}

echo "Final fix.\n";
