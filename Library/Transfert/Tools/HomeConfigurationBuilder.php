<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Library\Transfert\Tools;

use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use JMS\DiExtraBundle\Annotation as DI;
use Claroline\CoreBundle\Event\ValidateToolConfigEvent;
use Claroline\CoreBundle\Library\Transfert\ImporterInterface;

/**
 * @DI\Service("claroline.home_tool_config_builder")
 */
class HomeConfigurationBuilder implements ConfigurationInterface
{
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
    private function getConfigBuilder(ValidateToolConfigEvent $event)
    {
        $event->setConfigurationBuilder($this->getConfigTreeBuilder());
        $event->stopPropagation();
    }

    private function addHomeSection($rootNode)
    {
        $rootNode
            ->children()
                ->prototype('array')
                    ->arrayNode('tab')
                        ->children()
                            ->scalarNode('name')->isRequired()->end()
                        ->end()
                    ->prototype('array')
                        ->arrayNode('widgets')
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
            ->end();
    }

    public function support()
    {

    }

    public function import(array $data)
    {

    }

    public function validate(array $data)
    {

    }
} 