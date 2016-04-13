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
    private $webSrc;
    private $webProd;

    public function __construct($rootDir)
    {
        $ds = DIRECTORY_SEPARATOR;
        $this->webSrc = "{$rootDir}{$ds}..{$ds}vendor{$ds}claroline{$ds}distribution{$ds}main{$ds}core{$ds}Resources/web";
        $this->webProd = "{$rootDir}{$ds}..{$ds}web";
        $this->listFiles($this->webSrc);
    }

    public function preUpdate()
    {
        $this->clean();
        $this->copy();
    }

    private function copy()
    {
        foreach ($this->files as $newPath => $oldPath) {
            if (!file_exists($newPath)) {
                if (is_dir($oldPath)) {
                    mkdir($newPath, 0777, true);
                } else {
                    copy($oldPath, $newPath);
                }
            }
        }
    }

    private function clean()
    {
        foreach ($this->files as $newPath => $oldPath) {
            $this->deleteDirectory($newPath);
        }
    }

    private function listFiles($path)
    {
        $ds = DIRECTORY_SEPARATOR;
        $iterator = new \DirectoryIterator($path);

        foreach ($iterator as $element) {
            $newPath = $this->webProd.str_replace($this->webSrc, '', $element->getPathName());

            if (!$element->isDot()) {
                $this->files[$newPath] = $element->getPathName();

                if ($element->isDir()) {
                    $this->listFiles($element->getPathName());
                }
            }
        }
    }

    /**
     * http://stackoverflow.com/questions/1653771/how-do-i-remove-a-directory-that-is-not-empty.
     *
     * @param $dir
     *
     * @return bool
     */
    private function deleteDirectory($dir)
    {
        if (!file_exists($dir)) {
            return true;
        }

        if (!is_dir($dir)) {
            return unlink($dir);
        }

        foreach (scandir($dir) as $item) {
            if ($item == '.' || $item == '..') {
                continue;
            }

            if (!$this->deleteDirectory($dir.DIRECTORY_SEPARATOR.$item)) {
                return false;
            }
        }

        return rmdir($dir);
    }
}
