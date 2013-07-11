<?php

namespace Claroline\CoreBundle\Manager;

use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\AbstractRoleSubject;
use Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace;
use Claroline\CoreBundle\Entity\Tool\Tool;
use Claroline\CoreBundle\Repository\RoleRepository;
use Claroline\CoreBundle\Database\Writer;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.manager.role_manager")
 */
class RoleManager
{
    private $writer;
    private $roleRepo;
    private $sc;

    /**
     * Constructor.
     *
     * @DI\InjectParams({
     *     "writer"   = @DI\Inject("claroline.database.writer"),
     *     "roleRepo" = @DI\Inject("role_repository"),
     *     "sc" =       @DI\Inject("security.context")
     * })
     */
    public function __construct(Writer $writer, RoleRepository $roleRepo, SecurityContextInterface $sc)
    {
        $this->writer = $writer;
        $this->roleRepo = $roleRepo;
        $this->sc = $sc;
    }

    public function createWorkspaceRole($name, $translationKey, AbstractWorkspace $workspace, $isReadOnly = false)
    {
        $role = new Role();
        $role->setName($name);
        $role->setTranslationKey($translationKey);
        $role->setReadOnly($isReadOnly);
        $role->setType(Role::WS_ROLE);
        $role->setWorkspace($workspace);

        $this->writer->create($role);

        return $role;
    }

    public function createBaseRole($name, $translationKey, $isReadOnly = true)
    {
        $role = new Role();
        $role->setName($name);
        $role->setTranslationKey($translationKey);
        $role->setReadOnly($isReadOnly);
        $role->setType(Role::PLATFORM_ROLE);

        $this->writer->create($role);

        return $role;
    }

    public function createCustomRole($name, $translationKey, $isReadOnly = false)
    {
        $role = new Role();
        $role->setName($name);
        $role->setTranslationKey($translationKey);
        $role->setReadOnly($isReadOnly);
        $role->setType(Role::CUSTOM_ROLE);

        $this->writer->create($role);

        return $role;
    }

    public function setRoleToRoleSubject(AbstractRoleSubject $ars, $roleName)
    {
        $role = $this->roleRepo->findOneBy(array('name' => $roleName));

        if (!is_null($role)) {
            $ars->addRole($role);
            $this->writer->update($ars);
        }
    }

    public function getRole($roleId)
    {
        return $this->roleRepo->find($roleId);
    }

    public function associateRole(AbstractRoleSubject $ars, Role $role)
    {
        $ars->addRole($role);

        $this->writer->update($ars);
    }

    public function dissociateRole(AbstractRoleSubject $ars, Role $role)
    {
        $ars->removeRole($role);

        $this->writer->update($ars);
    }

    public function associateRoles(AbstractRoleSubject $ars, ArrayCollection $roles)
    {
        foreach ($roles as $role) {
            $ars->addRole($role);
        }
        $this->writer->update($ars);
    }

    public function resetRoles(User $user)
    {
        $userRole = $this->roleRepo->findOneByName('ROLE_USER');
        $roles = $this->roleRepo->findPlatformRoles($user);

        foreach ($roles as $role) {
            if ($role !== $userRole) {
                $user->removeRole($role);
            }
        }
        $this->writer->update($user);
    }

    public function initWorkspaceBaseRole(array $roles, AbstractWorkspace $workspace)
    {
        $entityRoles = array();

        foreach ($roles as $name => $translation) {
            $role = $this->createWorkspaceRole(
                "{$name}_{$workspace->getId()}",
                $translation,
                $workspace,
                false
            );
            $entityRoles[$name] = $role;
        }

        return $entityRoles;
    }

    public function findWorkspaceRoles(AbstractWorkspace $workspace)
    {
        return array_merge(
            $this->roleRepo->findByWorkspace($workspace),
            $this->roleRepo->findBy(array('name' => 'ROLE_ANONYMOUS'))
        );
    }

    public function getRolesByWorkspace(AbstractWorkspace $workspace)
    {
        return $this->roleRepo->findByWorkspace($workspace);
    }

    public function getCollaboratorRole(AbstractWorkspace $workspace)
    {
        return $this->roleRepo->findCollaboratorRole($workspace);
    }

    public function getVisitorRole(AbstractWorkspace $workspace)
    {
        return $this->roleRepo->findVisitorRole($workspace);
    }

    public function getManagerRole(AbstractWorkspace $workspace)
    {
        return $this->roleRepo->findManagerRole($workspace);
    }

    public function getPlatformRoles(User $user)
    {
        return $this->roleRepo->findPlatformRoles($user);
    }

    public function getWorkspaceRole(AbstractRoleSubject $subject, AbstractWorkspace $workspace)
    {
        return $this->roleRepo->findWorkspaceRole($subject, $workspace);
    }

    public function getWorkspaceRoleForUser(User $user, AbstractWorkspace $workspace)
    {
        return $this->roleRepo->findWorkspaceRoleForUser($user, $workspace);
    }

    public function getRolesByWorkspaceAndTool(AbstractWorkspace $workspace, Tool $tool)
    {
        return $this->roleRepo->findByWorkspaceAndTool($workspace, $tool);
    }

    public function getRolesBySearchOnWorkspaceAndTag($search)
    {
        return $this->roleRepo->findByWorkspaceCodeTag($search);
    }

    public function getRoleById($roleId)
    {
        return $this->roleRepo->find($roleId);
    }

    public function getRoleByName($name)
    {
        return $this->roleRepo->findOneByName($name);
    }

    public function getAllRoles()
    {
        return $this->roleRepo->findAll();
    }

    public function getStringRolesFromCurrentUser()
    {
        return $this->getStringRolesFromToken($this->sc->getToken());
    }

    /**
     * Returns the roles (an array of string) of the $token.
     *
     * @todo remove this $method
     *
     * @param \Symfony\Component\Security\Core\Authentication\Token\TokenInterface $token
     *
     * @return array
     */
    public function getStringRolesFromToken(TokenInterface $token)
    {
        $roles = array();

        foreach ($token->getRoles() as $role) {
            $roles[] = $role->getRole();
        }

        return $roles;
    }
}