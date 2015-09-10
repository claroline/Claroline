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
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Entity\Widget\WidgetDisplayConfig;
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

    public function import(array $array, $workspace)
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

                $widgetConfig = new WidgetDisplayConfig();
                if ($widget['widget']['row'])    $widgetConfig->setRow($widget['widget']['row']);
                if ($widget['widget']['column']) $widgetConfig->setColumn($widget['widget']['column']);
                if ($widget['widget']['width'])  $widgetConfig->setWidth($widget['widget']['width']);
                if ($widget['widget']['height']) $widgetConfig->setHeight($widget['widget']['height']);
                if ($widget['widget']['color'])  $widgetConfig->setColor($widget['widget']['color']);
                $widgetConfig->setWorkspace($workspace);
                $widgetConfig->setWidgetInstance($widgetInstance);
                $this->om->persist($widgetConfig);

                $widgetHomeTabConfig = new WidgetHomeTabConfig();
                $widgetHomeTabConfig->setWidgetInstance($widgetInstance);
                $widgetHomeTabConfig->setHomeTab($homeTab);
                $widgetHomeTabConfig->setWorkspace($this->getWorkspace());
                $widgetHomeTabConfig->setType('workspace');
                $widgetHomeTabConfig->setVisible(true);
                $widgetHomeTabConfig->setLocked(false);
                $widgetHomeTabConfig->setWidgetOrder($widgetOrder);
                $this->om->persist($widgetHomeTabConfig);

                $importer = $this->getImporterByName($widget['widget']['type']);

                if (isset($widget['widget']['data']) && $importer) {
                    $widgetdata = $widget['widget']['data'];
                    $importer->import($widgetdata, $widgetInstance);
                }

                $widgetOrder++;
            }

            $homeTabOrder++;
        }
    }

    public function export(Workspace $workspace, array &$files, $object)
    {
        $homeTabs = $this->container->get('claroline.manager.home_tab_manager')
            ->getWorkspaceHomeTabConfigsByWorkspace($workspace);
        $tabs = [];

        foreach ($homeTabs as $homeTab) {
            $widgets = [];
            $widgetConfigs = $this->container->get('claroline.manager.home_tab_manager')
                ->getWidgetConfigsByWorkspace($homeTab->getHomeTab(), $workspace);

            foreach ($widgetConfigs as $widgetConfig) {
                $data = [];
                $importer = $this->getImporterByName($widgetConfig->getWidgetInstance()->getWidget()->getName());

                if ($importer) {
                    $data = $importer->export($workspace, $files, $widgetConfig->getWidgetInstance());
                }

                $widgetDisplayConfigs = $this->container->get('claroline.manager.widget_manager')->getWidgetDisplayConfigsByWorkspaceAndWidgets(
                    $workspace,
                    array($widgetConfig->getWidgetInstance())
                );

                $widgetDisplayConfig = isset($widgetDisplayConfigs[0]) ? $widgetDisplayConfigs[0]: null;

                //export the widget content here
                $widgetData = array('widget' => array(
                    'name'   => $widgetConfig->getWidgetInstance()->getName(),
                    'type'   => $widgetConfig->getWidgetInstance()->getWidget()->getName(),
                    'data'   => $data,
                    'row'    => $widgetDisplayConfig ? $widgetDisplayConfig->getRow(): null,
                    'column' => $widgetDisplayConfig ? $widgetDisplayConfig->getColumn(): null,
                    'width'  => $widgetDisplayConfig ? $widgetDisplayConfig->getWidth(): null,
                    'height' => $widgetDisplayConfig ? $widgetDisplayConfig->getHeight(): null,
                    'color'  => $widgetDisplayConfig ? $widgetDisplayConfig->getColor(): null
                ));

                $widgets[] = $widgetData;
            }

            $tabs[] = array('tab' => array(
                'name' => $homeTab->getHomeTab()->getName(),
                'widgets' => $widgets
            ));
        }

        return $tabs;
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
