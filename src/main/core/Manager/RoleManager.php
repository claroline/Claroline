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
    private RoleRepository $roleRepo;

    public function __construct(
        private readonly ObjectManager $om
    ) {
        $this->roleRepo = $om->getRepository(Role::class);
    }

    public function createWorkspaceRole(string $name, string $translationKey, Workspace $workspace, bool $isReadOnly = false): Role
    {
        $role = new Role();
        $role->setName($name);
        $role->setTranslationKey($translationKey);
        $role->setLocked($isReadOnly);
        $role->setType(Role::WS_ROLE);
        $role->setWorkspace($workspace);

        $this->om->persist($role);
        $workspace->addRole($role);
        $this->om->persist($workspace);
        $this->om->flush();

        return $role;
    }

    /**
     * @deprecated use CRUD instead
     */
    public function createBaseRole(string $name, string $translationKey, bool $isReadOnly = true, bool $makeGroup = false): Role
    {
        $role = new Role();
        $role->setName($name);
        $role->setTranslationKey($translationKey);
        $role->setLocked($isReadOnly);
        $role->setPersonalWorkspaceCreationEnabled(true);
        $role->setType(Role::PLATFORM_ROLE);
        $this->om->persist($role);

        if ($makeGroup) {
            $group = new Group();
            $group->setName($name);
            $group->setCode($name);
            $group->setLocked($isReadOnly);
            $group->addRole($role);
            $this->om->persist($group);
        }

        $this->om->flush();

        return $role;
    }

    /**
     * @deprecated use CRUD instead
     */
    public function createUserRole(User $user): Role
    {
        $username = $user->getUsername();
        $roleName = 'ROLE_USER_'.strtoupper($username);
        $role = $this->getRoleByName($roleName);

        $this->om->startFlushSuite();

        if (is_null($role)) {
            $role = new Role();
            $role->setName($roleName);
            $role->setTranslationKey($username);
            $role->setLocked(true);
            $role->setType(Role::USER_ROLE);
            $this->om->persist($role);
        }

        $user->addRole($role);
        $this->om->endFlushSuite();

        return $role;
    }

    public function renameUserRole(Role $role, string $username): void
    {
        $roleName = 'ROLE_USER_'.strtoupper($username);
        $role->setName($roleName);
        $role->setTranslationKey($username);

        $this->om->persist($role);
    }

    public function getWorkspaceRoles(Workspace $workspace): array
    {
        return $this->roleRepo->findBy(['workspace' => $workspace]);
    }

    public function getCollaboratorRole(Workspace $workspace): ?Role
    {
        return $this->roleRepo->findCollaboratorRole($workspace);
    }

    public function getManagerRole(Workspace $workspace): ?Role
    {
        return $this->roleRepo->findManagerRole($workspace);
    }

    public function getRoleByName(string $name): ?Role
    {
        return $this->roleRepo->findOneBy(['name' => $name]);
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
