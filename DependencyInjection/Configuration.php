<?php
namespace Icap\NotificationBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('icap_notification');

        $rootNode
            ->children()
                ->scalarNode('user_class')->isRequired()->end()
                ->scalarNode('resource_class')->isRequired()->end()
                ->scalarNode('default_layout')->isRequired()->end()
                ->integerNode('max_per_page')->defaultValue(50)->end()
                ->integerNode('dropdown_items')->defaultValue(10)->end()
                ->scalarNode('system_name')->isRequired()->end()
            ->end()
        ;

        return $treeBuilder;
    }
}