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
use Claroline\KernelBundle\Bundle\PluginBundleInterface;
use Symfony\Component\Config\Definition\Builder\NodeBuilder;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    private PluginBundleInterface $plugin;
    private array $listNames = [];
    private array $listTools = [];
    private array $listWidgets = [];
    private bool $updateMode = false;

    public function __construct(
        PluginBundleInterface $plugin,
        array $resourceNames,
        array $listTools,
        array $listWidgets
    ) {
        $this->plugin = $plugin;
        $this->listNames = $resourceNames;
        $this->listTools = $listTools;
        $this->listWidgets = $listWidgets;
        $this->updateMode = false;
    }

    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('config');
        $rootNode = $treeBuilder->getRootNode();
        $pluginSection = $rootNode->children('plugin');

        $this->addWidgetSection($pluginSection);
        $this->addResourceSection($pluginSection);
        $this->addToolSection($pluginSection);
        $this->addThemeSection($pluginSection);
        $this->addResourceIconsSection($pluginSection);

        return $treeBuilder;
    }

    private function addResourceSection(NodeBuilder $pluginSection): void
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
                       ->arrayNode('resource_rights')
                            ->scalarPrototype()->end()
                       ->end()
                    ->end()
                 ->end()
            ->end()
        ->end()->end();
    }

    private function addWidgetSection(NodeBuilder $pluginSection): void
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

    private function addToolSection(NodeBuilder $pluginSection): void
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
                        ->scalarNode('icon')->defaultValue('tools')->end()
                        ->arrayNode('tool_rights')
                            ->scalarPrototype()->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ->end()->end();
    }

    private function addThemeSection(NodeBuilder $pluginSection): void
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

    private function addResourceIconsSection(NodeBuilder $pluginSection): void
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

    public static function isResourceClassLoadable($v): bool
    {
        return class_exists($v);
    }

    public static function isAbstractResourceExtended($v): bool
    {
        return (new $v()) instanceof AbstractResource;
    }

    public static function isNameAlreadyExist($v, $listNames): bool
    {
        return !in_array($v, $listNames);
    }

    public function setUpdateMode(bool $updateMode): self
    {
        $this->updateMode = $updateMode;

        return $this;
    }

    public function isInUpdateMode(): bool
    {
        return $this->updateMode;
    }
}
