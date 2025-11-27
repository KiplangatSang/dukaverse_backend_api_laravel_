<?php

$dir = 'app/Models';
$files = glob($dir . '/*.php');

foreach ($files as $file) {
    $content = file_get_contents($file);
    // Add use SoftDeletes; if not present
    if (strpos($content, 'use Illuminate\Database\Eloquent\SoftDeletes;') === false) {
        $content = preg_replace('/(use [^;]+;\n)(class)/', "$1use Illuminate\\Database\\Eloquent\\SoftDeletes;\n$2", $content);
    }
    // Add SoftDeletes to the use traits in class
    if (strpos($content, 'use SoftDeletes;') === false) {
        $content = preg_replace('/(use [^;]*)(;)/', "$1, SoftDeletes$2", $content);
    }
    file_put_contents($file, $content);
}

echo "Fixed all model files.\n";
