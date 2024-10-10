<?php

namespace Icap\LessonBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\DependencyInjection\Extension\Extension;

/**
 * This is the class that loads and manages your bundle configuration.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class IcapLessonExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $serviceLoader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $serviceLoader->load('services.yml');
    }
}
