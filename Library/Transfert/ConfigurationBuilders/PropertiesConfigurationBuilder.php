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

class PropertiesConfigurationBuilder implements ConfigurationInterface
{
    public function  getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('properties');
        $this->addPropertiesSection($rootNode);

        return $treeBuilder;
    }

    public function addPropertiesSection($rootNode)
    {
        $rootNode
            ->children()
                ->scalarNode('name')->isRequired()->end()
                ->scalarNode('code')->isRequired()->end()
                ->booleanNode('visible')->isRequired()->end()
                ->booleanNode('selfregistration')->isRequired()->end()
                ->booleanNode('selfUnregistration')->isRequired()->end()
                ->scalarNode('owner')->end()
                ->end()
            ->end();

    }


} 