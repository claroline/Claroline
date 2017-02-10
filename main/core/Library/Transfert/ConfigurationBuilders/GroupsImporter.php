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

use Claroline\CoreBundle\Library\Transfert\Importer;
use Claroline\CoreBundle\Persistence\ObjectManager;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Processor;

/**
 * @DI\Service("claroline.importer.groups_importer")
 * @DI\Tag("claroline.importer")
 */
class GroupsImporter extends Importer implements ConfigurationInterface
{
    private static $data;
    private $om;

    /**
     * @DI\InjectParams({
     *     "om" = @DI\Inject("claroline.persistence.object_manager")
     * })
     */
    public function __construct(ObjectManager $om)
    {
        $this->om = $om;
    }

    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('groups');
        $this->addGroupsSection($rootNode);

        return $treeBuilder;
    }

    public function addGroupsSection($rootNode)
    {
        $configuration = $this->getConfiguration();
        $names = $this->om->getRepository('Claroline\CoreBundle\Entity\Group')->findNames();
        $availableUsernames = [];

        foreach ($this->om->getRepository('Claroline\CoreBundle\Entity\User')->findUsernames() as $username) {
            $availableUsernames[] = $username['username'];
        }

        if (isset($configuration['members']['users'])) {
            $mergedUsers = $configuration['members']['users'];

            foreach ($mergedUsers as $el) {
                $availableUsernames[] = $el['user']['username'];
            }
        }

        if (isset($configuration['members']['owner'])) {
            $availableUsernames[] = $configuration['members']['owner']['username'];
        }

        $availableRoleName = [];

        if (isset($configuration['roles'])) {
            foreach ($configuration['roles'] as $role) {
                $availableRoleName[] = $role['role']['name'];
            }
        }

        $rootNode
            ->prototype('array')
                ->children()
                    ->arrayNode('group')
                        ->children()
                           ->scalarNode('name')->example('GROUP_01')->isRequired()
                                ->validate()
                                    ->ifTrue(
                                        function ($v) use ($names) {
                                            return call_user_func_array(
                                                __CLASS__.'::nameAlreadyExistsInDatabase',
                                                [$v, $names]
                                            );
                                        }
                                    )
                                    ->thenInvalid('The name %s already exists in the database')
                                ->end()
                                    ->validate()
                                    ->ifTrue(
                                        function ($v) use ($names) {
                                            return call_user_func_array(
                                                __CLASS__.'::nameAlreadyExistsInConfig',
                                                [$v, $names]
                                            );
                                        }
                                    )
                                    ->thenInvalid('The name %s already exists in the configuration')
                                ->end()
                           ->end()
                           ->arrayNode('users')
                                ->prototype('array')
                                    ->children()
                                        ->scalarNode('username')->info('An existing username')->example('jdoe')->isRequired()
                                            ->validate()
                                                ->ifTrue(
                                                    function ($v) use ($availableUsernames) {
                                                        return call_user_func_array(
                                                            __CLASS__.'::usernameExists',
                                                            [$v, $availableUsernames]
                                                        );
                                                    }
                                                )
                                                ->thenInvalid("The username %s doesn't exists")
                                            ->end()
                                        ->end()
                                    ->end()
                                ->end()
                           ->end()
                           ->arrayNode('roles')
                               ->prototype('array')
                                    ->children()
                                        ->scalarNode('name')->info('An existing role')->example('ROLE_01')->isRequired()
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

    /**
     * Validate the group section.
     *
     * @param array $data
     */
    public function validate(array $data)
    {
        $processor = new Processor();
        self::setData($data);
        $processor->processConfiguration($this, $data);
    }

    private static function setData($data)
    {
        self::$data = $data;
    }

    private static function getData()
    {
        return self::$data;
    }

    public function getName()
    {
        return 'groups';
    }

    public static function nameAlreadyExistsInConfig($v)
    {
        $groups = self::getData();
        $found = false;

        foreach ($groups as $el) {
            foreach ($el as $group) {
                if ($group['group']['name'] === $v) {
                    if ($found) {
                        return true;
                    }
                    $found = true;
                }
            }
        }

        return false;
    }

    public function export($workspace, array &$files, $object)
    {
        return [];
    }

    public function import(array $data)
    {
    }

    public static function nameAlreadyExistsInDatabase($v, $groups)
    {
        return in_array($v, $groups);
    }

    public static function usernameExists($v, $usernames)
    {
        return !in_array($v, $usernames);
    }

    public static function roleNameExists($v, $roles)
    {
        return !in_array($v, $roles);
    }
}
