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

use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\AbstractRoleSubject;
use Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace;
use Claroline\CoreBundle\Entity\Tool\Tool;
use Claroline\CoreBundle\Manager\Exception\LastManagerDeleteException;
use Claroline\CoreBundle\Manager\Exception\RoleReadOnlyException;
use Claroline\CoreBundle\Repository\RoleRepository;
use Claroline\CoreBundle\Repository\UserRepository;
use Claroline\CoreBundle\Repository\GroupRepository;
use Claroline\CoreBundle\Event\StrictDispatcher;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Translation\Translator;
use Symfony\Component\DependencyInjection\Container;
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
    private $om;
    private $messageManager;
    private $container;
    private $translator;

    /**
     * Constructor.
     *
     * @DI\InjectParams({
     *     "om"             = @DI\Inject("claroline.persistence.object_manager"),
     *     "dispatcher"     = @DI\Inject("claroline.event.event_dispatcher"),
     *     "messageManager" = @DI\Inject("claroline.manager.message_manager"),
     *     "container"      = @DI\Inject("service_container"),
     *     "translator"     = @DI\Inject("translator")
     * })
     */
    public function __construct(
        ObjectManager $om,
        StrictDispatcher $dispatcher,
        MessageManager $messageManager,
        Container $container,
        Translator $translator
    )
    {
        $this->roleRepo = $om->getRepository('ClarolineCoreBundle:Role');
        $this->userRepo = $om->getRepository('ClarolineCoreBundle:User');
        $this->groupRepo = $om->getRepository('ClarolineCoreBundle:Group');
        $this->om = $om;
        $this->dispatcher = $dispatcher;
        $this->messageManager = $messageManager;
        $this->container = $container;
        $this->translator = $translator;
    }

    /**
     * @param string                                                   $name
     * @param string                                                   $translationKey
     * @param \Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace $workspace
     * @param boolean                                                  $isReadOnly
     *
     * @return \Claroline\CoreBundle\Entity\Role
     */
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

    /**
     * @param string  $name
     * @param string  $translationKey
     * @param boolean $isReadOnly
     *
     * @return \Claroline\CoreBundle\Entity\Role
     */
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

    /**
     * @param string  $name
     * @param string  $translationKey
     * @param boolean $isReadOnly
     *
     * @return \Claroline\CoreBundle\Entity\Role
     */
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

    /**
     * @param \Claroline\CoreBundle\Entity\AbstractRoleSubject $ars
     * @param string                                           $roleName
     */
    public function setRoleToRoleSubject(AbstractRoleSubject $ars, $roleName)
    {
        $role = $this->roleRepo->findOneBy(array('name' => $roleName));

        if (!is_null($role)) {
            $ars->addRole($role);
            $this->om->persist($ars);
            $this->om->flush();
        }
    }

    /**
     * @param integer $roleId
     *
     * @return \Claroline\CoreBundle\Entity\Role
     */
    public function getRole($roleId)
    {
        return $this->roleRepo->find($roleId);
    }

    /**
     * @param \Claroline\CoreBundle\Entity\AbstractRoleSubject $ars
     * @param \Claroline\CoreBundle\Entity\Role                $role
     * @param boolean                                          $sendMail
     */
    public function associateRole(AbstractRoleSubject $ars, Role $role, $sendMail = false)
    {
        if (!$ars->hasRole($role->getName())) {
            $ars->addRole($role);
            $this->om->startFlushSuite();

            $this->dispatcher->dispatch(
                'log',
                'Log\LogRoleSubscribe',
                array($role, $ars)
            );
            $this->om->persist($ars);
            $this->om->endFlushSuite();

            if ($sendMail) {
                $this->sendInscriptionMessage($ars, $role);
            }
        }
    }

    /**
     * @param \Claroline\CoreBundle\Entity\AbstractRoleSubject $ars
     * @param \Claroline\CoreBundle\Entity\Role                $role
     */
    public function dissociateRole(AbstractRoleSubject $ars, Role $role)
    {
        if ($ars->hasRole($role->getName())) {
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
    }

    /**
     * @param \Claroline\CoreBundle\Entity\AbstractRoleSubject $ars
     * @param \Doctrine\Common\Collections\ArrayCollection     $roles
     * @param boolean                                          $sendMail
     */
    public function associateRoles(AbstractRoleSubject $ars, ArrayCollection $roles, $sendMail = false)
    {
        foreach ($roles as $role) {
            $this->associateRole($ars, $role, $sendMail);
        }
        $this->om->persist($ars);
        $this->om->flush();
    }

    /**
     * @param \Claroline\CoreBundle\Entity\AbstractRoleSubject[]
     * @param \Claroline\CoreBundle\Entity\Role $role
     */
    public function associateRoleToMultipleSubjects(array $subjects, Role $role)
    {
        foreach ($subjects as $subject) {
            $subject->addRole($role);
            $this->om->persist($subject);
        }
        $this->om->flush();
    }

    /**
     * @param \Claroline\CoreBundle\Entity\User $user
     */
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

    /**
     * @param \Claroline\CoreBundle\Entity\AbstractRoleSubject         $subject
     * @param \Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace $workspace
     * @param \Claroline\CoreBundle\Entity\Role                        $role
     */
    public function dissociateWorkspaceRole(AbstractRoleSubject $subject, AbstractWorkspace $workspace, Role $role)
    {
        $this->checkWorkspaceRoleEditionIsValid(array($subject), $workspace, array($role));
        $this->dissociateRole($subject, $role);
    }

    /**
     * @param \Claroline\CoreBundle\Entity\AbstractRoleSubject         $subject
     * @param \Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace $workspace
     */
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

    /**
     * @param \Claroline\CoreBundle\Entity\AbstractRoleSubject[]         $subjects
     * @param \Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace $workspace
     */
    public function resetWorkspaceRoleForSubjects(array $subjects, $workspace)
    {
        $this->om->startFlushSuite();

        foreach ($subjects as $subject) {
            $this->resetWorkspaceRolesForSubject($subject, $workspace);
        }

        $this->om->endFlushSuite();
    }

    /**
     * @param array                                                    $roles
     * @param \Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace $workspace
     *
     * @return array
     */
    public function initWorkspaceBaseRole(array $roles, AbstractWorkspace $workspace)
    {
        $this->om->startFlushSuite();

        $entityRoles = array();

        foreach ($roles as $name => $translation) {
            $role = $this->createWorkspaceRole(
                "{$name}_{$workspace->getGuid()}",
                $translation,
                $workspace,
                false
            );
            $entityRoles[$name] = $role;
        }

        $role = $this->createWorkspaceRole(
            "ROLE_WS_MANAGER_{$workspace->getGuid()}",
            'manager',
            $workspace,
            true
        );

        $entityRoles['ROLE_WS_MANAGER'] = $role;
        $this->om->endFlushSuite();

        return $entityRoles;
    }

    /**
     * @param \Claroline\CoreBundle\Entity\Role $role
     *
     * @throws RoleReadOnlyException
     */
    public function remove(Role $role)
    {
        if ($role->isReadOnly()) {
            throw new RoleReadOnlyException('This role cannot be modified nor removed');
        }

        $this->om->remove($role);
        $this->om->flush();
    }

    /**
     * @param array|\Claroline\CoreBundle\Entity\AbstractRoleSubject $subjects
     * @param \Claroline\CoreBundle\Entity\Role[]                    $roles
     * @param boolean                                                $sendMail
     */
    public function associateRolesToSubjects(array $subjects, array $roles, $sendMail = false)
    {
        $this->om->startFlushSuite();

        foreach ($subjects as $subject) {
            foreach ($roles as $role) {
                $this->associateRole($subject, $role, $sendMail);
            }
        }

        $this->om->endFlushSuite();
    }

    /**
     * @param AbstractRoleSubject[] $subjects
     * @param \Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace $workspace
     * @param \Claroline\CoreBundle\Entity\Role[] $roles
     *
     * @throws Exception\LastManagerDeleteException
     */
    public function checkWorkspaceRoleEditionIsValid(array $subjects, AbstractWorkspace $workspace, array $roles)
    {
        $managerRole = $this->getManagerRole($workspace);
        $groupsManagers = $this->groupRepo->findByRoles(array($managerRole));
        $usersManagers = $this->userRepo->findByRoles(array($managerRole));

        $removedGroupsManager = 0;
        $removedUsersManager = 0;

        foreach ($subjects as $subject) {
            if ($subject->hasRole($managerRole->getName()) && in_array($managerRole, $roles)) {
                $subject instanceof \Claroline\CoreBundle\Entity\Group ?
                    $removedGroupsManager++:
                    $removedUsersManager++;
            }
        }

        if ($removedGroupsManager >= count($groupsManagers) && $removedUsersManager >= count($usersManagers)) {
            throw new LastManagerDeleteException("You can't remove every managers");
        }
    }

    /**
     * @param \Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace $workspace
     *
     * @return \Claroline\CoreBundle\Entity\Role[]
     */
    public function getWorkspaceRoles(AbstractWorkspace $workspace)
    {
        return $this->roleRepo->findByWorkspace($workspace);
    }

    /**
     * @param AbstractWorkspace $workspace
     *
     * @return \Claroline\CoreBundle\Entity\Role[]
     */
    public function getWorkspaceConfigurableRoles(AbstractWorkspace $workspace)
    {
        $roles = $this->roleRepo->findByWorkspace($workspace);
        $configurableRoles = [];

        foreach ($roles as $role) {
            if ($role->getName() !== 'ROLE_WS_MANAGER_' . $workspace->getGuid()) {
                $configurableRoles[] = $role;
            }
        }

        return array_merge(
            $configurableRoles,
            $this->roleRepo->findBy(array('name' => 'ROLE_ANONYMOUS')),
            $this->roleRepo->findBy(array('name' => 'ROLE_USER'))
        );
    }

    /**
     * @param \Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace $workspace
     *
     * @return \Claroline\CoreBundle\Entity\Role[]
     */
    public function getRolesByWorkspace(AbstractWorkspace $workspace)
    {
        return $this->roleRepo->findByWorkspace($workspace);
    }

    /**
     * @param \Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace $workspace
     *
     * @return \Claroline\CoreBundle\Entity\Role
     */
    public function getCollaboratorRole(AbstractWorkspace $workspace)
    {
        return $this->roleRepo->findCollaboratorRole($workspace);
    }

    /**
     * @param \Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace $workspace
     *
     * @return \Claroline\CoreBundle\Entity\Role
     */
    public function getVisitorRole(AbstractWorkspace $workspace)
    {
        return $this->roleRepo->findVisitorRole($workspace);
    }

    /**
     * @param \Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace $workspace
     *
     * @return \Claroline\CoreBundle\Entity\Role
     */
    public function getManagerRole(AbstractWorkspace $workspace)
    {
        return $this->roleRepo->findManagerRole($workspace);
    }

    /**
     * @param \Claroline\CoreBundle\Entity\User $user
     *
     * @return \Claroline\CoreBundle\Entity\Role[]
     */
    public function getPlatformRoles(User $user)
    {
        return $this->roleRepo->findPlatformRoles($user);
    }

    /**
     * @param \Claroline\CoreBundle\Entity\User                        $user
     * @param \Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace $workspace
     *
     * @return \Claroline\CoreBundle\Entity\Role[]
     */
    public function getWorkspaceRolesForUser(User $user, AbstractWorkspace $workspace)
    {
        return $this->roleRepo->findWorkspaceRolesForUser($user, $workspace);
    }

    /**
     * @param \Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace $workspace
     * @param \Claroline\CoreBundle\Entity\Tool\Tool                   $tool
     *
     * @return \Claroline\CoreBundle\Entity\Role[]
     */
    public function getRolesByWorkspaceAndTool(AbstractWorkspace $workspace, Tool $tool)
    {
        return $this->roleRepo->findByWorkspaceAndTool($workspace, $tool);
    }

    /**
     * @param string $search
     *
     * @return \Claroline\CoreBundle\Entity\Role[]
     */
    public function getRolesBySearchOnWorkspaceAndTag($search)
    {
        return $this->roleRepo->findByWorkspaceCodeTag($search);
    }

    /**
     * @param integer $roleId
     *
     * @return \Claroline\CoreBundle\Entity\Role
     */
    public function getRoleById($roleId)
    {
        return $this->roleRepo->find($roleId);
    }

    /**
     * @param integer[] $ids
     *
     * @return \Claroline\CoreBundle\Entity\Role[]
     */
    public function getRolesByIds(array $ids)
    {
        return $this->om->findByIds('Claroline\CoreBundle\Entity\Role', $ids);
    }

    /**
     * @param string $name
     *
     * @return \Claroline\CoreBundle\Entity\Role
     */
    public function getRoleByName($name)
    {
        return $this->roleRepo->findOneByName($name);
    }

    /**
     * @return \Claroline\CoreBundle\Entity\Role[]
     */
    public function getAllRoles()
    {
        return $this->roleRepo->findAll();
    }

    public function getAllWhereWorkspaceIsDisplayable()
    {
        return $this->roleRepo->findAllWhereWorkspaceIsDisplayable();
    }

    /**
     * @return \Claroline\CoreBundle\Entity\Role[]
     */
    public function getAllPlatformRoles()
    {
        return $this->roleRepo->findAllPlatformRoles();
    }

    /**
     * @param string                                                   $key       The translation key
     * @param \Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace $workspace
     *
     * @return \Claroline\CoreBundle\Entity\Role
     */
    public function getRoleByTranslationKeyAndWorkspace($key, AbstractWorkspace $workspace)
    {
        return $this->roleRepo->findOneBy(array('translationKey' => $key, 'workspace' => $workspace));
    }

    /**
     * @param \Claroline\CoreBundle\Entity\Role $role
     */
    public function edit(Role $role)
    {
        $this->om->persist($role);
        $this->om->flush();
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

    /**
     * @param string $roleName
     *
     * @return string
     */
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

    private function sendInscriptionMessage(AbstractRoleSubject $ars, Role $role)
    {
        //workspace registration
        if ($role->getWorkspace()) {
            $content = $this->translator->trans(
                'workspace_registration_message',
                array('%workspace_name%' => $role->getWorkspace()->getName()),
                'platform'
            );
            $object = $this->translator->trans(
                'workspace_registration_message_object',
                array('%workspace_name%' => $role->getWorkspace()->getName()),
                'platform'
            );
        } else {
            //new role
            $content = $this->translator->trans('new_role_message', array(), 'platform');
            $object = $this->translator->trans('new_role_message_object', array(), 'platform');
        }

        $sender = $this->container->get('security.context')->getToken()->getUser();
        $this->messageManager->sendMessageToAbstractRoleSubject($ars, $content, $object, $sender);
    }

    public function getPlatformNonAdminRoles()
    {
        return $this->roleRepo->findPlatformNonAdminRoles();
    }

    public function createPlatformRoleAction($translationKey)
    {
        $role = new Role();
        $role->setType($translationKey);
        $role->setName('ROLE_' . strtoupper($translationKey));
        $role->setTranslationKey($translationKey);
        $role->setReadOnly(false);
        $role->setType(Role::PLATFORM_ROLE);
        $this->om->persist($role);
        $this->om->flush();
    }
}
