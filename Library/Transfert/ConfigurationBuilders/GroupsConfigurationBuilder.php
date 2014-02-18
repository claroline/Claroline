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

class GroupsConfigurationBuilder implements ConfigurationInterface
{
    public function  getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('groups');
        $this->addGroupsSection($rootNode);

        return $treeBuilder;
    }

    public function addGroupsSection($rootNode)
    {
        $rootNode
            ->prototype('array')
                ->children()
                    ->arrayNode('group')
                        ->children()
                           ->scalarNode('name')->isRequired()->end()
                           ->arrayNode('users')
                                ->prototype('array')
                                    ->children()
                                        ->scalarNode('username')->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->arrayNode('roles')
                            ->prototype('array')
                                ->children()
                                    ->scalarNode('name')->isRequired()->end()
                                ->end()
                            ->end()
                        ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();
    }
}