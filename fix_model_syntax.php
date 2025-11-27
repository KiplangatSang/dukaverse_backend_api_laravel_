<?php

$dir = 'app/Models';
$files = glob($dir . '/*.php');

foreach ($files as $file) {
    $content = file_get_contents($file);
    // Fix the syntax error: remove extra { and use SoftDeletes; between class and use HasFactory;
    $content = preg_replace('/(class \w+ extends \w+)\n\{\n    use SoftDeletes;\n\{\n    use HasFactory;/', '$1\n{\n    use HasFactory, SoftDeletes;', $content);
    // If SoftDeletes is not in the use, add it
    if (strpos($content, 'use HasFactory, SoftDeletes;') === false && strpos($content, 'use SoftDeletes;') !== false) {
        $content = str_replace('use HasFactory;', 'use HasFactory, SoftDeletes;', $content);
        $content = str_replace('    use SoftDeletes;', '', $content);
    }
    file_put_contents($file, $content);
}

echo "Fixed syntax errors in model files.\n";
