<?php

namespace Claroline\MigrationBundle\DependencyInjection;

use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;

/**
 * Loads the services configuration file.
 */
class ClarolineMigrationExtension extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $locator = new FileLocator(__DIR__ . '/../Resources/config');
        $loader = new XmlFileLoader($container, $locator);
        $loader->load('services.xml');
    }
}
