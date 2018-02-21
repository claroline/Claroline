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

use Claroline\AppBundle\Event\StrictDispatcher;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\BundleRecorder\Log\LoggableTrait;
use Claroline\CoreBundle\Entity\AbstractRoleSubject;
use Claroline\CoreBundle\Entity\Group;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\RoleOptions;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Claroline\CoreBundle\Manager\Exception\LastManagerDeleteException;
use Claroline\CoreBundle\Manager\Exception\RoleReadOnlyException;
use Claroline\CoreBundle\Repository\GroupRepository;
use Claroline\CoreBundle\Repository\RoleRepository;
use Claroline\CoreBundle\Repository\UserRepository;
use Doctrine\ORM\NonUniqueResultException;
use JMS\DiExtraBundle\Annotation as DI;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * @DI\Service("claroline.manager.role_manager")
 */
class RoleManager
{
    const EMPTY_USERS = 1;
    const EMPTY_GROUPS = 2;

    use LoggableTrait;

    /** @var RoleRepository */
    private $roleRepo;
    /** @var RoleOptionsRepository */
    private $roleOptionsRepo;
    /** @var UserRepository */
    private $userRepo;
    /** @var GroupRepository */
    private $groupRepo;
    private $dispatcher;
    private $om;
    private $container;
    private $translator;
    private $configHandler;

    /**
     * Constructor.
     *
     * @DI\InjectParams({
     *     "om"             = @DI\Inject("claroline.persistence.object_manager"),
     *     "dispatcher"     = @DI\Inject("claroline.event.event_dispatcher"),
     *     "container"      = @DI\Inject("service_container"),
     *     "translator"     = @DI\Inject("translator"),
     *     "configHandler"  = @DI\Inject("claroline.config.platform_config_handler")
     * })
     */
    public function __construct(
        ObjectManager $om,
        StrictDispatcher $dispatcher,
        Container $container,
        TranslatorInterface $translator,
        PlatformConfigurationHandler $configHandler
    ) {
        $this->roleRepo = $om->getRepository('ClarolineCoreBundle:Role');
        $this->workspaceRepo = $om->getRepository('ClarolineCoreBundle:Workspace\Workspace');
        $this->roleOptionsRepo = $om->getRepository('ClarolineCoreBundle:RoleOptions');
        $this->userRepo = $om->getRepository('ClarolineCoreBundle:User');
        $this->groupRepo = $om->getRepository('ClarolineCoreBundle:Group');
        $this->om = $om;
        $this->dispatcher = $dispatcher;
        $this->container = $container;
        $this->translator = $translator;
        $this->configHandler = $configHandler;
    }

    /**
     * @param string                                           $name
     * @param string                                           $translationKey
     * @param \Claroline\CoreBundle\Entity\Workspace\Workspace $workspace
     * @param bool                                             $isReadOnly
     *
     * @return \Claroline\CoreBundle\Entity\Role
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
     *
     * @return \Claroline\CoreBundle\Entity\Role
     */
    public function createBaseRole($name, $translationKey, $isReadOnly = true)
    {
        $role = new Role();
        $role->setName($name);
        $role->setTranslationKey($translationKey);
        $role->setReadOnly($isReadOnly);
        $role->setPersonalWorkspaceCreationEnabled(true);
        $role->setType(Role::PLATFORM_ROLE);
        $this->om->persist($role);
        $this->om->flush();
        $this->container->get('claroline.manager.profile_property_manager')
            ->addDefaultProperties();

        return $role;
    }

    /**
     * @param string $name
     * @param string $translationKey
     * @param bool   $isReadOnly
     *
     * @return \Claroline\CoreBundle\Entity\Role
     */
    public function createCustomRole($name, $translationKey, $isReadOnly = false)
    {
        $role = new Role();
        $role->setName($name);
        $role->setTranslationKey($translationKey);
        $role->setReadOnly($isReadOnly);
        $role->setType(Role::CUSTOM_ROLE);

        $this->om->persist($role);
        $this->om->flush();

        return $role;
    }

