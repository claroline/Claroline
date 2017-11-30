<?php

/**
 * This script creates a backup of the local package repository
 * (stored in vendor/composer/installed.json) so that the upgrade
 * process can be based on the difference between that version and
 * the version resulting from an update of the vendors.
 */

$installed = __DIR__ . '/../vendor/composer/installed.json';
$previous = __DIR__ . '/../app/config/previous-installed.json';

set_error_handler(function ($severity, $message, $file, $line) {
    throw new ErrorException($message, 0, $severity, $file, $line);
});

if (!file_exists($previous)) {
    if (!file_exists($installed)) {
        file_put_contents($previous, '[]');
    } else {
        copy($installed, $previous);
    }
}
