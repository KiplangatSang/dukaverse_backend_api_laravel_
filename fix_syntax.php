<?php

$dir = 'app/Models';
$files = glob($dir . '/*.php');

foreach ($files as $file) {
    $content = file_get_contents($file);
    // Fix the malformed class declaration
    $content = preg_replace('/(class \w+ extends \w+)\n\{\n    use SoftDeletes;\n\{\n    use HasFactory, SoftDeletes;/', '$1\n{\n    use HasFactory, SoftDeletes;', $content);
    // Also fix if it's \n{\n
    $content = str_replace('\n{\n', '{\n', $content);
    file_put_contents($file, $content);
}

echo "Fixed syntax.\n";
