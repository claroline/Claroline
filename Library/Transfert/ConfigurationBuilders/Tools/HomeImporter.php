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

use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use JMS\DiExtraBundle\Annotation as DI;
use Claroline\CoreBundle\Event\ValidateToolConfigEvent;
use Symfony\Component\Config\Definition\Processor;
use Claroline\CoreBundle\Library\Transfert\ToolImporter;
use Symfony\Component\Yaml\Yaml;

/**
 * @DI\Service("claroline.home_tool_config_builder")
 * @DI\Tag("claroline.importer")
 */
class HomeImporter extends ToolImporter implements ConfigurationInterface
{
    private $result;

    public function  getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('tabs');
        $this->addHomeSection($rootNode);

        return $treeBuilder;
    }

    /**
     * @DI\Observe("create_form_directory")
     */
    public function getConfigBuilder(ValidateToolConfigEvent $event)
    {
        $event->setConfigurationBuilder($this->getConfigTreeBuilder());
        $event->stopPropagation();
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
                                                    ->scalarNode('name')->isRequired()->end()
                                                    ->scalarNode('type')->isRequired()->end()
                                                    ->scalarNode('config')->isRequired()->end()
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
        return $type == 'yml' ? true: false;
    }

    public function validate(array $data)
    {
        $processor = new Processor();
        $this->result = $processor->processConfiguration($this, $data);
        //home widget validations
        foreach ($data['tabs'] as $tab) {
            foreach ($tab['tab'] as $widget) {
                $toolImporter = null;

                if (isset ($tab['tab']['widgets'])) {
                    foreach ($tab['tab']['widgets'] as $widget) {
                        foreach ($this->listImporters as $importer) {
                            if ($importer->getName() == $widget['widget']['type']) {
                                $toolImporter = $importer;
                            }
                        }

                        if (isset ($widget['widget']['config']) && $toolImporter) {
                            $ds = DIRECTORY_SEPARATOR;
                            $filepath = $this->getRootPath() . $ds . $widget['widget']['config'];
                            var_dump($filepath);
                            //@todo error handling if path doesn't exists
                            $widgetdata =  Yaml::parse(file_get_contents($filepath));
                            $importer->validate($widgetdata);
                        }

                        if (isset($widget['widget']['data']) && $toolImporter) {
                            $widgetdata = $widget['widget']['data'];
                            $importer->validate($widgetdata);
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