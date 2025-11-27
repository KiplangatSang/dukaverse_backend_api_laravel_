<?php

$dir = 'app/Models';
$files = glob($dir . '/*.php');

foreach ($files as $file) {
    $content = file_get_contents($file);
    // Remove extra { and use SoftDeletes; if present
    $content = preg_replace('/(\{\s*use SoftDeletes;\s*\{\s*use HasFactory;)/', '{\n    use HasFactory, SoftDeletes;', $content);
    file_put_contents($file, $content);
}

echo "Fixed extra braces.\n";
