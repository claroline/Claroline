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
use Symfony\Component\Config\Definition\Processor;
use Claroline\CoreBundle\Library\Transfert\Importer;
use Symfony\Component\Yaml\Yaml;

/**
 * @DI\Service("claroline.tool.resource_manager_importer")
 * @DI\Tag("claroline.importer")
 */
class ResourceManagerImporter extends Importer implements ConfigurationInterface
{
    private $result;

    public function  getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('data');
        $this->addResourceSection($rootNode);

        return $treeBuilder;
    }

    public function supports($type)
    {
        return $type == 'yml' ? true: false;
    }

    public function validate(array $data)
    {
        $processor = new Processor();
        $this->result = $processor->processConfiguration($this, $data);

        foreach ($data['data']['items'] as $item) {
            $importer = $this->getImporterByName($item['item']['type']);

            if (!$importer) {
                throw new \Exception('The importer ' . $item['item']['type'] . ' does not exist');
            }

            if (isset($item['item']['data'])) {
                $importer->validate($item['item']['data']);
            }
        }

    }

    public function import(array $array)
    {

    }

    public function getName()
    {
        return 'resource_manager';
    }

    public function addResourceSection($rootNode)
    {
        $availableRoleName = [];
        $configuration = $this->getConfiguration();

        if (isset($configuration['roles'])) {
            foreach ($configuration['roles'] as $role) {
                $availableRoleName[] = $role['role']['name'];
            }
        }

        $rootNode
            ->children()
                ->arrayNode('directories')
                    ->prototype('array')
                        ->children()
                            ->arrayNode('directory')
                                ->children()
                                    ->scalarNode('name')->end()
                                    ->scalarNode('uid')->end()
                                    ->scalarNode('creator')->end()
                                    ->scalarNode('parent')->end()
                                    ->arrayNode('roles')
                                        ->prototype('array')
                                            ->children()
                                                ->arrayNode('role')
                                                    ->children()
                                                        ->scalarNode('name')
                                                            ->validate()
                                                                ->ifTrue(
                                                                    function ($v) use ($availableRoleName) {
                                                                        return call_user_func_array(
                                                                            __CLASS__ . '::roleNameExists',
                                                                            array($v, $availableRoleName)
                                                                        );
                                                                    }
                                                                )
                                                                ->thenInvalid("The role name %s doesn't exists")
                                                            ->end()
                                                        ->end()
                                                        ->variableNode('rights')->end()
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
                ->arrayNode('items')
                    ->prototype('array')
                        ->children()
                            ->arrayNode('item')
                                ->children()
                                    ->scalarNode('name')->end()
                                    ->scalarNode('creator')->end()
                                    ->scalarNode('parent')->end()
                                    ->scalarNode('type')->end()
                                    ->variableNode('data')->end()
                                    ->arrayNode('import')
                                        ->prototype('array')
                                            ->children()
                                                ->scalarNode('path')->end()
                                            ->end()
                                        ->end()
                                    ->end()
                                    ->arrayNode('roles')
                                        ->prototype('array')
                                            ->children()
                                                ->arrayNode('role')
                                                    ->children()
                                                        ->scalarNode('name')
                                                            ->validate()
                                                                ->ifTrue(
                                                                    function ($v) use ($availableRoleName) {
                                                                        return call_user_func_array(
                                                                            __CLASS__ . '::roleNameExists',
                                                                            array($v, $availableRoleName)
                                                                        );
                                                                    }
                                                                )
                                                                ->thenInvalid("The role name %s doesn't exists")
                                                            ->end()
                                                        ->end()
                                                        ->variableNode('rights')->end()
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

    public static function roleNameExists($v, $roles)
    {
        return !in_array($v, $roles);
    }
} 