<?php

namespace App;

use Symfony\Component\Config\Loader\LoaderInterface;
use Claroline\KernelBundle\ClarolineKernelBundle;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;

class Kernel extends BaseKernel
{
    private $kernelBundle;

    public function __construct($environment, $debug)
    {	
	    parent::__construct($environment, $debug);
        $this->kernelBundle = new ClarolineKernelBundle($this);
	    
	    date_default_timezone_set('UTC');
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

    /**
     * {@inheritdoc}
     *
     * @todo Remove this method once the directory structure (+provisioning/deploy shared directories) has been updated for 4.4+
     */
    public function getLogDir()
    {
        return $this->getProjectDir() . '/app/log';
    }

    /**
     * {@inheritdoc}
     *
     * @todo Remove this method once the directory structure (+provisioning/deploy shared directories) has been updated for 4.4+
     */
    public function getCacheDir()
    {
        return $this->getProjectDir() . '/app/cache/' . $this->getEnvironment();
    }
}
