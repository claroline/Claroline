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
use Claroline\AuthenticationBundle\Messenger\Stamp\AuthenticationStamp;
use Claroline\CommunityBundle\Messenger\Message\GenerateUserRoles;
use Claroline\CommunityBundle\Repository\RoleRepository;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class RoleManager
{
    private RoleRepository $roleRepo;

    public function __construct(
        private readonly TokenStorageInterface $tokenStorage,
        private readonly MessageBusInterface $messageBus,
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
        $role->setType(Role::WORKSPACE);
        $role->setWorkspace($workspace);

        $this->om->persist($role);
        $workspace->addRole($role);
        $this->om->persist($workspace);
        $this->om->flush();

        return $role;
    }

    public function createBaseRole(string $name, string $translationKey, bool $isReadOnly = true): Role
    {
        $role = new Role();
        $role->setName($name);
        $role->setTranslationKey($translationKey);
        $role->setLocked($isReadOnly);
        $role->setType(Role::PLATFORM);

        $this->om->persist($role);
        $this->om->flush();

        return $role;
    }

    public function createUserRole(User $user): Role
    {
        $role = new Role();
        $role->setName('ROLE_USER_'.strtoupper($user->getUsername()));
        $role->setTranslationKey($user->getUsername());
        $role->setLocked(true);
        $role->setType(Role::USER);

        $user->addRole($role);
        $this->om->persist($role);

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

    public function getUserRole(string $username): ?Role
    {
        return $this->roleRepo->findUserRoleByUsername($username);
    }

    public function generateUserRoles(): void
    {
        $this->messageBus->dispatch(
            new GenerateUserRoles(),
            [new AuthenticationStamp($this->tokenStorage->getToken()?->getUser()->getId())]
        );
    }
}
