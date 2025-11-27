<?php

$dir = 'app';
$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
foreach ($iterator as $file) {
    if ($file->isFile() && $file->getExtension() === 'php') {
        $lines = file($file->getPathname());
        if (isset($lines[10]) && strpos($lines[10], '{') !== false) { // line 11 is index 10
            echo "File: " . $file->getPathname() . " has '{' on line 11\n";
            echo "Line 11: " . trim($lines[10]) . "\n";
        }
    }
}

echo "Search complete.\n";
