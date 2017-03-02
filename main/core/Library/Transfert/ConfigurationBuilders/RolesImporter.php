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

use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Library\Transfert\Importer;
use Claroline\CoreBundle\Manager\RoleManager;
use Claroline\CoreBundle\Persistence\ObjectManager;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Processor;

/**
 * @DI\Service("claroline.importer.role_importer")
 * @DI\Tag("claroline.importer")
 */
class RolesImporter extends Importer implements ConfigurationInterface
{
    private static $data;
    private $om;
    private $roleManager;

    /**
     * @DI\InjectParams({
     *     "om"          = @DI\Inject("claroline.persistence.object_manager"),
     *     "roleManager" = @DI\Inject("claroline.manager.role_manager")
     * })
     */
    public function __construct(ObjectManager $om, RoleManager $roleManager)
    {
        $this->om = $om;
        $this->roleManager = $roleManager;
    }

    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('roles');
        $this->addRolesSection($rootNode);

        return $treeBuilder;
    }

    public function addRolesSection($rootNode)
    {
        $rootNode
            ->prototype('array')
                ->children()
                    ->arrayNode('role')
                        ->children()
                            ->scalarNode('name')->example('ROLE_01')->isRequired()
                                ->validate()
                                ->ifTrue(
                                    function ($v) {
                                        return call_user_func_array(
                                            __CLASS__.'::nameAlreadyExists',
                                            [$v]
                                        );
                                    }
                                )
                                ->thenInvalid('The name %s already exists')
                                ->end()
                            ->end()
                            ->scalarNode('translation')->info('The displayed role name')->example('student')->isRequired()->end()
                            ->booleanNode('is_base_role')->defaultTrue()->isRequired()->end()
                        ->end()
                    ->end()
                ->end()
            ->end();
    }

    public function getName()
    {
        return 'roles';
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

    private static function setData($data)
    {
        self::$data = $data;
    }

    private static function getData()
    {
        return self::$data;
    }

    public static function nameAlreadyExists($v)
    {
        $roles = self::getData();
        $found = false;

        foreach ($roles as $el) {
            foreach ($el as $role) {
                if ($role['role']['name'] === $v) {
                    if ($found) {
                        return true;
                    }
                    $found = true;
                }
            }
        }

        return false;
    }

    public function import(array $roles, Workspace $workspace)
    {
        $entityRoles = [];

        foreach ($roles as $role) {
            $roleEntity = null;

            if (!$role['role']['is_base_role']) {
                //check if the role exists in case we're importing everything in an existing workspace
                if (count($this->roleManager->getRolesByName("{$role['role']['name']}_{$workspace->getGuid()}")) === 0) {
                    $roleEntity = $this->roleManager->createWorkspaceRole(
                        "{$role['role']['name']}_{$workspace->getGuid()}",
                        $role['role']['translation'],
                        $workspace,
                        false
                    );
                }
            } else {
                $roleEntity = $this->roleManager->createBaseRole(
                    $role['role']['name'],
                    $role['role']['translation'],
                    false
                );
            }

            if ($roleEntity) {
                $entityRoles[$role['role']['name']] = $roleEntity;
            }
        }

        return $entityRoles;
    }

    public function export($workspace, array &$files, $object)
    {
        $data = [];

        foreach ($workspace->getRoles() as $role) {
            if ($role !== $this->roleManager->getManagerRole($workspace)) {
                $data[] = ['role' => [
                    'name' => $this->roleManager->getWorkspaceRoleBaseName($role),
                    'translation' => $role->getTranslationKey(),
                    'is_base_role' => false,
                ]];
            }
        }

        return $data;
    }
}
