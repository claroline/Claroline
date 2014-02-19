<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Library\Transfert\ConfigurationBuilders;

use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;

class HomeConfigurationBuilder implements ConfigurationInterface
{
    public function  getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('tabs');
        $this->addHomeSection($rootNode);

        return $treeBuilder;
    }

    public function addHomeSection($rootNode)
    {
        $rootNode
            ->prototype('array')
                ->children()
                    ->arrayNode('tab')
                        ->children()
                            ->scalarNode('name')->end()
                            ->arrayNode('widgets')
                                ->prototype('array')
                                    ->children()
                                        ->arrayNode('widget')
                                            ->children()
                                                ->scalarNode('name')->isRequired()->end()
                                                ->scalarNode('type')->end()
                                                ->scalarNode('config')->end()
                                            ->end()
                                        ->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();

    }
}