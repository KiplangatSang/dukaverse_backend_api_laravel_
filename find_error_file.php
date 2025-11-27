<?php

require_once 'vendor/autoload.php';

use PhpParser\ParserFactory;
use PhpParser\Error;

$parser = (new ParserFactory())->createForNewestSupportedVersion();

$dir = 'app';
$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
foreach ($iterator as $file) {
    if ($file->isFile() && $file->getExtension() === 'php') {
        try {
            $stmts = $parser->parse(file_get_contents($file->getPathname()));
        } catch (Error $e) {
            echo "Error in file: " . $file->getPathname() . "\n";
            echo "Error: " . $e->getMessage() . "\n";
            break;
        }
    }
}

echo "Search complete.\n";
