<?php

namespace Claroline\AppBundle;

use Claroline\KernelBundle\ClarolineKernelBundle;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;
use Symfony\Component\Routing\RouteCollectionBuilder;

class Kernel extends BaseKernel
{
    use MicroKernelTrait;

    private $kernelBundle;

    public function __construct(string $environment, bool $debug)
    {
        parent::__construct($environment, $debug);

        $this->kernelBundle = new ClarolineKernelBundle($this);

        // make sure dates are in UTC regardless to the server config
        date_default_timezone_set('UTC');
    }

    public function registerBundles(): iterable
    {
        return $this->kernelBundle->getBundles();
    }

    public function getProjectDir(): string
    {
        return realpath(__DIR__.'/../../..');
    }

    protected function configureContainer(ContainerBuilder $container, LoaderInterface $loader): void
    {
        $loader->load($this->getProjectDir().'/config/parameters.yml');

        $this->kernelBundle->loadConfigurations($loader);
    }

    protected function configureRoutes(RouteCollectionBuilder $routes): void
    {
    }
}
