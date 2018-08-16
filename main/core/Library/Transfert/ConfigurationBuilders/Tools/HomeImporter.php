<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Library\Transfert\ConfigurationBuilders\Tools;

use Claroline\AppBundle\Persistence\ObjectManager;
//TODO FIXME ! NOT GONNA WORK ANYMORE
use Claroline\CoreBundle\Library\Transfert\Importer;
use Claroline\CoreBundle\Library\Transfert\RichTextInterface;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Processor;

/**
 * @DI\Service("claroline.tool.home_importer")
 * @DI\Tag("claroline.importer")
 */
class HomeImporter extends Importer implements ConfigurationInterface, RichTextInterface
{
    private $om;
    private $container;

    /**
     * @DI\InjectParams({
     *      "om"        = @DI\Inject("claroline.persistence.object_manager"),
     *      "container" = @DI\Inject("service_container")
     * })
     */
    public function __construct(ObjectManager $om, $container)
    {
        $this->om = $om;
        $this->container = $container;
    }

    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('tabs');
        $this->addHomeSection($rootNode);

        return $treeBuilder;
    }

    private function addHomeSection($rootNode)
    {
        $rootNode
            ->prototype('array')
                ->children()
                    ->arrayNode('tab')
                        ->children()
                            ->scalarNode('name')->isRequired()->end()
                                ->arrayNode('widgets')
                                    ->prototype('array')
                                        ->children()
                                            ->arrayNode('widget')
                                                ->children()
                                                    ->scalarNode('name')->defaultNull()->end()
                                                    ->scalarNode('type')->isRequired()->end()
                                                    ->scalarNode('row')->defaultNull()->end()
                                                    ->scalarNode('column')->defaultNull()->end()
                                                    ->scalarNode('width')->defaultNull()->end()
                                                    ->scalarNode('height')->defaultNull()->end()
                                                    ->scalarNode('color')->defaultNull()->end()
                                                    ->variableNode('data')->defaultNull()->end()
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
                ->end();
    }

    public function supports($type)
    {
        return 'yml' === $type ? true : false;
    }

    public function validate(array $data)
    {
        $processor = new Processor();
        $data = $processor->processConfiguration($this, $data);

        //home widget validations
        foreach ($data as &$tab) {
            foreach ($tab['tab'] as &$widgets) {
                $toolImporter = null;
                if (isset($widgets['widgets'])) {
                    foreach ($widgets['widgets'] as $widget) {
                        foreach ($this->getListImporters() as $importer) {
                            if ($widget['widget']['type'] === $importer->getName()) {
                                $toolImporter = $importer;
                            }
                        }

                        if (isset($widget['widget']['data']) && $toolImporter) {
                            $widgetdata = $widget['widget']['data'];
                            $widget['widget']['data'] = $toolImporter->validate($widgetdata);
                        }
                    }
                }
            }
        }

        return $data;
    }

    public function import(array $array, $workspace)
    {
        //not gonna work
    }

    public function export($workspace, array &$files, $object)
    {
        return [];
    }

    public function format($data)
    {
        foreach ($data['data'] as $tab) {
            foreach ($tab['tab']['widgets'] as $widget) {
                $widgetImporter = null;

                foreach ($this->getListImporters() as $importer) {
                    if ($widget['widget']['type'] === $importer->getName()) {
                        $widgetImporter = $importer;
                    }
                }

                if ($widgetImporter instanceof RichTextInterface) {
                    if (isset($widget['widget']['data']) && $widgetImporter) {
                        $widgetdata = $widget['widget']['data'];
                        $widgetImporter->format($widgetdata);
                    }
                }
            }
        }
    }

    public function getName()
    {
        return 'home';
    }
}
