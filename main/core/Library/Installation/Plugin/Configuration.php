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
use Claroline\CoreBundle\Library\DistributionPluginBundle;
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

    public function __construct(DistributionPluginBundle $plugin, array $resourceNames, array $listTools, array $listResourceActions, array $listWidgets)
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
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('config');
        //maybe remove that line and edit plugins later
        $pluginSection = $rootNode->children('plugin');
        $this->addGeneralSection($pluginSection);
        $this->addWidgetSection($pluginSection);
        $this->addResourceSection($pluginSection);
        $this->addResourceActionSection($pluginSection);
        $this->addToolSection($pluginSection);
        $this->addThemeSection($pluginSection);
        $this->addAdminToolSection($pluginSection);
        $this->addAdditionalActionSection($pluginSection);

        return $treeBuilder;
    }

    private function addGeneralSection(NodeBuilder $pluginSection)
    {
        $plugin = $this->plugin;
        $pluginFqcn = get_class($plugin);
        $imgFolder = $plugin->getImgFolder();
        $ds = DIRECTORY_SEPARATOR;

        $pluginSection
            ->booleanNode('has_options')
                ->defaultFalse()
            ->end()
            ->scalarNode('icon')
                ->validate()
                    ->ifTrue(
                        function ($v) use ($plugin) {
                            return !call_user_func_array(
                                __CLASS__.'::isIconValid',
                                [$v, $plugin]
                            );
                        }
                    )
                    ->thenInvalid($pluginFqcn." : this file was not found ({$imgFolder}{$ds}%s)")
                ->end()
            ->end()
        ->end();
    }

    private function addResourceSection(NodeBuilder $pluginSection)
    {
        $plugin = $this->plugin;
        $pluginFqcn = get_class($plugin);
        $resourceFile = $plugin->getConfigFile();
        $imgFolder = $plugin->getImgFolder();
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
                       ->booleanNode('is_visible')->end()    // must be removed
                       ->booleanNode('is_browsable')->end()  // must be removed
                       ->scalarNode('is_exportable')->defaultValue(false)->end()
                       ->scalarNode('icon')
                           ->validate()
                                ->ifTrue(
                                    function ($v) use ($plugin) {
                                        return !call_user_func_array(
                                            __CLASS__.'::isResourceIconValid',
                                            [$v, $plugin]
                                        );
                                    }
                                )
                                ->thenInvalid($pluginFqcn." : this file was not found ({$imgFolder}%s)")
                           ->end()
                       ->end()
                       ->arrayNode('actions')
                         ->prototype('array')
                            ->children()
                                ->scalarNode('name')->isRequired()->end()
                                ->scalarNode('menu_name')->end()
                                ->booleanNode('is_form')->defaultFalse()->end()
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
                       ->arrayNode('activity_rules')
                         ->prototype('array')
                            ->children()
                                ->scalarNode('action')->isRequired()->end()
                                ->scalarNode('type')->end()
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
                        ->booleanNode('is_form')->defaultFalse()->end()
                        ->booleanNode('is_async')->defaultFalse()->end()
                        ->booleanNode('is_custom')->defaultFalse()->end()
                        ->scalarNode('class')->defaultNull()->end()
                        ->scalarNode('group')->defaultNull()->end()
                        ->scalarNode('value')->defaultValue('open')->end()
                        ->scalarNode('resource_type')->defaultNull()->end()
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
        $imgFolder = $plugin->getImgFolder();
        $ds = DIRECTORY_SEPARATOR;
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
                        ->booleanNode('is_configurable')->isRequired()->end()
                        ->scalarNode('is_exportable')->defaultValue(false)->end()
                        ->scalarNode('default_width')->defaultValue(4)->end()
                        ->scalarNode('default_height')->defaultValue(3)->end()
                        ->scalarNode('is_displayable_in_workspace')->defaultValue(true)->end()
                        ->scalarNode('is_displayable_in_desktop')->defaultValue(true)->end()
                        ->scalarNode('icon')
                            ->validate()
                            ->ifTrue(
                                function ($v) use ($plugin) {
                                    return !call_user_func_array(
                                        __CLASS__.'::isIconValid',
                                        [$v, $plugin]
                                    );
                                }
                            )
                            ->thenInvalid($pluginFqcn." : this file was not found ({$imgFolder}{$ds}%s)")
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ->end()->end();
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
                        //@todo remove the following line later
                        ->scalarNode('has_options')->defaultValue(false)->end()
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
                                    ->scalarNode('granted_icon_class')->isRequired()->end()
                                    ->scalarNode('denied_icon_class')->isRequired()->end()
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

    private function addAdditionalActionSection(NodeBuilder $pluginSection)
    {
        $pluginSection
            ->arrayNode('additional_action')
                ->prototype('array')
                    ->children()
                        ->scalarNode('action')->isRequired()->end()
                        ->scalarNode('type')->isRequired()->end()
                        ->scalarNode('displayed_name')->isRequired()->end()
                        ->scalarNode('class')->isRequired()->end()
                    ->end()
                ->end()
            ->end()
        ->end()->end();
    }

    public static function isResourceClassLoadable($v)
    {
        return class_exists($v);
    }

    public static function isAbstractResourceExtended($v)
    {
        return (new $v()) instanceof AbstractResource;
    }

    public static function isResourceIconValid($v, $plugin)
    {
        $ds = DIRECTORY_SEPARATOR;
        $imgFolder = $plugin->getImgFolder();
        $expectedImgLocation = $imgFolder.$ds.$ds.$v;

        return file_exists($expectedImgLocation);
    }

    public static function isSmallIconValid($v, $plugin)
    {
        $ds = DIRECTORY_SEPARATOR;
        $imgFolder = $plugin->getImgFolder();
        $expectedImgLocation = $imgFolder.$ds.'small'.$ds.$v;

        return file_exists($expectedImgLocation);
    }

    public static function isIconValid($v, $plugin)
    {
        $ds = DIRECTORY_SEPARATOR;
        $imgFolder = $plugin->getImgFolder();
        $expectedImgLocation = $imgFolder.$ds.$v;

        return file_exists($expectedImgLocation);
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
