<?php

namespace Claroline\DevBundle;

use Claroline\KernelBundle\Bundle\AutoConfigurableInterface;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\Routing\RouteCollectionBuilder;

class ClarolineDevBundle extends Bundle implements AutoConfigurableInterface
{
    public function supports($environment)
    {
        return 'prod' !== $environment;
    }

    public function configureContainer(ContainerBuilder $container, LoaderInterface $loader)
    {
    }

    public function configureRoutes(RouteCollectionBuilder $routes)
    {
        $routingFile = $this->getPath().'/Resources/config/routing.yml';
        if (file_exists($routingFile)) {
            $routes->import($routingFile);
        }
    }
}
