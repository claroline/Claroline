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

class RolesConfigurationBuilder implements ConfigurationInterface
{
    public function  getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('users');
        $this->addRolesSection($rootNode);

        return $treeBuilder;
    }

    public function addRolesSection($rootNode)
    {
        $rootNode
            ->prototype('array')
                ->children()
                    ->arrayNode('role')
                        ->children()
                            ->scalarNode('name')->isRequired()->end()
                            ->scalarNode('translation')->isRequired()->end()
                            ->booleanNode('is_base_role')->isRequired()->end()
                        ->end()
                    ->end()
                ->end()
            ->end();
    }
}