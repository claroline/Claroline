<?php

namespace Claroline\CoreBundle\Manager;

use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\AbstractRoleSubject;
use Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace;
use Claroline\CoreBundle\Entity\Tool\Tool;
use Claroline\CoreBundle\Manager\Exception\RoleReadOnlyException;
use Claroline\CoreBundle\Repository\RoleRepository;
use Claroline\CoreBundle\Repository\UserRepository;
use Claroline\CoreBundle\Repository\GroupRepository;
use Claroline\CoreBundle\Event\StrictDispatcher;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Claroline\CoreBundle\Persistence\ObjectManager;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.manager.role_manager")
 */
class RoleManager
{
    /** @var RoleRepository */
    private $roleRepo;
    /** @var UserRepository */
    private $userRepo;
    /** @var GroupRepository */
    private $groupRepo;
    private $dispatcher;
    private $sc;
    private $om;

    /**
     * Constructor.
     *
     * @DI\InjectParams({
     *     "roleRepo"   = @DI\Inject("role_repository"),
     *     "sc"         = @DI\Inject("security.context"),
     *     "om"         = @DI\Inject("claroline.persistence.object_manager"),
     *     "dispatcher" = @DI\Inject("claroline.event.event_dispatcher")
     * })
     */
    public function __construct(SecurityContextInterface $sc, ObjectManager $om, StrictDispatcher $dispatcher)
    {
        $this->roleRepo = $om->getRepository('ClarolineCoreBundle:Role');
        $this->userRepo = $om->getRepository('ClarolineCoreBundle:User');
        $this->groupRepo = $om->getRepository('ClarolineCoreBundle:Group');
        $this->sc = $sc;
        $this->om = $om;
        $this->dispatcher = $dispatcher;
    }

    public function createWorkspaceRole($name, $translationKey, AbstractWorkspace $workspace, $isReadOnly = false)
    {
        $role = $this->om->factory('Claroline\CoreBundle\Entity\Role');
        $role->setName($name);
        $role->setTranslationKey($translationKey);
        $role->setReadOnly($isReadOnly);
        $role->setType(Role::WS_ROLE);
        $role->setWorkspace($workspace);

        $this->om->persist($role);
        $this->om->flush();

        return $role;
    }

    public function createBaseRole($name, $translationKey, $isReadOnly = true)
    {
        $role = $this->om->factory('Claroline\CoreBundle\Entity\Role');
        $role->setName($name);
        $role->setTranslationKey($translationKey);
        $role->setReadOnly($isReadOnly);
        $role->setType(Role::PLATFORM_ROLE);
        $this->om->persist($role);
        $this->om->flush();

        return $role;
    }

    public function createCustomRole($name, $translationKey, $isReadOnly = false)
    {
        $role = $this->om->factory('Claroline\CoreBundle\Entity\Role');
        $role->setName($name);
        $role->setTranslationKey($translationKey);
        $role->setReadOnly($isReadOnly);
        $role->setType(Role::CUSTOM_ROLE);

        $this->om->persist($role);
        $this->om->flush();

        return $role;
    }

    public function setRoleToRoleSubject(AbstractRoleSubject $ars, $roleName)
    {
        $role = $this->roleRepo->findOneBy(array('name' => $roleName));

        if (!is_null($role)) {
            $ars->addRole($role);
            $this->om->persist($ars);
            $this->om->flush();
        }
    }

    public function getRole($roleId)
    {
        return $this->roleRepo->find($roleId);
    }

    public function associateRole(AbstractRoleSubject $ars, Role $role)
    {
        $ars->addRole($role);
        $this->om->startFlushSuite();

        $this->dispatcher->dispatch(
                'log',
                'Log\LogRoleSubscribe',
                array($role, $ars)
        );

        $this->om->persist($ars);
        $this->om->endFlushSuite();
    }

    public function dissociateRole(AbstractRoleSubject $ars, Role $role)
    {
        $ars->removeRole($role);
        $this->om->startFlushSuite();

        $this->dispatcher->dispatch(
            'log',
            'Log\LogRoleUnsubscribe',
            array($role, $ars)
        );

        $this->om->persist($ars);
        $this->om->endFlushSuite();
    }

    public function associateRoles(AbstractRoleSubject $ars, ArrayCollection $roles)
    {
        foreach ($roles as $role) {
            $this->associateRole($ars, $role);
        }
        $this->om->persist($ars);
        $this->om->flush();
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
        $this->om->persist($user);
        $this->om->flush();
    }