    /**
     * @param User $user
     *
     * @return \Claroline\CoreBundle\Entity\Role
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
     * @param Role   $role
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
     * @param \Claroline\CoreBundle\Entity\AbstractRoleSubject $ars
     * @param string                                           $roleName
     *
     * @throws \Exception
     */
    public function setRoleToRoleSubject(AbstractRoleSubject $ars, $roleName, $validate = true)
    {
        $role = $this->roleRepo->findOneBy(['name' => $roleName]);

        if ($validate) {
            $validated = $this->validateRoleInsert($ars, $role);
        } else {
            $validated = true;
        }

        if (!$validated) {
            throw new Exception\AddRoleException();
        }

        if ('Claroline\CoreBundle\Entity\Group' === get_class($ars) && 'ROLE_USER' === $role->getName()) {
            throw new Exception\AddRoleException('ROLE_USER cannot be added to groups');
        }

        if (!is_null($role)) {
            $ars->addRole($role);
            $this->om->persist($ars);
            $this->om->flush();
        }
    }

    /**
     * @param int $roleId
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
     * @param bool                                             $sendMail
     *
     * @throws Exception\AddRoleException
     */
    public function associateRole(AbstractRoleSubject $ars, Role $role, $sendMail = false, $dispatch = true)
    {
        if (!$this->validateRoleInsert($ars, $role)) {
            throw new Exception\AddRoleException('Role cannot be added');
        }

        if ('Claroline\CoreBundle\Entity\Group' === get_class($ars) && 'ROLE_USER' === $role->getName()) {
            throw new Exception\AddRoleException('ROLE_USER cannot be added to groups');
        }

        $hasRole = $ars->hasRole($role->getName());
        $ars->addRole($role);
        $this->om->startFlushSuite();

        if ($dispatch && !$hasRole) {
            $this->dispatcher->dispatch('log', 'Log\LogRoleSubscribe', [$role, $ars]);
        }

        $this->om->persist($ars);
        $this->om->endFlushSuite();

        $withMail = $this->configHandler->getParameter('send_mail_at_workspace_registration');

        if ($sendMail) {
            $this->sendInscriptionMessage($ars, $role, $withMail);
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
                [$role, $ars]
            );

            $this->om->persist($ars);
            $this->om->endFlushSuite();
        }
    }

    /**
     * @param \Claroline\CoreBundle\Entity\AbstractRoleSubject $ars
     * @param array                                            $roles
     * @param bool                                             $sendMail
     */
    public function associateRoles(AbstractRoleSubject $ars, $roles, $sendMail = false)
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
            $this->associateRole($subject, $role);
        }
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
     * @param \Claroline\CoreBundle\Entity\AbstractRoleSubject $subject
     * @param \Claroline\CoreBundle\Entity\Workspace\Workspace $workspace
     * @param \Claroline\CoreBundle\Entity\Role                $role
     */
    public function dissociateWorkspaceRole(AbstractRoleSubject $subject, Workspace $workspace, Role $role)
    {
        $this->checkWorkspaceRoleEditionIsValid([$subject], $workspace, [$role]);
        $this->dissociateRole($subject, $role);
    }

    /**
     * @param \Claroline\CoreBundle\Entity\AbstractRoleSubject $subject
     * @param \Claroline\CoreBundle\Entity\Workspace\Workspace $workspace
     */
    public function resetWorkspaceRolesForSubject(AbstractRoleSubject $subject, Workspace $workspace)
    {
        $roles = $subject instanceof Group ?
            $this->roleRepo->findByGroupAndWorkspace($subject, $workspace) :
            $this->roleRepo->findByUserAndWorkspace($subject, $workspace);

        $this->checkWorkspaceRoleEditionIsValid([$subject], $workspace, $roles);
        $this->om->startFlushSuite();

        foreach ($roles as $role) {
            $this->dissociateRole($subject, $role);
        }

        $this->om->endFlushSuite();
    }

    /**
     * @param \Claroline\CoreBundle\Entity\AbstractRoleSubject[] $subjects
     * @param \Claroline\CoreBundle\Entity\Workspace\Workspace   $workspace
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
     * @param array                                            $roles
     * @param \Claroline\CoreBundle\Entity\Workspace\Workspace $workspace
     *
     * @return array
     */
    public function initWorkspaceBaseRole(array $roles, Workspace $workspace)
    {
        $this->om->startFlushSuite();
        $entityRoles = [];

        $entityRoles['ROLE_WS_MANAGER'] = $this->createWorkspaceRole(
            "ROLE_WS_MANAGER_{$workspace->getGuid()}",
            'manager',
            $workspace,
            true
        );

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
            throw new RoleReadOnlyException('This role cannot be removed');
        }

        $this->om->remove($role);
        $this->om->flush();
    }

    /**
     * @param array|\Claroline\CoreBundle\Entity\AbstractRoleSubject $subjects
     * @param \Claroline\CoreBundle\Entity\Role[]                    $roles
     * @param bool                                                   $sendMail
     */
    public function associateRolesToSubjects(array $subjects, array $roles, $sendMail = false)
    {
        $this->om->startFlushSuite();

        foreach ($subjects as $subject) {
            $this->associateRoles($subject, $roles);
        }

        $this->om->endFlushSuite();
    }

    /**
     * @param AbstractRoleSubject[]                            $subjects
     * @param \Claroline\CoreBundle\Entity\Workspace\Workspace $workspace
     * @param \Claroline\CoreBundle\Entity\Role[]              $roles
     *
     * @throws Exception\LastManagerDeleteException
     */
    public function checkWorkspaceRoleEditionIsValid(array $subjects, Workspace $workspace, array $roles)
    {
        $managerRole = $this->getManagerRole($workspace);
        $groupsManagers = $this->groupRepo->findByRoles([$managerRole]);
        $usersManagers = $this->userRepo->findByRoles([$managerRole]);

        $removedGroupsManager = 0;
        $removedUsersManager = 0;

        foreach ($subjects as $subject) {
            if ($subject->hasRole($managerRole->getName()) && in_array($managerRole, $roles)) {
                $subject instanceof Group ?
                    $removedGroupsManager++ :
                    $removedUsersManager++;
            }
        }

        if ($removedGroupsManager >= count($groupsManagers) && $removedUsersManager >= count($usersManagers)) {
            throw new LastManagerDeleteException("You can't remove every managers");
        }
    }

    /**
     * @param \Claroline\CoreBundle\Entity\Workspace\Workspace $workspace
     *
     * @return \Claroline\CoreBundle\Entity\Role[]
     */
    public function getWorkspaceRoles(Workspace $workspace)
    {
        return $this->roleRepo->findByWorkspace($workspace);
    }

    /**
     * @param Workspace $workspace
     *
     * @return \Claroline\CoreBundle\Entity\Role[]
     */
    public function getWorkspaceConfigurableRoles(Workspace $workspace)
    {
        $roles = $this->roleRepo->findByWorkspace($workspace);
        $configurableRoles = [];

        foreach ($roles as $role) {
            if ($role->getName() !== 'ROLE_WS_MANAGER_'.$workspace->getGuid()) {
                $configurableRoles[] = $role;
            }
        }

        return array_merge(
            $configurableRoles,
            $this->roleRepo->findBy(['name' => 'ROLE_ANONYMOUS']),
            $this->roleRepo->findBy(['name' => 'ROLE_USER'])
        );
    }

    public function getWorkspaceNonAdministrateRoles(Workspace $workspace)
    {
        return $this->roleRepo->findByWorkspaceNonAdministrate($workspace);
    }

    /**
     * @param \Claroline\CoreBundle\Entity\Workspace\Workspace $workspace
     *
     * @return \Claroline\CoreBundle\Entity\Role[]
     */
    public function getRolesByWorkspace(
        Workspace $workspace,
        $search = '',
        $orderedBy = 'id',
        $order = 'ASC'
    ) {
        if (empty($search)) {
            return $this->roleRepo->findByWorkspace($workspace, $orderedBy, $order);
        } else {
            return $this->roleRepo->findByWorkspaceAndSearch(
                $workspace,
                $search,
                $orderedBy,
                $order
            );
        }
    }

    /**
     * @param \Claroline\CoreBundle\Entity\Workspace\Workspace $workspace
     *
     * @return \Claroline\CoreBundle\Entity\Role
     */
    public function getCollaboratorRole(Workspace $workspace)
    {
        return $this->roleRepo->findCollaboratorRole($workspace);
    }

    /**
     * @param \Claroline\CoreBundle\Entity\Workspace\Workspace $workspace
     *
     * @return \Claroline\CoreBundle\Entity\Role
     */
    public function getVisitorRole(Workspace $workspace)
    {
        return $this->roleRepo->findVisitorRole($workspace);
    }

    /**
     * @param \Claroline\CoreBundle\Entity\Workspace\Workspace $workspace
     *
     * @return \Claroline\CoreBundle\Entity\Role
     */
    public function getManagerRole(Workspace $workspace)
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
     * @param \Claroline\CoreBundle\Entity\User                $user
     * @param \Claroline\CoreBundle\Entity\Workspace\Workspace $workspace
     *
     * @return \Claroline\CoreBundle\Entity\Role[]
     */
    public function getWorkspaceRolesForUser(User $user, Workspace $workspace)
    {
        return $this->roleRepo->findWorkspaceRolesForUser($user, $workspace);
    }

    /**
     * @param \Claroline\CoreBundle\Entity\User $user
     *
     * @return \Claroline\CoreBundle\Entity\Role[]
     */
    public function getWorkspaceRolesByUser(User $user, $executeQuery = true)
    {
        return $this->roleRepo->findWorkspaceRolesByUser($user, $executeQuery);
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
     * @param int $roleId
     *
     * @return \Claroline\CoreBundle\Entity\Role
     */
    public function getRoleById($roleId)
    {
        return $this->roleRepo->find($roleId);
    }

    /**
     * @param int[] $ids
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
     * @param string $name
     *
     * @return \Claroline\CoreBundle\Entity\Role[]
     */
    public function getRolesByName($name)
    {
        return $this->roleRepo->findByName($name);
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

    public function getAllWhereWorkspaceIsDisplayableAndInList(array $workspaces)
    {
        return $this->roleRepo->findAllWhereWorkspaceIsDisplayableAndInList($workspaces);
    }

    /**
     * @return \Claroline\CoreBundle\Entity\Role[]
     */
    public function getAllPlatformRoles($includeRoleUser = true)
    {
        return $this->roleRepo->findAllPlatformRoles($includeRoleUser);
    }

    public function getAllPlatformRoleNamesAndKeys($includeRoleUser = true)
    {
        return $this->roleRepo->findAllPlatformRoleNamesAndKeys($includeRoleUser);
    }

    /**
     * @param string                                           $key       The translation key
     * @param \Claroline\CoreBundle\Entity\Workspace\Workspace $workspace
     *
     * @return \Claroline\CoreBundle\Entity\Role
     */
    public function getRoleByTranslationKeyAndWorkspace($key, Workspace $workspace)
    {
        return $this->roleRepo->findOneBy(['translationKey' => $key, 'workspace' => $workspace]);
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
        $roles = [];

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
        if ('ROLE_ANONYMOUS' === $roleName) {
            return $roleName;
        }

        $substr = explode('_', $roleName);
        $roleName = array_shift($substr);

        for ($i = 0, $countSubstr = count($substr) - 1; $i < $countSubstr; ++$i) {
            $roleName .= '_'.$substr[$i];
        }

        return $roleName;
    }

    private function sendInscriptionMessage(AbstractRoleSubject $ars, Role $role, $withMail = true)
    {
        //workspace registration
        if ($role->getWorkspace()) {
            $content = $this->translator->trans(
                'workspace_registration_message',
                ['%workspace_name%' => $role->getWorkspace()->getName()],
                'platform'
            );
            $object = $this->translator->trans(
                'workspace_registration_message_object',
                ['%workspace_name%' => $role->getWorkspace()->getName()],
                'platform'
            );
        } else {
            //new role
            $content = $this->translator->trans('new_role_message', [], 'platform');
            $object = $this->translator->trans('new_role_message_object', [], 'platform');
        }

        $sender = $this->container->get('security.token_storage')->getToken()->getUser();
        $this->dispatcher->dispatch(
            'claroline_message_sending',
            'SendMessage',
            [$sender, $content, $object, $ars, [], $withMail]
        );
    }

    public function getPlatformNonAdminRoles($includeAnonymous = false)
    {
        return $this->roleRepo->findPlatformNonAdminRoles($includeAnonymous);
    }

    public function createPlatformRoleAction($translationKey)
    {
        $role = new Role();
        $role->setType($translationKey);
        $role->setName('ROLE_'.strtoupper($translationKey));
        $role->setTranslationKey($translationKey);
        $role->setReadOnly(false);
        $role->setType(Role::PLATFORM_ROLE);
        $this->om->persist($role);
        $this->om->flush();

        $this->container->get('claroline.manager.profile_property_manager')
            ->addDefaultProperties();

        return $role;
    }

    /**
     * Returns if a role can be added to a RoleSubject.
     *
     * @param AbstractRoleSubject $ars
     * @param Role                $role
     *
     * @return bool
     */
    public function validateRoleInsert(AbstractRoleSubject $ars, Role $role)
    {
        $total = $this->countUsersByRoleIncludingGroup($role);

        //cli always win!
        if ('ROLE_ADMIN' === $role->getName() && 'cli' === php_sapi_name() ||
            //web installer too
            null === $this->container->get('security.token_storage')->getToken()) {
            return true;
        }

        if ('ROLE_ADMIN' === $role->getName() && !$this->container->get('security.authorization_checker')->isGranted('ROLE_ADMIN')) {
            return false;
        }

        //if we already have the role, then it's ok
        if ($ars->hasRole($role->getName())) {
            return true;
        }

        if (null === $role->getMaxUsers()) {
            return true;
        }

        if ($role->getWorkspace()) {
            $maxUsers = $role->getWorkspace()->getMaxUsers();
            $countByWorkspace = $this->container->get('claroline.manager.workspace_manager')->countUsers($role->getWorkspace(), true);

            if ($maxUsers <= $countByWorkspace) {
                return false;
            }
        }

        if ($ars instanceof User) {
            return $total < $role->getMaxUsers();
        }

        if ($ars instanceof Group) {
            $userCount = $this->userRepo->countUsersOfGroup($ars);
            $userWithRoleCount = $this->userRepo->countUsersOfGroupByRole($ars, $role);

            return $total + $userCount - $userWithRoleCount < $role->getMaxUsers();
        }

        return false;
    }

    public function validateNewUserRolesInsert(array $roles)
    {
        $unavailableRoles = [];

        foreach ($roles as $role) {
            $isAvailable = $this->validateRoleInsert(new User(), $role);

            if (!$isAvailable) {
                $unavailableRoles[] = $role;
            }
        }
        $roleUser = $this->getRoleByName('ROLE_USER');
        $isAvailable = $this->validateRoleInsert(new User(), $roleUser);

        if (!$isAvailable) {
            $unavailableRoles[] = $roleUser;
        }

        return array_unique($unavailableRoles);
    }

    /**
     * @param Role $role
     *
     * @return bool
     */
    public function countUsersByRoleIncludingGroup(Role $role)
    {
        return $this->om->getRepository('ClarolineCoreBundle:User')->countUsersByRoleIncludingGroup($role);
    }

    public function getRolesWithRightsByResourceNode(
        ResourceNode $resourceNode,
        $executeQuery = true
    ) {
        return $this->roleRepo
            ->findRolesWithRightsByResourceNode($resourceNode, $executeQuery);
    }

    /**
     * This functions sets the role limit equal to the current number of users having that role.
     *
     * @param Role $role
     */
    public function initializeLimit(Role $role)
    {
        $count = $this->countUsersByRoleIncludingGroup($role);
        $role->setMaxUsers($count);
        $this->om->persist($role);
        $this->om->flush();
    }

    /**
     * @param Role $role
     * @param $limit
     */
    public function increaseRoleMaxUsers(Role $role, $limit)
    {
        $role->setMaxUsers($role->getMaxUsers() + $limit);
        $this->om->persist($role);
        $this->om->flush();
    }

    /**
     * @param string $workspaceCode
     * @param string $translationKey
     * @param bool   $executeQuery
     */
    public function getRoleByWorkspaceCodeAndTranslationKey(
        $workspaceCode,
        $translationKey,
        $executeQuery = true
    ) {
        return $this->roleRepo->findRoleByWorkspaceCodeAndTranslationKey(
            $workspaceCode,
            $translationKey,
            $executeQuery
        );
    }

    /**
     * @param string $workspaceCode
     * @param string $translationKey
     * @param bool   $executeQuery
     */
    public function getRolesByWorkspaceCodeAndTranslationKey(
        $workspaceCode,
        $translationKey,
        $executeQuery = true
    ) {
        return $this->roleRepo->findRolesByWorkspaceCodeAndTranslationKey(
            $workspaceCode,
            $translationKey,
            $executeQuery
        );
    }

    /**
     * Returns all non-platform roles of a user.
     *
     * @param User $user The subject of the role
     *
     * @return array[Role]|query
     */
    public function getNonPlatformRolesForUser(User $user, $executeQuery = true)
    {
        return $this->roleRepo->findNonPlatformRolesForUser($user, $executeQuery);
    }

    public function getWorkspaceRoleBaseName(Role $role)
    {
        if ($role->getWorkspace()) {
            return substr($role->getName(), 0, strrpos($role->getName(), '_'));
        }

        return $role->getName();
    }

    public function getAllUserRoles($executeQuery = true)
    {
        return $this->roleRepo->findAllUserRoles($executeQuery);
    }

    public function getUserRolesByTranslationKeys(array $keys, $executeQuery = true)
    {
        return 0 === count($keys) ?
            [] :
            $this->roleRepo->findUserRolesByTranslationKeys($keys, $executeQuery);
    }

    public function getWorkspaceRoleWithToolAccess(Workspace $workspace)
    {
        return $this->roleRepo->findWorkspaceRoleWithToolAccess($workspace);
    }

    public function getRoleByUserAndRoleName(User $user, $roleName, $executeQuery = true)
    {
        return $this->roleRepo->findRoleByUserAndRoleName($user, $roleName, $executeQuery);
    }

    public function getWorkspaceRoleByNameOrTranslationKey(
        Workspace $workspace,
        $translationKey,
        $executeQuery = true
    ) {
        return $this->roleRepo->findWorkspaceRoleByNameOrTranslationKey(
            $workspace,
            $translationKey,
            $executeQuery
        );
    }

    public function invertWorkspaceCreation(Role $role)
    {
        $role->setPersonalWorkspaceCreationEnabled(!$role->isPersonalWorkspaceCreationEnabled());
        $this->om->persist($role);
        $this->om->flush();
    }

    public function associateWorkspaceRolesByImport(Workspace $workspace, array $datas)
    {
        $this->om->startFlushSuite();
        $i = 1;

        foreach ($datas as $data) {
            $username = $data[0];
            $roleName = $data[1];
            $firstName = isset($data[2]) ? $data[2] : null;
            $lastName = isset($data[3]) ? $data[3] : null;

            $user = null;
            if (!empty($username)) {
                $user = $this->userRepo->findOneUserByUsername($username);
            } elseif (!empty($firstName) && !empty($lastName)) {
                $user = $this->userRepo->findOneBy([
                    'firstName' => $firstName,
                    'lastName' => $lastName,
                ]);
            }

            $roles = $this->roleRepo->findRolesByWorkspaceCodeAndTranslationKey(
                $workspace->getCode(),
                $roleName
            );

            if (!is_null($user) && count($roles) > 0) {
                $this->associateRoles($user, $roles);
            }

            if (0 === $i % 100) {
                $this->om->forceFlush();
            }
            ++$i;
        }
        $this->om->endFlushSuite();
    }

    public function getRoleOptions(Role $role)
    {
        $roleOptions = $this->roleOptionsRepo->findOneByRole($role);

        if (is_null($roleOptions)) {
            $roleOptions = new RoleOptions();
            $roleOptions->setRole($role);
            $roleOptions->setDetails(['home_lock' => false]);
            $this->om->persist($roleOptions);
            $this->om->flush();
        }

        return $roleOptions;
    }

    public function persistRoleOptions(RoleOptions $roleOptions)
    {
        $this->om->persist($roleOptions);
        $this->om->flush();
    }

    public function isHomeLocked(User $user)
    {
        $adminRole = $this->getRoleByUserAndRoleName($user, 'ROLE_ADMIN');
        $isLocked = is_null($adminRole);

        if ($isLocked) {
            $roles = $this->getPlatformRoles($user);
            $rolesOptions = $this->getRoleOptionsByRoles($roles);

            foreach ($rolesOptions as $options) {
                $details = $options->getDetails();

                if (!isset($details['home_lock']) || !$details['home_lock']) {
                    $isLocked = false;
                    break;
                }
            }
        }

        return $isLocked;
    }

    public function getRoleOptionsByRoles(array $roles)
    {
        return count($roles) > 0 ?
            $this->roleOptionsRepo->findRoleOptionsByRoles($roles) :
            [];
    }

    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function checkIntegrity($workspaceIdx = 0, $userIdx = 0)
    {
        // Define load batch size, and flush size
        $batchSize = 1000;
        $flushSize = 250;
        // Check workspaces roles
        $this->log('Checking workspace roles integrity... This may take a while.');
        $totalWs = $this->workspaceRepo->countWorkspaces();
        $this->log("Checking {$totalWs} workspaces role integrity!");
        $i = $workspaceIdx;
        $this->om->startFlushSuite();
        for ($batch = 0; $batch < ceil(($totalWs - $workspaceIdx) / $batchSize); ++$batch) {
            $workspaces = $this->workspaceRepo->findAllPaginated($batch * $batchSize + $workspaceIdx, $batchSize);
            $nb = count($workspaces);
            $this->log("Fetched {$nb} workspaces for checking");
            $j = 1;
            foreach ($workspaces as $workspace) {
                ++$i;
                $operationExecuted = $this->checkWorkspaceIntegrity($workspace, $i, $totalWs);

                if ($operationExecuted) {
                    ++$j;
                }

                if (0 === $j % $flushSize) {
                    $this->log('Flushing, this may be very long for large databases');
                    $this->om->forceFlush();
                    $j = 1;
                }
            }
            if ($j > 1) {
                $this->log('Flushing, this may be very long for large databases');
                $this->om->forceFlush();
            }
            $this->om->clear();
        }
        $this->om->endFlushSuite();
        // Check users' roles
        $this->log('Checking user role integrity.');
        $userManager = $this->container->get('claroline.manager.user_manager');
        $totalUsers = $userManager->getCountAllEnabledUsers();
        $i = $userIdx;
        $this->om->startFlushSuite();
        for ($batch = 0; $batch < ceil(($totalUsers - $userIdx) / $batchSize); ++$batch) {
            $users = $userManager
                ->getAllEnabledUsers(false)
                ->setMaxResults($batchSize)
                ->setFirstResult($batch * $batchSize + $userIdx)
                ->getResult();
            $nb = count($users);
            $this->log("Fetched {$nb} users for checking");
            $j = 1;

            foreach ($users as $user) {
                ++$i;
                $operationExecuted = $this->checkUserIntegrity($user, $i, $totalUsers);

                if ($operationExecuted) {
                    ++$j;
                }

                if (0 === $j % $flushSize) {
                    $this->log('Flushing, this may be very long for large databases');
                    $this->om->forceFlush();
                    $j = 1;
                }
            }

            if ($j > 1) {
                $this->log('Flushing, this may be very long for large databases');
                $this->om->forceFlush();
            }
            $this->om->clear();
        }
        $this->om->endFlushSuite();
    }

    public function checkUserIntegrity(User $user, $i = 1, $totalUsers = 1)
    {
        $this->log('Checking personal role for '.$user->getUsername()." ($i/$totalUsers)");
        $roleName = 'ROLE_USER_'.strtoupper($user->getUsername());
        $role = $this->roleRepo->findOneByName($roleName);

        if (!$role) {
            $this->log('Adding user role for '.$user->getUsername(), LogLevel::DEBUG);
            $this->createUserRole($user);

            return true;
        }

        return false;
    }

    public function checkWorkspaceIntegrity(Workspace $workspace, $i = 1, $totalWs = 1)
    {
        $this->log('Checking roles integrity for workspace '.$workspace->getCode()." ($i/$totalWs)");
        $collaborator = $this->getCollaboratorRole($workspace);
        $manager = $this->getManagerRole($workspace);
        $operationExecuted = false;

        if (!$collaborator) {
            // Create collaborator role
            $this->log('Adding collaborator role for workspace '.$workspace->getCode().'...', LogLevel::DEBUG);
            $role = $this->createWorkspaceRole(
                'ROLE_WS_COLLABORATOR_'.$workspace->getGuid(),
                'collaborator',
                $workspace,
                true
            );
            // And restore role for root resource
            $this->restoreRolesForRootResource($workspace, [$role]);
            $operationExecuted = true;
        } else {
            $operationExecuted = $this->restoreRolesForRootResource($workspace);
        }

        if (!$manager) {
            $this->log('Adding manager role for workspace '.$workspace->getCode().'...', LogLevel::DEBUG);
            $manager = $this->createWorkspaceRole(
                'ROLE_WS_MANAGER_'.$workspace->getGuid(),
                'manager',
                $workspace,
                true
            );
            $operationExecuted = true;
        }

        if ($creator = $workspace->getCreator()) {
            $creator->addRole($manager);
        }

        return $operationExecuted;
    }

    public function getUserRole($username)
    {
        return $this->roleRepo->findUserRoleByUsername($username);
    }

    public function emptyRole(Role $role, $mode)
    {
        if (self::EMPTY_USERS === $mode) {
            $users = $role->getUsers();

            foreach ($users as $user) {
                $user->removeRole($role);
                $this->om->persist($user);
            }
        }
        if (self::EMPTY_GROUPS === $mode) {
            $groups = $role->getGroups();

            foreach ($groups as $group) {
                $group->removeRole($role);
                $this->om->persist($group);
            }
        }

        $this->om->persist($role);
        $this->om->flush();
    }

    private function restoreRolesForRootResource(Workspace $workspace, array $roles = [])
    {
        $operationExecuted = false;
        try {
            $root = $this->container->get('claroline.manager.resource_manager')->getWorkspaceRoot($workspace);

            if ($root) {
                if (empty($roles)) {
                    $roles = $workspace->getRoles();
                }

                foreach ($roles as $role) {
                    $hasRole = false;
                    foreach ($root->getRights() as $perm) {
                        if ($perm->getRole() === $role || 'manager' === $role->getTranslationKey()) {
                            $hasRole = true;
                        }
                    }

                    if (!$hasRole) {
                        $operationExecuted = true;
                        $this->log('Restoring '.$role->getTranslationKey().' role for root resource of '.$workspace->getCode(), LogLevel::ERROR);
                        $this->container->get('claroline.manager.rights_manager')
                            ->create(
                                ['open' => true, 'export' => true],
                                $role,
                                $root,
                                true
                            );
                    }
                }
            } else {
                $this->log('No directory root for '.$workspace->getCode());
            }
        } catch (NonUniqueResultException $e) {
            $this->log('Multiple roots for '.$workspace->getCode(), LogLevel::ERROR);
        }

        return $operationExecuted;
    }

    public function save(Role $role)
    {
        $this->om->persist($role);
        $this->om->flush();
    }
}
