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
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Claroline\CoreBundle\Manager\RightsManager;

/**
 * @DI\Service("claroline.tool.resource_manager_importer")
 * @DI\Tag("claroline.importer")
 */
class ResourceManagerImporter extends Importer implements ConfigurationInterface
{
    private $result;
    private $data;
    private $rightManager;

    /**
     * @DI\InjectParams({
     *     "rightManager" = @DI\Inject("claroline.manager.rights_manager")
     * })
     */
    public function __construct(RightsManager $rightManager)
    {
        $this->rightManager = $rightManager;
    }

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
        $this->setData($data);
        $processor = new Processor();
        $this->result = $processor->processConfiguration($this, $data);

        if (isset($data['data']['items'])) {
            foreach ($data['data']['items'] as $item) {
                $importer = $this->getImporterByName($item['item']['type']);

                if (!$importer) {
                    throw new InvalidConfigurationException('The importer ' . $item['item']['type'] . ' does not exist');
                }

                if (isset($item['item']['data'])) {
                    $importer->validate($item['item']['data']);
                }
            }
        }
    }

    public function import(array $data, $workspace, $entityRoles, $root)
    {
        if (isset($data['data']['root'])) {
            foreach ($data['data']['root']['roles'] as $role) {
                $creations = array();
                $this->rightManager->create(
                    $role['role']['rights'],
                    $entityRoles[$role['role']['name']],
                    $root,
                    false,
                    $creations
                );
            }
        }
    }

    public function getName()
    {
        return 'resource_manager';
    }

    public function addResourceSection($rootNode)
    {
        $availableRoleName = [];
        $configuration = $this->getConfiguration();
        $data = $this->getData();

        if (isset($configuration['roles'])) {
            foreach ($configuration['roles'] as $role) {
                $availableRoleName[] = $role['role']['name'];
            }
        }

        $availableParents = [];

        if (isset($data['data']['directories'])) {
            foreach ($data['data']['directories'] as $directory) {
                $availableParents[] = $directory['directory']['uid'];
            }
        }

        if (isset($data['data']['root'])) {
            $availableParents[] = $data['data']['root']['uid'];
        }

        $rootNode
            ->children()
                ->arrayNode('root')
                    ->children()
                        ->scalarNode('uid')->isRequired()->end()
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
                ->arrayNode('directories')
                    ->prototype('array')
                        ->children()
                            ->arrayNode('directory')
                                ->children()
                                    ->scalarNode('name')->isRequired()->end()
                                    ->scalarNode('uid')->isRequired()->end()
                                    ->scalarNode('creator')->isRequired()->end()
                                    ->scalarNode('parent')->isRequired()
                                        ->validate()
                                            ->ifTrue(
                                                function ($v) use ($availableParents) {
                                                    return call_user_func_array(
                                                        __CLASS__ . '::parentExists',
                                                        array($v, $availableParents)
                                                    );
                                                }
                                            )
                                            ->thenInvalid("The parent name %s doesn't exists")
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
                ->arrayNode('items')
                    ->prototype('array')
                        ->children()
                            ->arrayNode('item')
                                ->children()
                                    ->scalarNode('name')->end()
                                    ->scalarNode('creator')->end()
                                    ->scalarNode('parent')
                                        ->validate()
                                        ->ifTrue(
                                            function ($v) use ($availableParents) {
                                                return call_user_func_array(
                                                    __CLASS__ . '::parentExists',
                                                    array($v, $availableParents)
                                                );
                                            }
                                        )
                                        ->thenInvalid("The parent uid %s doesn't exists")
                                        ->end()
                                    ->end()
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

    public static function parentExists($v, $parents)
    {
        return !in_array($v, $parents);
    }

    public function setData($data)
    {
        $this->data = $data;
    }

    public function getData()
    {
        return $this->data;
    }
} 