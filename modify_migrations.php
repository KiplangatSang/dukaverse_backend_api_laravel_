<?php

$dir = 'database/migrations';
$files = glob($dir . '/*.php');

foreach ($files as $file) {
    $content = file_get_contents($file);
    // Add softDeletes before timestamps
    $content = str_replace('$table->timestamps();', '$table->softDeletes();' . "\n" . '            $table->timestamps();', $content);
    file_put_contents($file, $content);
}

echo "Modified all migration files.\n";
