<?php

namespace Claroline\KernelBundle\Kernel;

use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Filesystem\Filesystem;

abstract class RebootableKernel extends Kernel
{
    private $isRebooting = false;
    private $lastInitTime = 0;
    private $fileSystem;

    public function reboot()
    {
        $this->isRebooting = true;
        $this->shutdown();
        $this->boot();
        $this->isRebooting = false;
        $this->clearTmpCache();
    }

    public function getCacheDir()
    {
        if (!$this->isRebooting) {
            return parent::getCacheDir();
        }

        return $this->rootDir . '/cache/tmp';
    }

    public function setFileSystemHandler(Filesystem $handler)
    {
        $this->fileSystem = $handler;
    }

    protected function getContainerClass()
    {
        if (!$this->isRebooting) {
            return parent::getContainerClass();
        }

        return 'tmpContainerReboot' . time();
    }

    protected function initializeContainer()
    {
        if ($this->lastInitTime === $time = time()) {
            // avoid conflicts for generated proxies whose name relies
            // on a timestamp if the kernel is (re-)booted repeatedly
            sleep(2);
        }

        parent::initializeContainer();
        $this->lastInitTime = $time;
    }

    private function clearTmpCache()
    {
        $handler = $this->fileSystem ?: new Filesystem();
        $handler->remove($this->rootDir . '/cache/tmp');
    }
}
