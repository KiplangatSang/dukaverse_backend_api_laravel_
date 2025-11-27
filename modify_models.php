<?php

$dir = 'app/Models';
$files = glob($dir . '/*.php');

foreach ($files as $file) {
    $content = file_get_contents($file);
    // Add use SoftDeletes; after other use statements
    if (strpos($content, 'use Illuminate\Database\Eloquent\SoftDeletes;') === false) {
        $content = preg_replace('/(use Illuminate\\\\Database\\\\Eloquent\\\\Model;)/', "$1\nuse Illuminate\\Database\\Eloquent\\SoftDeletes;", $content);
    }
    // Add use SoftDeletes; in the class
    if (strpos($content, 'use SoftDeletes;') === false) {
        $content = preg_replace('/(class \w+ extends Model)/', "$1\n{\n    use SoftDeletes;", $content);
    }
    file_put_contents($file, $content);
}

echo "Modified all model files.\n";
