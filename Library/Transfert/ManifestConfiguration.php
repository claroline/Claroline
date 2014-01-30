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
/*        $rootNode
              ->children()
                     ->arrayNode('users')
                     ->fixXMLConfig('users','user')
                         ->children()
                             ->arrayNode('user')
                                  ->prototype('scalar')->end()
                             ->end()
                         ->end()
                     ->end()
                 ->end()
            ->end();*/

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
                    ->fixXmlConfig('role')
                    ->children()
                        ->arrayNode('roles')
                            ->prototype('scalar')->end()
                        ->end()
                    ->end()
                ->end()
            ->end();
    }

    private function addmembersSection($rootNode)
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
                            ->fixXMLConfig('platformGroups','group')
                            ->children()
                                ->arrayNode('group')
                                    ->fixXMLConfig('group','name')
                                    ->children()
                                        ->prototype('scalar')->end()
                                        //->scalarNode('name')->end()
                                        /*->arrayNode('users')
                                            ->children()
                                                ->arrayNode('user')
                                                ->end()
                                            ->end()
                                        ->end()*/
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();
                                //->fixXMLConfig('users','user')
/*                                    ->children()
                                        ->scalarNode('name')->end()
                                        ->arrayNode('users')*/
                                           // ->children()
                                                //->arrayNode('user')
                                                    /*->children()
                                                        ->scalarNode('first_name')->isRequired()->end()
                                                        ->scalarNode('last_name')->isRequired()->end()
                                                        ->scalarNode('username')->isRequired()->end()
                                                        ->scalarNode('locale')->isRequired()->end()
                                                        ->scalarNode('administrative_code')->end()
                                                        ->scalarNode('phone')->end()
                                                        ->scalarNode('picture')->end()
                                                    ->end()*/
                                                  //  ->prototype('scalar')->end()
                                                //->end()
                                            //->end()
//                                        /->end()
                                    //->end()
/*                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();*/
    }
} 