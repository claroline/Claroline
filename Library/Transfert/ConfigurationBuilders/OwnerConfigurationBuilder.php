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

class OwnerConfigurationBuilder implements ConfigurationInterface
{
    public function  getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('owner');
        $this->addOwnerSection($rootNode);

        return $treeBuilder;
    }

    public function addOwnerSection($rootNode)
    {
        $rootNode
            ->children()
                ->scalarNode('first_name')->isRequired()->end()
                ->scalarNode('last_name')->isRequired()->end()
                ->scalarNode('username')->isRequired()->end()
                ->scalarNode('password')->isRequired()->end()
                ->scalarNode('mail')->isRequired()->end()
                ->scalarNode('code')->isRequired()->end()
                ->arrayNode('roles')
                    ->prototype('array')
                        ->children()
                            ->scalarNode('name')->isRequired()->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ->end();
    }
}