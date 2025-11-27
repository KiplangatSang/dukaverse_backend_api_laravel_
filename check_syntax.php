<?php

function checkSyntax($file) {
    $output = shell_exec("php -l \"$file\" 2>&1");
    if (strpos($output, 'No syntax errors detected') === false) {
        echo "Syntax error in $file: $output\n";
    }
}

$dir = 'app';
$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
foreach ($iterator as $file) {
    if ($file->isFile() && $file->getExtension() === 'php') {
        checkSyntax($file->getPathname());
    }
}

echo "Syntax check complete.\n";
