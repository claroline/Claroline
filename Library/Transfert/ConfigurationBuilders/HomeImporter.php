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

use Claroline\CoreBundle\Library\Transfert\Importer;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\Processor;

class HomeImporter extends Importer implements ConfigurationInterface
{
    public function  getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('data');
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
                                                ->variableNode('data')->end()
                                                ->arrayNode('import')
                                                    ->prototype('array')
                                                        ->children()
                                                            ->scalarNode('path')->end()
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
            ->end()
        ->end();
    }

    public function validate(array $data)
    {
        $processor = new Processor();
        $this->result = $processor->processConfiguration($this, $data);

        foreach ($data as $tabs) {
            foreach ($tabs as $tab) {
                if (isset($tab['tab']['widgets'])) {
                    foreach ($tab['tab']['widgets'] as $widgets) {
                        foreach ($widgets as $widget) {
                            $importer = $this->getImporterByName($widget['type']);
                            $importer->validate($widget['data']);
                        }
                    }
                }
            }
        }
    }

    public function import(array $array)
    {

    }

    public function getName()
    {
        return 'home';
    }
}