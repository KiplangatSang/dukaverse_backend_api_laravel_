<?php

$dir = 'app/Models';
$files = glob($dir . '/*.php');

foreach ($files as $file) {
    $content = file_get_contents($file);
    // Remove , SoftDeletes from use lines
    $content = str_replace(', SoftDeletes', '', $content);
    // Add use SoftDeletes; if not present
    if (strpos($content, 'use Illuminate\Database\Eloquent\SoftDeletes;') === false) {
        $content = preg_replace('/(use [^;]+;\n)(class)/', "$1use Illuminate\\Database\\Eloquent\\SoftDeletes;\n$2", $content);
    }
    // Add SoftDeletes to the use in class if not present
    if (strpos($content, 'use SoftDeletes') === false) {
        $content = preg_replace('/(use [^;]*)(;)/', "$1, SoftDeletes$2", $content, 1); // only first
    }
    file_put_contents($file, $content);
}

echo "Fixed all model files.\n";