    public function dissociateWorkspaceRole(AbstractRoleSubject $subject, AbstractWorkspace $workspace, Role $role)
    {
        $this->checkWorkspaceRoleEditionIsValid(array($subject), $workspace, array());
        $this->dissociateRole($subject, $role);
    }

    public function resetWorkspaceRolesForSubject(AbstractRoleSubject $subject, AbstractWorkspace $workspace)
    {
        $roles = $subject instanceof \Claroline\CoreBundle\Entity\Group ?
            $this->roleRepo->findByGroupAndWorkspace($subject, $workspace):
            $this->roleRepo->findByUserAndWorkspace($subject, $workspace);


        $this->checkWorkspaceRoleEditionIsValid(array($subject), $workspace, $roles);
        $this->om->startFlushSuite();

        foreach ($roles as $role) {
            $this->dissociateRole($subject, $role);
        }

        $this->om->endFlushSuite();
    }

    public function resetWorkspaceRoleForSubjects(array $subjects, $workspace)
    {
        $this->om->startFlushSuite();

        foreach ($subjects as $subject) {
            $this->resetWorkspaceRolesForSubject($subject, $workspace);
        }

        $this->om->endFlushSuite();
    }

    public function initWorkspaceBaseRole(array $roles, AbstractWorkspace $workspace)
    {
        $this->om->startFlushSuite();

        $entityRoles = array();

        foreach ($roles as $name => $translation) {
            $isReadOnly = (in_array($name, Role::getMandatoryWsRoles())) ? true: false;
            $role = $this->createWorkspaceRole(
                "{$name}_{$workspace->getGuid()}",
                $translation,
                $workspace,
                $isReadOnly
            );
            $entityRoles[$name] = $role;
        }

        $this->om->endFlushSuite();

        return $entityRoles;
    }

    public function remove(Role $role)
    {
        if ($role->isReadOnly()) {
            throw new RoleReadOnlyException('This role cannot be modified nor removed');
        }

        $this->om->remove($role);
        $this->om->flush();
    }

    public function edit(Role $role)
    {
        $this->om->persist($role);
        $this->om->flush();
    }

    public function editSubjectWorkspaceRoles(array $subjects, AbstractWorkspace $workspace, array $roles)
    {
        $this->om->startFlushSuite();
        $this->checkWorkspaceRoleEditionIsValid($subjects, $workspace, $roles);

        foreach ($subjects as $subject) {
            $this->resetWorkspaceRoles($subject, $workspace);
            $this->associateRoles($subject, $roles);
        }

        $this->om->endFlushSuite();
    }

    public function associateRolesToSubjects(array $subjects, array $roles)
    {
        $this->om->startFlushSuite();

        foreach($subjects as $subject) {
            foreach ($roles as $role) {
                $this->associateRole($subject, $role);
            }
        }

        $this->om->endFlushSuite();
    }

    /**
     * @param array[AbstractRoleSubject] $subjects
     * @param \Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace $workspace
     * @param array[Role] $roles
     * @throws \LogicException
     */
    public function checkWorkspaceRoleEditionIsValid(array $subjects, AbstractWorkspace $workspace, array $roles)
    {
        return true;

        $managerRole = $this->getManagerRole($workspace);
        $groupsManagers = $this->groupRepo->findByRoles(array($managerRole));
        $usersManagers = $this->userRepo->findByRoles(array($managerRole));

        $removedGroupsManager = 0;
        $removedUsersManager = 0;

        foreach ($subjects as $subject) {
           if ($subject->hasRole($managerRole->getName()) && !in_array($managerRole, $roles)) {
            $subject instanceof \Claroline\CoreBundle\Entity\Group ?
                $removedGroupsManager ++:
                $removedUsersManager ++;
            }
        }

        if ($removedGroupsManager >= count($groupsManagers) && $removedUsersManager >= count($usersManagers)) {
            throw new \LogicException("You can't remove every managers");
        }
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

    public function getWorkspaceRolesForUser(User $user, AbstractWorkspace $workspace)
    {
        return $this->roleRepo->findWorkspaceRolesForUser($user, $workspace);
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

    public function getRolesByIds(array $ids)
    {
        return $this->om->findByIds('Claroline\CoreBundle\Entity\Role', $ids);
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

    public function getRoleBaseName($roleName)
    {
        if ($roleName === 'ROLE_ANONYMOUS') {
            return $roleName;
        }

        $substr = explode('_', $roleName);
        $roleName = array_shift($substr);

        for ($i = 0, $countSubstr = count($substr) - 1; $i < $countSubstr; $i++) {
            $roleName .= '_' . $substr[$i];
        }

        return $roleName;
    }
}
