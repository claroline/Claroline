<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\KernelBundle\Kernel;

use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Filesystem\Filesystem;

abstract class SwitchKernel extends Kernel
{
    private $hasSwitched = false;
    private $fileSystem;
    private $loadAll = false;

    public function switchToTmpEnvironment()
    {
        if ($this->hasSwitched) {
            throw new \LogicException('Already switched to tmp environment');
        }

        $this->originalEnvironement = $this->environment;
        $this->environment = 'tmp'.time();
        $this->hasSwitched = true;
        $this->reboot();
    }

    public function switchBack()
    {
        if (!$this->hasSwitched) {
            throw new \LogicException('Kernel is in its original environment');
        }

        $this->removeCache();
        $this->environment = $this->originalEnvironement;
        $this->hasSwitched = false;

        $this->reboot();
    }

    public function setFileSystemHandler(Filesystem $handler)
    {
        $this->fileSystem = $handler;
    }

    public function removeCache()
    {
        $fileSystem = $this->fileSystem ?: new Filesystem();
        $fileSystem->remove($this->getCacheDir());
    }

    public function reboot()
    {
        $this->shutdown();
        $this->boot();
    }

    protected function getContainerClass()
    {
        if (!$this->hasSwitched) {
            return parent::getContainerClass();
        }

        return "{$this->environment}SwitchContainer";
    }
}
