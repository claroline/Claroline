<?php
/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Library\Installation\Updater;


class WebUpdater
{
    private $files = [];
    private $directories = [];
    private $webSrc;
    private $webProd;

    public function __construct($rootDir)
    {
        $ds = DIRECTORY_SEPARATOR;
        $this->webSrc = "{$rootDir}{$ds}..{$ds}vendor{$ds}claroline{$ds}core-bundle{$ds}Claroline{$ds}CoreBundle{$ds}Resources/web";
        $this->webProd = "{$rootDir}{$ds}..{$ds}web";
        $this->iterate($this->webSrc);
    }

    public function preUpdate()
    {
        $this->clean();
        $this->copy();
    }

    private function copy()
    {
        foreach ($this->directories as $newPath => $oldPath) {
            if (!file_exists($newPath)) {
                mkdir($newPath, 0777, true);
            }
        }

        foreach ($this->files as $newPath => $oldPath) {
            if (!file_exists($newPath)) {
                copy($oldPath, $newPath);
            }
        }
    }

    private function clean()
    {
        foreach ($this->directories as $newPath => $oldPath) {
            if (file_exists($newPath)) {
                rmdir($newPath);
            }
        }

        foreach ($this->files as $newPath => $oldPath) {
            if (file_exists($newPath)) {
                unlink($newPath);
            }
        }
    }

    private function iterate($path)
    {
        $ds = DIRECTORY_SEPARATOR;
        $iterator = new \DirectoryIterator($path);

        foreach ($iterator as $element) {

            $newPath = $this->webProd . str_replace($this->webSrc, '', $element->getPathName());

            if ($element->isDir() && !$element->isDot()) {
                $this->directories[$newPath] = $element->getPathName();
                $this->iterate($element->getPathName());
            }

            if ($element->isFile()) {
                $this->files[$newPath] = $element->getPathName();
            }
        }
    }
} 