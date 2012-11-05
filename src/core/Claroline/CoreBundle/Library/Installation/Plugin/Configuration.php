<?php

namespace Claroline\CoreBundle\Library\Installation\Plugin;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Claroline\CoreBundle\Library\PluginBundle;
use Claroline\CoreBundle\Entity\Resource\AbstractResource;

class Configuration implements ConfigurationInterface
{
    private $plugin;

    public function __construct(PluginBundle $plugin)
    {
        $this->plugin = $plugin;
    }

    public function getConfigTreeBuilder()
    {

        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('config');
        $pluginSection = $rootNode->children('plugin');
        $this->addGeneralSection($pluginSection);
        $this->addWidgetSection($pluginSection);
        $this->addResourceSection($pluginSection);

        return $treeBuilder;
    }

    private function addGeneralSection($pluginSection)
    {
        $plugin = $this->plugin;
        $pluginFqcn = get_class($plugin);
        $imgFolder = $plugin->getImgFolder();

        $ds = DIRECTORY_SEPARATOR;

        $pluginSection
            ->booleanNode('has_options')->end()
            ->scalarNode('icon')
                ->validate()
                    ->ifTrue(function($v) use ($plugin){
                        return !call_user_func_array('Claroline\CoreBundle\Library\Installation\Plugin\Configuration::isIconValid', array($v, $plugin));
                    })
                    ->thenInvalid($pluginFqcn . " : this file was not found ({$imgFolder}{$ds}%s)")
                ->end()
            ->end()
        ->end();
    }

    private function addResourceSection($pluginSection)
    {
        $plugin = $this->plugin;
        $pluginFqcn = get_class($plugin);
        $resourceFile = $plugin->getConfigFile();
        $imgFolder = $plugin->getImgFolder();

        $ds = DIRECTORY_SEPARATOR;

        $pluginSection
            ->arrayNode('resources')
                ->prototype('array')
                    ->children()
                       ->scalarNode('name')->isRequired()->end()
                       ->scalarNode('class')
                            ->isRequired()
                                ->validate()
                                    ->ifTrue(function($v) {
                                        return !call_user_func_array('Claroline\CoreBundle\Library\Installation\Plugin\Configuration::isResourceLocationValid', array($v));
                                    })
                                    ->thenInvalid($pluginFqcn . " : %s (declared in {$resourceFile}) was not found.")
                                ->end()
                                ->validate()
                                    ->ifTrue(function ($v) {
                                        return !call_user_func_array('Claroline\CoreBundle\Library\Installation\Plugin\Configuration::isAbstractResourceExtended', array($v));
                                    })
                                    ->thenInvalid($pluginFqcn . " : %s (declared in {$resourceFile}) must extend  'Claroline\\CoreBundle\\Entity\\Resource\\AbstractResource'.")
                                ->end()
                            ->end()
                       ->booleanNode('is_visible')->isRequired()->end()
                       ->booleanNode('is_browsable')->isRequired()->end()
                       ->scalarNode('large_icon')
                           ->validate()
                                ->ifTrue(function($v) use ($plugin) {
                                    return !call_user_func_array('Claroline\CoreBundle\Library\Installation\Plugin\Configuration::isLargeIconValid', array($v, $plugin));
                                })
                                ->thenInvalid($pluginFqcn . " : this file was not found ({$imgFolder}{$ds}large{$ds}%s)")
                           ->end()
                       ->end()
                       ->scalarNode('small_icon')
                           ->validate()
                                ->ifTrue(function($v) use ($plugin) {
                                    return !call_user_func_array('Claroline\CoreBundle\Library\Installation\Plugin\Configuration::isSmallIconValid', array($v, $plugin));
                                })
                                ->thenInvalid($pluginFqcn . " : this file was not found ({$imgFolder}{$ds}small{$ds}%s)")
                           ->end()
                        ->end()
                       ->arrayNode('actions')
                         ->prototype('array')
                            ->children()
                                ->scalarNode('name')->isRequired()->end()
                                ->booleanNode('is_action_in_new_page')->isRequired()->end()
                            ->end()
                        ->end()
                    ->end()
                 ->end()
            ->end()
        ->end()->end();
    }

    private function addWidgetSection($pluginSection)
    {
        $plugin = $this->plugin;
        $pluginFqcn = get_class($plugin);
        $imgFolder = $plugin->getImgFolder();

        $ds = DIRECTORY_SEPARATOR;
        $pluginSection
            ->arrayNode('widgets')
                ->prototype('array')
                    ->children()
                       ->scalarNode('name')->isRequired()->end()
                       ->booleanNode('is_configurable')->isRequired()->end()
                       ->scalarNode('icon')
                           ->validate()
                           ->ifTrue(function($v) use ($plugin){
                               return !call_user_func_array('Claroline\CoreBundle\Library\Installation\Plugin\Configuration::isIconValid', array($v, $plugin));
                           })
                           ->thenInvalid($pluginFqcn . " : this file was not found ({$imgFolder}{$ds}%s)")
                           ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
       ->end()->end();
    }

    public static function isResourceLocationValid($v)
    {
        if (class_exists($v)) {
            return true;
        }

        return false;
    }

    public static function isAbstractResourceExtended($v)
    {
        if (class_exists($v)) {
            $classInstance = new $v;

            if ($classInstance instanceof AbstractResource) {
                return true;
            }
        }

        return false;
    }

    public static function isLargeIconValid($v, $plugin)
    {
        $ds = DIRECTORY_SEPARATOR;
        $imgFolder = $plugin->getImgFolder();
        $expectedImgLocation = $imgFolder . $ds . 'large' . $ds . $v;

        if (file_exists($expectedImgLocation)) {

            return true;
        }

        return false;
    }

    public static function isSmallIconValid($v, $plugin)
    {
        $ds = DIRECTORY_SEPARATOR;
        $imgFolder = $plugin->getImgFolder();
        $expectedImgLocation = $imgFolder . $ds . 'small' . $ds . $v;

        if (file_exists($expectedImgLocation)) {
            return true;
        }

        return false;
    }

    public static function isIconValid($v, $plugin)
    {
        $ds = DIRECTORY_SEPARATOR;
        $imgFolder = $plugin->getImgFolder();
        $expectedImgLocation = $imgFolder.$ds.$v;

        if (file_exists($expectedImgLocation)){
            return true;
        }

        return false;
    }
}
