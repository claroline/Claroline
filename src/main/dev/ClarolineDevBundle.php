<?php

namespace Claroline\DevBundle;

use Claroline\KernelBundle\Bundle\AutoConfigurableInterface;
use Symfony\Bundle\DebugBundle\DebugBundle;
use Symfony\Bundle\WebProfilerBundle\WebProfilerBundle;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

class ClarolineDevBundle extends Bundle implements AutoConfigurableInterface
{
    public function supports(string $environment): bool
    {
        return in_array($environment, ['dev', 'test']);
    }

    public function getRequiredBundles(string $environment): array
    {
        return [
            new WebProfilerBundle(),
            new DebugBundle(),
        ];
    }

    public function configureContainer(ContainerBuilder $container, LoaderInterface $loader): void
    {
        $loader->load($this->getPath().'/Resources/config/suggested/web_profiler.yml');
    }

    public function configureRoutes(RoutingConfigurator $routes): void
    {
        $routes->import($this->getPath().'/Resources/config/routing.yml');
    }
}
