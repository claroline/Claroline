<?php

if (count($argv) < 2) {
    echo "Cannot launch tool: Missing target\n";
    exit(1);
}

$rootDir = realpath(__DIR__.'/../../../../../../..');
$binDir = "{$rootDir}/bin";
$configDir = realpath(__DIR__.'/../config');
