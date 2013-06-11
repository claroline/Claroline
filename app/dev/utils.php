<?php

/**
 * This file gathers helper functions used in the raw installation script.
 */

/**
 * Empties a file or create an empty one.
 *
 * @param string $file
 */
function refreshFile($file)
{
    file_put_contents($file, '');
}

/**
 * Empties a directory recursively.
 *
 * @param string $directory
 */
function emptyDir($directory)
{
    if (!is_dir($directory)) {
        return;
    }

    $iterator = new DirectoryIterator($directory);

    foreach ($iterator as $item) {
        if ($item->isFile() && $item->getFileName() !== 'placeholder' && $item->getFileName() !== '.gitempty') {
            unlink($item->getPathname());
        }
        if ($item->isDir() && !$item->isDot() && $item->getFilename() !== "tmp" && $item->getFilename() !== "thumbs") {
            emptyDir($item->getPathname());
            rmdir($item->getPathname());
        }
    }
}

/**
 * Searches for the composer executable and launches it in a fork (in order to
 * keep the user interaction required when building the parameters.yml file with
 * 'incenteev/composer-parameter-handler'). Makes the process exit if composer
 * is not found.
 */
function execComposer()
{
    $path = getenv('PATH');
    $binDirs = explode(':', $path);
    $binDirs = count($binDirs) > 0 ? $binDirs : explode(';', $path);
    $composer = false;

    foreach ($binDirs as $binDir) {
        if (is_executable($path = "{$binDir}/composer")) {
            $composer = $path;
        }
    }

    $composer || exit("Cannot find 'composer' executable\n");

    if (0 === $pid = pcntl_fork()) {
        pcntl_exec($composer, array('install', '--dev'));
    }

    pcntl_waitpid($pid, $status);
}