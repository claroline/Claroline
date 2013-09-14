<?php

use Symfony\Component\Config\Loader\LoaderInterface;
use Claroline\KernelBundle\Kernel\SwitchKernel;
use Claroline\KernelBundle\ClarolineKernelBundle;

class AppKernel extends SwitchKernel
{
    private $kernelBundle;

    public function __construct($environment, $debug)
    {
        parent::__construct($environment, $debug);
        $this->kernelBundle = new ClarolineKernelBundle($this);
    }

    public function registerBundles()
    {
        return $this->kernelBundle->getBundles();
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $this->kernelBundle->loadConfigurations($loader);
        $loader->load(__DIR__ . '/config/parameters.yml');
    }
}
