<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Library\Transfert;


use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;

class ManifestConfiguration implements ConfigurationInterface {

    public function  getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('workspace');
        $rootNode
            ->children()
                ->arrayNode('properties')
                    ->children()
                        ->scalarNode('name')->isRequired()->end()
                        ->scalarNode('code')->isRequired()->end()
                        ->booleanNode('visible')->isRequired()->end()
                        ->scalarNode('selfregistration')->isRequired()->end()
                     ->end()
                ->end()
                ->arrayNode('owner')
                    ->children()
                        ->scarlarNode('first_name')->isRequired->end()
                        ->scarlarNode('last_name')->isRequired->end()
                        ->scarlarNode('username')->isRequired->end()
                        ->scarlarNode('password')->isRequired->end()
                        ->scarlarNode('locale')->isRequired->end()
                        ->scarlarNode('administrative_code')->end()
                        ->scarlarNode('phone')->end()
                        ->scarlarNode('picture')->end()
                    ->end()
                ->end()
            ->end();


        return $treeBuilder;
    }
} 