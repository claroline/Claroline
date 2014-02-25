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
        if ($item->isFile() && $item->getFileName() !== 'placeholder' && $item->getFileName() !== '.gitempty' && $item->getFileName() !== '.gitkeep') {
            unlink($item->getPathname());
        }
        if ($item->isDir() && !$item->isDot() && $item->getFilename() !== "tmp" && $item->getFilename() !== "thumbs") {
            emptyDir($item->getPathname());
            rmdir($item->getPathname());
        }
    }
}
