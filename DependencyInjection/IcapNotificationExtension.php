<?php
namespace Icap\NotificationBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\DependencyInjection\Extension\InvalidArgumentException;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

class IcapNotificationExtension extends Extension
{

    /**
     * Loads a specific configuration.
     *
     * @param array $configs An array of configuration values
     * @param ContainerBuilder $container A ContainerBuilder instance
     *
     * @throws InvalidArgumentException When provided tag is not defined in this extension
     *
     * @api
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        foreach (array('user_class', 'resource_class', 'default_layout', 'max_per_page', 'dropdown_items', 'system_name') as $attribute) {
            $container->setParameter("icap_notification.".$attribute, $config[$attribute]);
        }

        $locator = new FileLocator(__DIR__ . '/../Resources/config/services');
        $loader = new YamlFileLoader($container, $locator);
        $loader->load('services.yml');
    }
}