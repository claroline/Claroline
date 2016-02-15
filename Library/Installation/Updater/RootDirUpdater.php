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

use Claroline\InstallationBundle\Updater\Updater;
use Claroline\CoreBundle\Library\Utilities\FileSystem;

class RootDirUpdater extends Updater
{
    private $fs = null;
    private $rootSrc;
    private $rootProd;

    public function __construct($rootDir)
    {
        $ds = DIRECTORY_SEPARATOR;
        $this->rootSrc = "{$rootDir}{$ds}..{$ds}vendor{$ds}claroline{$ds}core-bundle{$ds}Resources/rootDir";
        $this->rootProd = "{$rootDir}{$ds}..{$ds}";
        $this->fs = new FileSystem();
    }

    public function preUpdate()
    {
        $this->log('Updating root files...');
        $this->copyDirContent($this->rootSrc, $this->rootProd);
    }

    private function copyDirContent($path, $target)
    {
        $iterator = new \DirectoryIterator($path);

        foreach ($iterator as $el) {
            if (!$el->isDot()) {
                $parts = explode($el->getPathName(), '/');
                $newPath = $target . $el->getFileName();
                if (file_exists($newPath)) unlink($newPath);
                $this->fs->copy($el->getRealPath(), $newPath);
            }
        }
    }
}
