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
use Claroline\CoreBundle\Manager\ResourceManager;
use Claroline\CoreBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Resource\Directory;

/**
 * @DI\Service("claroline.tool.resource_manager_importer")
 * @DI\Tag("claroline.importer")
 */
class ResourceManagerImporter extends Importer implements ConfigurationInterface
{
    private $result;
    private $data;
    private $rightManager;
    private $resourceManager;
    private $availableParents;
    private $om;
    private $availableCreators;

    /**
     * @DI\InjectParams({
     *     "rightManager"    = @DI\Inject("claroline.manager.rights_manager"),
     *     "resourceManager" = @DI\Inject("claroline.manager.resource_manager"),
     *     "om"              = @DI\Inject("claroline.persistence.object_manager")
     * })
     */
    public function __construct(
        RightsManager $rightManager,
        ResourceManager $resourceManager,
        ObjectManager $om
    )
    {
        $this->rightManager = $rightManager;
        $this->resourceManager = $resourceManager;
        $this->om = $om;
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
                    $forum['data'] = $item['item']['data'];
                    $importer->validate($forum['data']);
                }
            }
        }
    }

    public function import(array $data, $workspace, $entityRoles, Directory $root)
    {
        //@TODO CHANGE IMPLEMENTATION SO ROLE_USER AND ROLE_ANONYMOUS PERMS CAN BE CHANGED
        //@TODO FIX WARNINGS !!!

        /*
         * Each directory is created without parent.
         * The parent is set after the ResourceManager::create method is fired.
         * When there is no parent and no right array, the resource creation will copy
         * the parent rights (ROLE_USER and ROLE_ANONYMOUS) and we only need to add the roles from the $data
         * instead of the full array with default perms.
         * The implementation will change later (if we need to change the perms of
         * ROLE_USER and ROLE_ANONYMOUS) but it's easier to code it that way.
         */

        $directories[$data['data']['root']['uid']] = $root;

        /*************************/
        /* WORKSPACE DIRECTORIES */
        /*************************/

        if (isset($data['data']['directories'])) {
            //build the nodes
            foreach ($data['data']['directories'] as $directory) {
                $directoryEntity = new Directory();
                $directoryEntity->setName($directory['directory']['name']);
                $owner = $this->om
                    ->getRepository('ClarolineCoreBundle:User')->findOneByUsername($directory['directory']['creator']);

                $directories[$directory['directory']['uid']] = $this->resourceManager->create(
                    $directoryEntity,
                    $this->om->getRepository('Claroline\CoreBundle\Entity\Resource\ResourceType')->findOneByName('directory'),
                    $owner,
                    $workspace,
                    null,
                    null,
                    array()
                );

                //add the missing roles
                foreach ($directory['directory']['roles'] as $role) {
                     $creations = (isset($role['role']['rights']['create'])) ?
                        $this->getCreationRightsArray($role['role']['rights']['create']):
                        array();

                    $this->rightManager->create(
                        $role['role']['rights'],
                        $entityRoles[$role['role']['name']],
                        $directoryEntity->getResourceNode(),
                        false,
                        $creations
                    );
                }
            }

            //set the correct parent
            foreach ($data['data']['directories'] as $directory) {
                $node = $directories[$directory['directory']['uid']]->getResourceNode();
                $node->setParent($directories[$directory['directory']['parent']]->getResourceNode());
                $this->om->persist($node);
            }
        }

        /*************/
        /* RESOURCES */
        /*************/

        if (isset($data['data']['items'])) {
            foreach ($data['data']['items'] as $item) {
                $res['data'] = $item['item']['data'];
                //get the entity from an importer
                $entity = $this->getImporterByName($item['item']['type'])
                    ->import($res, $item['item']['name']);
                $entity->setName($item['item']['name']);
                $type = $this->om
                    ->getRepository('Claroline\CoreBundle\Entity\Resource\ResourceType')
                    ->findOneByName($item['item']['type']);
                $owner = $this->om
                    ->getRepository('ClarolineCoreBundle:User')
                    ->findOneByUsername($item['item']['creator']);

                $entity = $this->resourceManager->create(
                    $entity,
                    $type,
                    $owner,
                    $workspace,
                    null,
                    null,
                    array()
                );

                $entity->getResourceNode()->setParent($directories[$item['item']['parent']]->getResourceNode());
                $this->om->persist($entity);
                //add the missing roles
                foreach ($item['item']['roles'] as $role) {
                    $this->rightManager->create(
                        $role['role']['rights'],
                        $entityRoles[$role['role']['name']],
                        $entity->getResourceNode(),
                        false,
                        array()
                    );
                }
            }
        }

        /***************/
        /* ROOT RIGHTS */
        /***************/

        //add the missing roles
        foreach ($data['data']['root']['roles'] as $role) {
            $creations = (isset($role['role']['rights']['create'])) ?
                $this->getCreationRightsArray($role['role']['rights']['create']):
                array();

            $this->rightManager->create(
                $role['role']['rights'],
                $entityRoles[$role['role']['name']],
                $root->getResourceNode(),
                false,
                $creations
            );
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

        $this->availableParents = [];

        if (isset($data['data']['directories'])) {
            foreach ($data['data']['directories'] as $directory) {
                $this->availableParents[] = $directory['directory']['uid'];
            }
        }

        if (isset($data['data']['root'])) {
            $this->availableParents[] = $data['data']['root']['uid'];
        }

        $availableParents = $this->availableParents;

        $this->availableCreators = [];

        if (isset($data['data']['members'])) {
            if (isset($data['data']['members']['users'])) {
                foreach ($data['data']['members']['users'] as $user) {
                    $this->availableCreators[] = $user['user']['username'];
                }
            }

            if (isset($data['data']['members']['owner'])) {
                //do something
            }
        }

        $users = $this->om->getRepository('ClarolineCoreBundle:User')->findAll();

        foreach ($users as $user) {
            $this->availableCreators[] = $user->getUsername();
        }

        $availableCreators = $this->availableCreators;

        $rootNode
            ->children()
                ->arrayNode('root')->isRequired()
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
                                    ->scalarNode('creator')->isRequired()
                                        ->validate()
                                            ->ifTrue(
                                                function ($v) use ($availableCreators) {
                                                    return call_user_func_array(
                                                        __CLASS__ . '::creatorExists',
                                                        array($v, $availableCreators)
                                                    );
                                                }
                                            )
                                            ->thenInvalid("The creator username %s doesn't exists")
                                        ->end()
                                    ->end()
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

    public static function creatorExists($v, $creators)
    {
        return !in_array($v, $creators);
    }

    public function setData($data)
    {
        $this->data = $data;
    }

    public function getData()
    {
        return $this->data;
    }

    private function getCreationRightsArray ($rights) {
        $creations = array();

        if ($rights !== null) {

            foreach ($rights as $el) {
                $creations[] = $this->om->getRepository('ClarolineCoreBundle:Resource\ResourceType')
                    ->findOneByName($el['name']);
            }
        }

        return $creations;
    }
} 