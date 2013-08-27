<?php

use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Config\Loader\LoaderInterface;
use Claroline\InstallationBundle\ClarolineInstallationBundle;

class AppKernel extends Kernel
{
    private $installBundle;

    public function __construct($environment, $debug)
    {
        parent::__construct($environment, $debug);
        $this->installBundle = new ClarolineInstallationBundle($this);
    }

    public function registerBundles()
    {
        return $this->installBundle->getBundles();
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $this->installBundle->loadConfigurations($loader);
        $loader->load(__DIR__ . '/config/local/parameters.yml');
    }
}
