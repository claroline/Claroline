<?php

namespace Claroline\CoreBundle\DependencyInjection;

use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;

class ClarolineCoreExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $locator = new FileLocator(__DIR__ . '/../Resources/config/services');
        $loader = new YamlFileLoader($container, $locator);
        $loader->load('listeners.yml');
        $loader->load('controllers.yml');
        $loader->load('installation.yml');
        $loader->load('browsing.yml');
        $loader->load('resource.yml');
        $loader->load('user.yml');
        $loader->load('security.yml');
        $loader->load('workspace.yml');
    }
}