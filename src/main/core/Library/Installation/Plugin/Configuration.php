<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Library\Installation\Plugin;

use Claroline\CoreBundle\Entity\Resource\AbstractResource;
use Claroline\KernelBundle\Bundle\PluginBundle;
use Symfony\Component\Config\Definition\Builder\NodeBuilder;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    private $plugin;
    private $listNames;
    private $listTools;
    private $listWidgets;
    private $updateMode;
    private $listResourceActions;

    public function __construct(PluginBundle $plugin, array $resourceNames, array $listTools, array $listResourceActions, array $listWidgets)
    {
        $this->plugin = $plugin;
        $this->listNames = $resourceNames;
        $this->listTools = $listTools;
        $this->listResourceActions = $listResourceActions;
        $this->listWidgets = $listWidgets;
        $this->updateMode = false;
    }

    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('config');
        $rootNode = $treeBuilder->getRootNode();
        //maybe remove that line and edit plugins later
        $pluginSection = $rootNode->children('plugin');
        $this->addWidgetSection($pluginSection);
        $this->addDataSourceSection($pluginSection);
        $this->addResourceSection($pluginSection);
        $this->addResourceActionSection($pluginSection);
        $this->addToolSection($pluginSection);
        $this->addThemeSection($pluginSection);
        $this->addAdminToolSection($pluginSection);
        $this->addTemplateSection($pluginSection);
        $this->addResourceIconsSection($pluginSection);

        return $treeBuilder;
    }

    private function addResourceSection(NodeBuilder $pluginSection)
    {
        $plugin = $this->plugin;
        $pluginFqcn = get_class($plugin);
        $resourceFile = $plugin->getConfigFile();
        $listNames = $this->listNames;
        $updateMode = $this->isInUpdateMode();

        $pluginSection
            ->arrayNode('resources')
                ->prototype('array')
                    ->children()
                       ->scalarNode('name')
                            ->isRequired()
                                ->validate()
                                    ->ifTrue(
                                        function ($v) use ($listNames, $updateMode) {
                                            return !$updateMode && !call_user_func_array(
                                                __CLASS__.'::isNameAlreadyExist',
                                                [$v, $listNames]
                                            );
                                        }
                                    )
                                    ->thenInvalid($pluginFqcn.' : the ressource type name already exists')
                                ->end()
                            ->end()
                       ->scalarNode('class')
                            ->isRequired()
                                ->validate()
                                    ->ifTrue(
                                        function ($v) use ($plugin) {
                                            return !call_user_func_array(
                                                __CLASS__.'::isResourceClassLoadable',
                                                [$v, $plugin]
                                            );
                                        }
                                    )
                                    ->thenInvalid($pluginFqcn." : %s (declared in {$resourceFile}) was not found.")
                                ->end()
                                ->validate()
                                    ->ifTrue(
                                        function ($v) {
                                            return !call_user_func_array(
                                                __CLASS__.'::isAbstractResourceExtended',
                                                [$v]
                                            );
                                        }
                                    )
                                    ->thenInvalid(
                                        $pluginFqcn." : %s (declared in {$resourceFile}) must extend  "
                                        ."'Claroline\\CoreBundle\\Entity\\Resource\\AbstractResource'."
                                    )
                                ->end()
                            ->end()
                       ->scalarNode('exportable')->defaultValue(false)->end()
                       ->arrayNode('tags')
                           ->prototype('scalar')->end()
                           ->defaultValue([])
                       ->end()
                       ->arrayNode('actions')
                         ->prototype('array')
                            ->children()
                                ->scalarNode('name')->isRequired()->end()
                                ->scalarNode('group')->defaultValue(null)->end()
                                ->scalarNode('decoder')->defaultValue('open')->end()
                                ->arrayNode('scope')
                                    ->prototype('scalar')->end()
                                    ->defaultValue(['object'])
                                ->end()
                                ->arrayNode('api')
                                    ->prototype('scalar')->end()
                                ->end()
                                ->booleanNode('default')->defaultValue(false)->end()
                                ->booleanNode('recursive')->defaultValue(false)->end()
                            ->end()
                         ->end()
                       ->end()
                       ->arrayNode('default_rights')
                         ->prototype('array')
                            ->children()
                                ->scalarNode('name')->end()
                            ->end()
                         ->end()
                       ->end()
                    ->end()
                 ->end()
            ->end()
        ->end()->end();
    }

    public function addResourceActionSection(NodeBuilder $pluginSection)
    {
        $plugin = $this->plugin;
        $pluginFqcn = get_class($plugin);
        $listResourceActions = $this->listResourceActions;
        $updateMode = $this->isInUpdateMode();

        $pluginSection
            ->arrayNode('resource_actions')
                ->prototype('array')
                    ->children()
                        ->scalarNode('name')
                            ->isRequired()
                            ->cannotBeEmpty()
                            ->validate()
                                ->ifTrue(
                                    function ($v) use ($listResourceActions, $updateMode) {
                                        return !$updateMode && in_array($v, $listResourceActions);
                                    }
                                )
                                ->thenInvalid($pluginFqcn.' : the resource action name already exists')
                            ->end()
                        ->end()
                        ->scalarNode('name')->isRequired()->end()
                        ->scalarNode('group')->defaultValue(null)->end()
                        ->scalarNode('decoder')->defaultValue('open')->end()
                        ->arrayNode('scope')
                            ->prototype('scalar')->end()
                            ->defaultValue(['object'])
                        ->end()
                        ->arrayNode('api')
                            ->prototype('scalar')->end()
                        ->end()
                        ->booleanNode('default')->defaultValue(false)->end()
                        ->booleanNode('recursive')->defaultValue(false)->end()
                        ->scalarNode('resource_type')->defaultNull()->end() // todo : should be an array
                    ->end()
                ->end()
            ->end()
        ->end()->end();
    }

    private function addWidgetSection(NodeBuilder $pluginSection)
    {
        $widgets = $this->listWidgets;
        $plugin = $this->plugin;
        $pluginFqcn = get_class($plugin);
        $updateMode = $this->isInUpdateMode();

        $pluginSection
            ->arrayNode('widgets')
                ->prototype('array')
                    ->children()
                        ->scalarNode('name')
                            ->isRequired()
                                ->validate()
                                ->ifTrue(
                                    function ($v) use ($pluginFqcn, $widgets, $updateMode) {
                                        return !$updateMode && !call_user_func_array(
                                            __CLASS__.'::isNameAlreadyExist',
                                            [$pluginFqcn.'-'.$v, $widgets]
                                        );
                                    }
                                )
                                ->thenInvalid($pluginFqcn.' : the widget name already exists')
                            ->end()
                        ->end()
                        ->scalarNode('class')->defaultValue(null)->end()
                        ->arrayNode('sources')
                            ->prototype('scalar')->end()
                        ->end()
                        ->booleanNode('exportable')->defaultFalse()->end()
                        ->arrayNode('context')
                            ->prototype('scalar')->end()
                            ->defaultValue(['desktop', 'workspace', 'home', 'administration'])
                        ->end()
                        ->arrayNode('tags')
                            ->prototype('scalar')->end()
                            ->defaultValue([])
                        ->end()
                    ->end()
                ->end()
            ->end()
        ->end()->end();
    }

    private function addDataSourceSection(NodeBuilder $pluginSection)
    {
        $pluginSection
            ->arrayNode('data_sources')
                ->prototype('array')
                    ->children()
                        ->scalarNode('name')->isRequired()->end()
                        ->scalarNode('type')->isRequired()->end()
                        ->arrayNode('context')
                            ->prototype('scalar')->end()
                            ->defaultValue(['desktop', 'workspace', 'administration'])
                        ->end()
                        ->arrayNode('tags')
                            ->prototype('scalar')->end()
                            ->defaultValue([])
                        ->end()
                    ->end()
                ->end()
            ->end();
    }

    private function addToolSection(NodeBuilder $pluginSection)
    {
        $tools = $this->listTools;
        $plugin = $this->plugin;
        $pluginFqcn = get_class($plugin);
        $updateMode = $this->isInUpdateMode();
        $pluginSection
            ->arrayNode('tools')
                ->prototype('array')
                    ->children()
                        ->scalarNode('name')
                            ->isRequired()
                                ->validate()
                                ->ifTrue(
                                    function ($v) use ($pluginFqcn, $tools, $updateMode) {
                                        return !$updateMode && !call_user_func_array(
                                            __CLASS__.'::isNameAlreadyExist',
                                            [$pluginFqcn.'-'.$v, $tools]
                                        );
                                    }
                                )
                                ->thenInvalid($pluginFqcn.' : the tool name already exists')
                            ->end()
                        ->end()
                        ->booleanNode('is_displayable_in_workspace')->isRequired()->end()
                        ->booleanNode('is_displayable_in_desktop')->isRequired()->end()
                        ->scalarNode('class')->end()
                        ->scalarNode('is_exportable')->defaultValue(false)->end()
                        ->scalarNode('is_desktop_required')->defaultValue(false)->end()
                        ->scalarNode('is_workspace_required')->defaultValue(false)->end()
                        ->scalarNode('is_configurable_in_workspace')->defaultValue(false)->end()
                        ->scalarNode('is_configurable_in_desktop')->defaultValue(false)->end()
                        ->scalarNode('is_locked_for_admin')->defaultValue(false)->end()
                        ->scalarNode('is_anonymous_excluded')->defaultValue(true)->end()
                        ->arrayNode('tool_rights')
                            ->prototype('array')
                                ->children()
                                    ->scalarNode('name')->isRequired()->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ->end()->end();
    }

    private function addThemeSection(NodeBuilder $pluginSection)
    {
        $pluginSection
            ->arrayNode('themes')
                ->prototype('array')
                    ->children()
                      ->scalarNode('name')->isRequired()->end()
                    ->end()
                ->end()
            ->end()
        ->end()->end();
    }

    private function addAdminToolSection(NodeBuilder $pluginSection)
    {
        $pluginSection
            ->arrayNode('admin_tools')
                ->prototype('array')
                    ->children()
                        ->scalarNode('name')->isRequired()->end()
                        ->scalarNode('class')->end()
                    ->end()
                ->end()
            ->end()
        ->end()->end();
    }

    private function addTemplateSection(NodeBuilder $pluginSection)
    {
        $pluginSection
            ->arrayNode('templates')
                ->prototype('array')
                    ->children()
                        ->scalarNode('name')->isRequired()->end()
                        ->scalarNode('type')->isRequired()->end()
                        ->arrayNode('placeholders')
                            ->prototype('scalar')->end()
                            ->defaultValue([])
                        ->end()
                    ->end()
                ->end()
            ->end();
    }

    private function addResourceIconsSection(NodeBuilder $pluginSection)
    {
        $pluginSection
            ->arrayNode('resource_icons')
                ->prototype('array')
                    ->children()
                        ->scalarNode('name')->isRequired()->end()
                        ->arrayNode('mime_types')
                            ->prototype('scalar')->end()
                            ->defaultValue([])
                        ->end()
                    ->end()
                ->end()
            ->end();
    }

    public static function isResourceClassLoadable($v)
    {
        return class_exists($v);
    }

    public static function isAbstractResourceExtended($v)
    {
        return (new $v()) instanceof AbstractResource;
    }

    public static function isNameAlreadyExist($v, $listNames)
    {
        return !in_array($v, $listNames);
    }

    /**
     * @param $updateMode
     *
     * @return Configuration
     */
    public function setUpdateMode($updateMode)
    {
        $this->updateMode = $updateMode;

        return $this;
    }

    /**
     * @return bool
     */
    public function isInUpdateMode()
    {
        return $this->updateMode;
    }
}
