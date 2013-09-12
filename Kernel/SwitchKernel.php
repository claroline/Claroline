<?php

namespace Claroline\KernelBundle\Kernel;

use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Filesystem\Filesystem;

abstract class SwitchKernel extends Kernel
{
    private $hasSwitched = false;
    private $originalEnvironement;
    private $originalContainer;
    private $fileSystem;

    public function switchToTmpEnvironment()
    {
        if ($this->hasSwitched) {
            throw new \LogicException('Already switched to tmp environment');
        }

        $this->originalEnvironement = $this->environment;
        $this->originalContainer = $this->container;
        $this->environment = 'tmp-switch-env';
        $this->hasSwitched = true;
        $this->shutdown();
        $this->boot();
    }

    public function switchBack()
    {
        if (!$this->hasSwitched) {
            throw new \LogicException('Kernel is in its original environment');
        }

        $fileSystem = $this->fileSystem ?: new Filesystem();
        $fileSystem->remove($this->getCacheDir());
        $this->environment = $this->originalEnvironement;
        $this->container = $this->originalContainer;
        $this->hasSwitched = false;

        $this->shutdown();
        $this->boot();
    }

    public function setFileSystemHandler(Filesystem $handler)
    {
        $this->fileSystem = $handler;
    }

    protected function getContainerClass()
    {
        if (!$this->hasSwitched) {
            return parent::getContainerClass();
        }

        return 'tmpSwitchContainer' . time();
    }
}
