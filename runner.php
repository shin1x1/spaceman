<?php

use Koriym\Spaceman\Convert;

require __DIR__ . '/vendor/autoload.php';

if ($argc < 2) {
    printf("Usage: %s PACKAGE_NAME TARGET_PATH\n", basename($argv[0]));
    exit(1);
}

$packageName = $argv[1];
$targetPath = $argv[2];

if ($targetPath[0] !== '/') {
    $targetPath = realpath(__DIR__ . '/' . $targetPath);
}

// Rewrite php file with adding namespace declaration starting `$packageName` on directory basis
(new Convert($packageName))($targetPath);

