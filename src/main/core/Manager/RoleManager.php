<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Manager;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CommunityBundle\Repository\RoleRepository;
use Claroline\CoreBundle\Entity\Group;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;

class RoleManager
{
    /** @var ObjectManager */
    private $om;

    /** @var RoleRepository */
    private $roleRepo;

    public function __construct(
        ObjectManager $om
    ) {
        $this->om = $om;

        $this->roleRepo = $om->getRepository(Role::class);
    }

    /**
     * @param string $name
     * @param string $translationKey
     * @param bool   $isReadOnly
     *
     * @return Role
     */
    public function createWorkspaceRole(
        $name,
        $translationKey,
        Workspace $workspace,
        $isReadOnly = false
    ) {
        $role = new Role();
        $role->setName($name);
        $role->setTranslationKey($translationKey);
        $role->setReadOnly($isReadOnly);
        $role->setType(Role::WS_ROLE);
        $role->setWorkspace($workspace);

        $this->om->persist($role);
        $workspace->addRole($role);
        $this->om->persist($workspace);
        $this->om->flush();

        return $role;
    }

    /**
     * @param string $name
     * @param string $translationKey
     * @param bool   $isReadOnly
     * @param bool   $makeGroup
     *
     * @return Role
     */
    public function createBaseRole($name, $translationKey, $isReadOnly = true, $makeGroup = false)
    {
        $role = new Role();
        $role->setName($name);
        $role->setTranslationKey($translationKey);
        $role->setReadOnly($isReadOnly);
        $role->setPersonalWorkspaceCreationEnabled(true);
        $role->setType(Role::PLATFORM_ROLE);
        $this->om->persist($role);

        if ($makeGroup) {
            $group = new Group();
            $group->setName($name);
            $group->setReadOnly($isReadOnly);
            $group->addRole($role);
            $this->om->persist($group);
        }

        $this->om->flush();

        return $role;
    }

    /**
     * @return Role
     */
    public function createUserRole(User $user)
    {
        $username = $user->getUsername();
        $roleName = 'ROLE_USER_'.strtoupper($username);
        $role = $this->getRoleByName($roleName);

        $this->om->startFlushSuite();

        if (is_null($role)) {
            $role = new Role();
            $role->setName($roleName);
            $role->setTranslationKey($username);
            $role->setReadOnly(true);
            $role->setType(Role::USER_ROLE);
            $this->om->persist($role);
        }

        $user->addRole($role);
        $this->om->endFlushSuite();

        return $role;
    }

    /**
     * @param string $username
     */
    public function renameUserRole(Role $role, $username)
    {
        $roleName = 'ROLE_USER_'.strtoupper($username);
        $role->setName($roleName);
        $role->setTranslationKey($username);

        $this->om->persist($role);
    }

    /**
     * @return Role[]
     */
    public function getWorkspaceRoles(Workspace $workspace)
    {
        return $this->roleRepo->findBy(['workspace' => $workspace]);
    }

    /**
     * @return Role
     */
    public function getCollaboratorRole(Workspace $workspace)
    {
        return $this->roleRepo->findCollaboratorRole($workspace);
    }

    /**
     * @return Role
     */
    public function getManagerRole(Workspace $workspace)
    {
        return $this->roleRepo->findManagerRole($workspace);
    }

    /**
     * @return Role[]
     */
    public function getPlatformRoles(User $user)
    {
        return $this->roleRepo->findPlatformRoles($user);
    }

    /**
     * @return Role[]
     */
    public function getWorkspaceRolesForUser(User $user, Workspace $workspace)
    {
        return $this->roleRepo->findWorkspaceRolesForUser($user, $workspace);
    }

    public function getRoleByName(string $name): ?Role
    {
        return $this->roleRepo->findOneBy(['name' => $name]);
    }

    /**
     * @return Role[]
     */
    public function getAllPlatformRoles()
    {
        return $this->roleRepo->findAllPlatformRoles();
    }

    public function getRoleByTranslationKeyAndWorkspace(string $key, Workspace $workspace): ?Role
    {
        return $this->roleRepo->findOneBy([
            'translationKey' => $key,
            'workspace' => $workspace,
        ]);
    }

    public function getUserRole($username): ?Role
    {
        return $this->roleRepo->findUserRoleByUsername($username);
    }
}
