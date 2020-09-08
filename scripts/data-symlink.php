<?php

/**
 * This script creates a symlink to files/data directory into web directory.
 *
 * It is needed for the installation from a pre-built archive
 * as this symlink cannot be generated correctly in the archive.
 */

set_error_handler(function ($severity, $message, $file, $line) {
    throw new ErrorException($message, 0, $severity, $file, $line);
});

exec('ln -s '.__DIR__.'/../files/data '.__DIR__.'/../public');
