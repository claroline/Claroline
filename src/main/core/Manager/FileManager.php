<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Manager;

use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;

class FileManager
{
    /** @var string */
    private $fileDir;
    /** @var PlatformConfigurationHandler */
    private $config;

    public function __construct(
        string $fileDir,
        PlatformConfigurationHandler $config
    ) {
        $this->fileDir = $fileDir;
        $this->config = $config;
    }

    /**
     * Get the path to the files directory.
     */
    public function getDirectory(): string
    {
        return $this->fileDir;
    }

    public function isStorageFull(): bool
    {
        // TODO : enable when our storage management is fixed
        return false
            && $this->config->getParameter('restrictions.storage')
            && $this->config->getParameter('restrictions.used_storage')
            && $this->config->getParameter('restrictions.used_storage') >= $this->config->getParameter('restrictions.storage');
    }

    /**
     * Computes the size of the files directory in bytes.
     */
    public function computeUsedStorage(): int
    {
        $filesDirSize = 0;

        foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($this->fileDir)) as $file) {
            if ('.' !== $file->getFilename() && '..' !== $file->getFilename()) {
                $filesDirSize += $file->getSize();
            }
        }

        return $filesDirSize;
    }

    /**
     * Dumps used storage in platform_options for performances (it's heavy to scan the whole files directory to get the files sizes).
     */
    public function updateUsedStorage(int $usedStorage): void
    {
        $this->config->setParameter('restrictions.used_storage', $usedStorage);
    }
}
