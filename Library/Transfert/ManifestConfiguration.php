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
        $this->addPropertiesSection($rootNode);
        $this->addCustomeRolesSection($rootNode);
        $this->addmembersSection($rootNode);
        $this->addResourceSection($rootNode);
        $this->addToolsSection($rootNode);

        return $treeBuilder;
    }

    private function addPropertiesSection($rootNode)
    {
        $rootNode
            ->children()
                ->arrayNode('properties')
                    ->children()
                        ->scalarNode('name')->isRequired()->end()
                        ->scalarNode('code')->end()
                        ->booleanNode('visible')->isRequired()->end()
                        ->scalarNode('selfregistration')->isRequired()->end()
                    ->end()
                ->end()
            ->end();
    }

    private function addCustomeRolesSection($rootNode)
    {
        $rootNode
            ->children()
                ->arrayNode('customRoles')
                    ->prototype('array')
                    ->children()
                        ->scalarNode('role')->end()
                    ->end()
                    ->end()
                ->end()
            ->end();
    }

    private function addMembersSection($rootNode)
    {
        $rootNode
            ->children()
                ->arrayNode('members')
                    ->children()
                        ->arrayNode('owner')
                            ->children()
                                ->scalarNode('first_name')->isRequired()->end()
                                ->scalarNode('last_name')->isRequired()->end()
                                ->scalarNode('username')->isRequired()->end()
                                ->scalarNode('locale')->isRequired()->end()
                                ->scalarNode('administrative_code')->end()
                                ->scalarNode('phone')->end()
                                ->scalarNode('picture')->end()
                            ->end()
                        ->end()
                    ->end()
                    ->children()
                        ->arrayNode('platformGroups')
                            ->prototype('array')
                                ->children()
                                    ->arrayNode('group')
                                        ->children()
                                            ->scalarNode('name')->end()
                                            ->arrayNode('users')
                                                ->prototype('scalar')->end()
                                                    ->children()
                                                        ->scalarNode('user')->end()
                                                    ->end()
                                            ->end()
                                        ->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                    ->children()
                            ->arrayNode('users')
                                ->children()
                                    ->scalarNode('user')
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ->end();
    }

    private function addResourceSection($rootNode)
    {
        $rootNode
        ->children()
            ->arrayNode('resources')
                ->children()
                    ->arrayNode('directories')
                        ->children()
                            ->arrayNode('directory')
                                ->children()
                                    ->scalarNode('name')->end()
                                    ->scalarNode('creator')->end()
                                    ->scalarNode('parent')->end()
                                    ->arrayNode('rights')
                                        ->children()
                                            ->arrayNode('role')
                                                ->children()
                                                    ->scalarNode('name')->end()
                                                    ->scalarNode('open')->end()
                                                    ->scalarNode('edit')->end()
                                                    ->arrayNode('create')
                                                        ->children()
                                                            ->scalarNode('resource')->end()
                                                        ->end()
                                                    ->end()
                                                ->end()
                                            ->end()
                                        ->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
                ->children()
                     ->arrayNode('items')
                         ->children()
                            ->arrayNode('item')
                                ->children()
                                    ->scalarNode('name')->end()
                                    ->scalarNode('creator')->end()
                                    ->scalarNode('parent')->end()
                                    ->scalarNode('type')->end()
                                ->end()
                            ->children()
                                ->arrayNode('rights')
                                    ->children()
                                        ->arrayNode('role')
                                        ->children()
                                            ->scalarNode('name')->end()
                                            ->scalarNode('foo')->end()
                                            ->scalarNode('open')->end()
                                            ->scalarNode('edit')->end()
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

    public function addToolsSection($rootNode)
    {
        $rootNode
            ->children()
                ->arrayNode('tools')
                    ->prototype('array')
                        ->children()
                            ->scalarNode('name')->end()
                        ->end()
                ->end()
            ->end();
    }
} 