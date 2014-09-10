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

use Claroline\CoreBundle\Entity\Home\HomeTab;
use Claroline\CoreBundle\Entity\Home\HomeTabConfig;
use Claroline\CoreBundle\Entity\Widget\WidgetHomeTabConfig;
use Claroline\CoreBundle\Entity\Widget\WidgetInstance;
use Claroline\CoreBundle\Persistence\ObjectManager;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Config\Definition\Processor;
use Claroline\CoreBundle\Library\Transfert\Importer;
use Claroline\CoreBundle\Library\Transfert\RichTextInterface;

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

    public function  getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('tabs');
        $this->addHomeSection($rootNode);

        return $treeBuilder;
    }

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
        foreach ($data['data'] as $tab) {
            foreach ($tab['tab'] as $widgets) {
                $toolImporter = null;
                if (isset ($widgets['widgets'])) {
                    foreach ($widgets['widgets'] as $widget) {
                        foreach ($this->getListImporters() as $importer) {
                            if ($importer->getName() == $widget['widget']['type']) {
                                $toolImporter = $importer;
                            }
                        }

                        if (isset($widget['widget']['data']) && $toolImporter) {
                            $widgetdata = $widget['widget']['data'];
                            $toolImporter->validate($widgetdata);
                        }
                    }
                }
            }
        }
    }

    public function import(array $array)
    {
        $homeTabOrder = 1;

        foreach ($array['data'] as $tab) {
            $homeTab = new HomeTab();
            $homeTab->setName($tab['tab']['name']);
            $homeTab->setWorkspace($this->getWorkspace());
            $homeTab->setType('workspace');
            $this->om->persist($homeTab);
            $homeTabConfig = new HomeTabConfig();
            $homeTabConfig->setHomeTab($homeTab);
            $homeTabConfig->setType('workspace');
            $homeTabConfig->setWorkspace($this->getWorkspace());
            $homeTabConfig->setLocked(false);
            $homeTabConfig->setVisible(true);
            $homeTabConfig->setTabOrder($homeTabOrder);
            $this->om->persist($homeTabConfig);
            $this->container->get('claroline.manager.home_tab_manager')->insertHomeTabConfig($homeTabConfig);
            $widgetOrder = 1;

            foreach ($tab['tab']['widgets'] as $widget) {
                $widgetType = $this->om->getRepository('ClarolineCoreBundle:Widget\Widget')
                    ->findOneByName($widget['widget']['type']);
                $widgetInstance = new WidgetInstance();
                $widgetInstance->setName($widget['widget']['name']);
                $widgetInstance->setWidget($widgetType);
                $widgetInstance->setWorkspace($this->getWorkspace());
                $widgetInstance->setIsAdmin(false);
                $widgetInstance->setIsDesktop(false);
                $this->om->persist($widgetInstance);

                $widgetHomeTabConfig = new WidgetHomeTabConfig();
                $widgetHomeTabConfig->setWidgetInstance($widgetInstance);
                $widgetHomeTabConfig->setHomeTab($homeTab);
                $widgetHomeTabConfig->setWorkspace($this->getWorkspace());
                $widgetHomeTabConfig->setType('workspace');
                $widgetHomeTabConfig->setVisible(true);
                $widgetHomeTabConfig->setLocked(false);
                $widgetHomeTabConfig->setWidgetOrder($widgetOrder);
                $this->om->persist($widgetHomeTabConfig);

                foreach ($this->getListImporters() as $importer) {
                    if ($importer->getName() == $widget['widget']['type']) {
                        $toolImporter = $importer;
                    }
                }

                if (isset($widget['widget']['data']) && $toolImporter) {
                    $widgetdata = $widget['widget']['data'];
                    $toolImporter->import($widgetdata, $widgetInstance);
                }

                $widgetOrder++;
            }

            $homeTabOrder++;
        }
    }

    public function format($data)
    {
        foreach ($data['data'] as $tab) {
            foreach ($tab['tab']['widgets'] as $widget) {
                $widgetImporter = null;

                foreach ($this->getListImporters() as $importer) {
                    if ($importer->getName() == $widget['widget']['type']) {
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
