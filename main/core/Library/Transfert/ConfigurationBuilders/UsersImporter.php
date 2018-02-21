<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Library\Transfert\ConfigurationBuilders;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Library\Transfert\Importer;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Processor;

/**
 * @DI\Service("claroline.importer.users_importer")
 * @DI\Tag("claroline.importer")
 */
class UsersImporter extends Importer implements ConfigurationInterface
{
    private static $data;
    private $om;
    private $userManager;
    private $container;

    /**
     * @DI\InjectParams({
     *     "om"        = @DI\Inject("claroline.persistence.object_manager"),
     *     "container" = @DI\Inject("service_container")
     * })
     */
    public function __construct(ObjectManager $om, $container)
    {
        $this->om = $om;
        $this->container = $container;
    }

    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('users');
        $this->addUsersSection($rootNode);

        return $treeBuilder;
    }

    public function addUsersSection($rootNode)
    {
        $usernames = [];

        foreach ($this->om->getRepository('Claroline\CoreBundle\Entity\User')->findUsernames() as $username) {
            $usernames[] = $username['username'];
        }

        $configuration = $this->getConfiguration();
        $availableRoleName = [];

        if (isset($configuration['roles'])) {
            foreach ($configuration['roles'] as $role) {
                $availableRoleName[] = $role['role']['name'];
            }
        }

        //add platform roles
        $existingRoles = $this->om->getRepository('ClarolineCoreBundle:Role')->findAllPlatformRoles();

        foreach ($existingRoles as $existingRole) {
            $availableRoleName[] = $existingRole->getName();
        }

        //ROLE_ANONYMOUS can be selected
        $availableRoleName[] = 'ROLE_ANONYMOUS';
        //ROLE_WS_MANAGER is created automatically
        $availableRoleName[] = 'ROLE_WS_MANAGER';

        $rootNode
            ->prototype('array')
                ->children()
                    ->arrayNode('user')
                        ->children()
                            ->scalarNode('username')->example('janedoe')->isRequired()
                                ->validate()
                                    ->ifTrue(
                                        function ($v) use ($usernames) {
                                            return call_user_func_array(
                                                __CLASS__.'::usernameMissingInDatabase',
                                                [$v, $usernames]
                                            );
                                        }
                                    )
                                    ->thenInvalid('The username %s does not exists')
                                ->end()
                            ->end()
                            ->arrayNode('roles')
                                ->prototype('array')
                                    ->children()
                                        ->scalarNode('name')->example('collaborator')->isRequired()
                                        ->validate()
                                            ->ifTrue(
                                                function ($v) use ($availableRoleName) {
                                                    return call_user_func_array(
                                                        __CLASS__.'::roleNameExists',
                                                        [$v, $availableRoleName]
                                                    );
                                                }
                                            )
                                            ->thenInvalid("The role name %s doesn't exists")
                                        ->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();
    }

    public function getName()
    {
        return 'user';
    }

    /**
     * Validate the workspace properties.
     *
     * @todo show the expected array
     *
     * @param array $data
     */
    public function validate(array $data)
    {
        $processor = new Processor();
        self::setData($data);
        $processor->processConfiguration($this, $data);
    }

    public function import(array $data, array $entityRoles)
    {
        $this->om->startFlushSuite();

        foreach ($data as $user) {
            $userEntities = $this->om->getRepository('ClarolineCoreBundle:User')
                ->findBy(['username' => $user['user']['username']]);

            if (isset($user['user']['roles']) && 1 === count($userEntities)) {
                foreach ($user['user']['roles'] as $role) {
                    $userEntities[0]->addRole($entityRoles[$role['name']]);
                }
                $this->om->persist($userEntities[0]);
            }
        }

        $this->om->endFlushSuite();
    }

    public static function usernameMissingInDatabase($v, $usernames)
    {
        return self::isStrict() ? !in_array($v, $usernames) : false;
    }

    private static function setData($data)
    {
        self::$data = $data;
    }

    public static function roleNameExists($v, $roles)
    {
        return !in_array($v, $roles);
    }

    public static function ownerAlreadyExists($v, $owner)
    {
        return $owner === $v ? true : false;
    }

    public function export($workspace, array &$files, $object)
    {
        return [];
    }
}
