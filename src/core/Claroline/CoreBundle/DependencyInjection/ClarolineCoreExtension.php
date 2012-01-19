<?php

namespace Claroline\CoreBundle\DependencyInjection;

use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class ClarolineCoreExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $locator = new FileLocator(__DIR__ . '/../Resources/config/services');
        $loader = new YamlFileLoader($container, $locator);
        $loader->load('plugin.yml');
        $loader->load('resource.yml');
        $loader->load('user.yml');
        $loader->load('security.yml');
        $loader->load('workspace.yml');
        $loader->load('admin.yml');
        $loader->load('desktop.yml');
        $loader->load('home.yml');
        $loader->load('install.yml');
        $loader->load('common.yml');
    }
}