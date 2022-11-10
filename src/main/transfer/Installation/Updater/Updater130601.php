<?php

namespace Claroline\TransferBundle\Installation\Updater;

use Claroline\InstallationBundle\Updater\Updater;
use Symfony\Component\Filesystem\Filesystem;

class Updater130601 extends Updater
{
    /** @var string */
    private $oldLogDir;

    /** @var string */
    private $newLogDir;

    public function __construct(
        string $logDir,
        string $newLogDir
    ) {
        $this->oldLogDir = $logDir.DIRECTORY_SEPARATOR.'transfer';
        $this->newLogDir = $newLogDir;
    }

    public function postUpdate()
    {
        // move transfer logs in files dir for more persistence
        $fs = new FileSystem();
        if ($fs->exists($this->oldLogDir)) {
            $fs->mirror($this->oldLogDir, $this->newLogDir);
        }
    }
}
