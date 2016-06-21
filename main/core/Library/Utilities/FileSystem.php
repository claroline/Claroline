<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Library\Utilities;

use Symfony\Component\Filesystem\Filesystem as Fs;

class FileSystem extends Fs
{
    public function rmdir($path, $recursive = false)
    {
        if (is_dir($path)) {
            if (!$recursive) {
                rmdir($path);
            } else {
                $this->recursiveRemoveDirectory($path);
            }
        }
    }

    public function rmDirContent($path, $recursive = false)
    {
        $iterator = new \DirectoryIterator($path);

        foreach ($iterator as $el) {
            if (!$el->isDot()) {
                if ($el->isDir()) {
                    $this->rmdir($el->getRealPath(), $recursive);
                }
                if ($el->isFile()) {
                    $this->remove($el->getRealPath());
                }
            }
        }
    }

    /**
     * @deprecated
     * Please remove me from bundle manager if you can and do something better
     */
    public function copyDir($path, $target, $originalPath = '', $originalTarget = '')
    {
        $iterator = new \DirectoryIterator($path);
        if ($originalPath === '') {
            $originalPath = $path;
        }
        if ($originalTarget === '') {
            $originalTarget = $target;
        }

        foreach ($iterator as $el) {
            if (!$el->isDot()) {
                $parts = explode($originalPath, $el->getRealPath());
                $basePath = $parts[1];
                $newPath = $originalTarget.$basePath;

                if ($el->isDir()) {
                    $this->mkdir($newPath);
                    $this->copyDir($el->getRealPath(), $newPath, $originalPath, $originalTarget);
                } elseif ($el->isFile()) {
                    $this->copy($el->getRealPath(), $newPath);
                }
            }
        }
    }

    private function recursiveRemoveDirectory($dir)
    {
        $it = new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS);
        $files = new \RecursiveIteratorIterator($it, \RecursiveIteratorIterator::CHILD_FIRST);

        foreach ($files as $file) {
            if ($file->isFile()) {
                unlink($file->getRealPath());
            } else {
                rmdir($file->getRealPath());
            }
        }

        if (is_dir($dir)) {
            rmdir($dir);
        } else {
            unlink($dir);
        }
    }

    public function isWritable($path, $recursive = false)
    {
        if (!$recursive) {
            return is_writable($path);
        }

        $it = new \RecursiveDirectoryIterator($path, \RecursiveDirectoryIterator::SKIP_DOTS);
        $files = new \RecursiveIteratorIterator($it, \RecursiveIteratorIterator::CHILD_FIRST);

        foreach ($files as $file) {
            if (!is_writable($file)) {
                return false;
            }
        }

        return true;
    }
}
