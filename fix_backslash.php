<?php

$dir = 'app/Models';
$files = glob($dir . '/*.php');

foreach ($files as $file) {
    $content = file_get_contents($file);
    // Replace \{ with {
    $content = str_replace('\\{', '{', $content);
    file_put_contents($file, $content);
}

echo "Fixed backslash in all model files.\n";
