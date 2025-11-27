<?php

$dir = 'app/Models';
$files = glob($dir . '/*.php');

foreach ($files as $file) {
    $content = file_get_contents($file);
    // Add space before { in class declaration
    $content = preg_replace('/(class \w+ extends \w+)\{/', '$1 {', $content);
    file_put_contents($file, $content);
}

echo "Added space before brace.\n";
