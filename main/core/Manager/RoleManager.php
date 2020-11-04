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

use Claroline\AppBundle\API\Crud;
use Claroline\AppBundle\Event\StrictDispatcher;
use Claroline\AppBundle\Log\LoggableTrait;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\AbstractRoleSubject;
use Claroline\CoreBundle\Entity\Group;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Exception\AddRoleException;
use Claroline\CoreBundle\Exception\RoleReadOnlyException;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Claroline\CoreBundle\Manager\Template\TemplateManager;
use Claroline\CoreBundle\Repository\User\GroupRepository;
use Claroline\CoreBundle\Repository\User\RoleRepository;
use Claroline\CoreBundle\Repository\User\UserRepository;
use Claroline\CoreBundle\Repository\WorkspaceRepository;
use Doctrine\ORM\NonUniqueResultException;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LogLevel;
use Symfony\Component\DependencyInjection\Container;

class RoleManager implements LoggerAwareInterface
{
    const EMPTY_USERS = 1;
    const EMPTY_GROUPS = 2;

    use LoggableTrait;

    /** @var StrictDispatcher */
    private $dispatcher;
    /** @var ObjectManager */
    private $om;
    /** @var Container */
    private $container;
    /** @var PlatformConfigurationHandler */
    private $configHandler;
    /** @var TemplateManager */
    private $templateManager;
    /** @var Crud */
    private $crud;

    /** @var WorkspaceRepository */
    private $workspaceRepo;
    /** @var RoleRepository */
    private $roleRepo;
    /** @var UserRepository */
    private $userRepo;
    /** @var GroupRepository */
    private $groupRepo;

    public function __construct(
        ObjectManager $om,
        StrictDispatcher $dispatcher,
        Container $container,
        PlatformConfigurationHandler $configHandler,
        TemplateManager $templateManager,
        Crud $crud
    ) {
        $this->om = $om;
        $this->dispatcher = $dispatcher;
        $this->container = $container;
        $this->configHandler = $configHandler;
        $this->templateManager = $templateManager;
        $this->crud = $crud;

        $this->roleRepo = $om->getRepository('ClarolineCoreBundle:Role');
        $this->workspaceRepo = $om->getRepository('ClarolineCoreBundle:Workspace\Workspace');
        $this->userRepo = $om->getRepository('ClarolineCoreBundle:User');
        $this->groupRepo = $om->getRepository('ClarolineCoreBundle:Group');
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
     * @param bool $sendMail
     *
     * @throws AddRoleException
     */
    public function associateRole(AbstractRoleSubject $ars, Role $role, $sendMail = false)
    {
        if (!$this->validateRoleInsert($ars, $role)) {
            throw new AddRoleException('Role cannot be added');
        }

        if ('Claroline\CoreBundle\Entity\Group' === get_class($ars) && 'ROLE_USER' === $role->getName()) {
            throw new AddRoleException('ROLE_USER cannot be added to groups');
        }

        $this->crud->patch($ars, 'role', Crud::COLLECTION_ADD, [$role]);

        if ($sendMail) {
            $withMail = $this->configHandler->getParameter('send_mail_at_workspace_registration');
            $this->sendInscriptionMessage($ars, $role, $withMail);
        }
    }

    public function dissociateRole(AbstractRoleSubject $ars, Role $role)
    {
        if ($ars->hasRole($role->getName())) {
            $this->crud->patch($ars, 'role', Crud::COLLECTION_REMOVE, [$role]);
        }
    }

    /**
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

    /**
     * @param string $name
     *
     * @return Role
     */
    public function getRoleByName($name)
    {
        /** @var Role $role */
        $role = $this->roleRepo->findOneBy(['name' => $name]);

        return $role;
    }

    /**
     * @return Role[]
     */
    public function getAllPlatformRoles()
    {
        return $this->roleRepo->findAllPlatformRoles();
    }

    /**
     * @param string $key - The translation key
     *
     * @return Role
     */
    public function getRoleByTranslationKeyAndWorkspace($key, Workspace $workspace)
    {
        /** @var Role $role */
        $role = $this->roleRepo->findOneBy(['translationKey' => $key, 'workspace' => $workspace]);

        return $role;
    }

    public function edit(Role $role)
    {
        $this->om->persist($role);
        $this->om->flush();
    }

    private function sendInscriptionMessage(AbstractRoleSubject $ars, Role $role, $withMail = true)
    {
        $workspace = $role->getWorkspace();
        $locale = null;
        $placeholders = [
            'role_name' => $role->getTranslationKey(),
        ];

        if ($ars instanceof User) {
            $locale = $ars->getLocale();
            $placeholders['first_name'] = $ars->getFirstName();
            $placeholders['last_name'] = $ars->getLastName();
            $placeholders['username'] = $ars->getUsername();
        }
        //workspace registration
        if ($workspace) {
            $placeholders['workspace_name'] = $workspace->getName();
            $placeholders['workspace_code'] = $workspace->getCode();

            $object = $this->templateManager->getTemplate('workspace_registration', $placeholders, $locale, 'title');
            $content = $this->templateManager->getTemplate('workspace_registration', $placeholders, $locale);
        } else {
            //new role
            $object = $this->templateManager->getTemplate('platform_role_registration', $placeholders, $locale, 'title');
            $content = $this->templateManager->getTemplate('platform_role_registration', $placeholders, $locale);
        }

        $sender = $this->container->get('security.token_storage')->getToken()->getUser();
        $this->dispatcher->dispatch(
            'claroline_message_sending',
            'SendMessage',
            [$sender, $content, $object, $ars, [], $withMail]
        );
    }

    /**
     * Returns if a role can be added to a RoleSubject.
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
            $countByWorkspace = $this->container->get('Claroline\AppBundle\API\FinderProvider')->fetch(
              User::class,
              ['workspace' => $role->getWorkspace()->getUuid()],
              null,
              0,
              -1,
              true
            );

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

    /**
     * @return bool
     */
    public function countUsersByRoleIncludingGroup(Role $role)
    {
        return $this->userRepo->countUsersByRoleIncludingGroup($role);
    }

    /**
     * @param string $workspaceCode
     * @param string $translationKey
     * @param bool   $executeQuery
     *
     * @return Role[]
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
     * @return Role[]
     */
    public function getWorkspaceRoleWithToolAccess(Workspace $workspace)
    {
        return $this->roleRepo->findWorkspaceRoleWithToolAccess($workspace);
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
            /** @var Workspace[] $workspaces */
            $workspaces = $this->workspaceRepo->findBy([], null, $batchSize, $batch * $batchSize + $workspaceIdx);

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
        $totalUsers = $userManager->countEnabledUsers();
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
        /** @var Role $userRole */
        $userRole = $role = $this->roleRepo->findOneBy(['name' => 'ROLE_USER']);
        $this->log('Checking personal role for '.$user->getUsername()." ($i/$totalUsers)");
        $roleName = 'ROLE_USER_'.strtoupper($user->getUsername());
        $role = $this->roleRepo->findOneBy(['name' => $roleName]);
        $user->addRole($userRole);
        $this->om->persist($user);

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
        $this->log('Setting workspace to roles for uuid '.$workspace->getUuid().'...');

        $collaborator = $this->getCollaboratorRole($workspace);
        $manager = $this->getManagerRole($workspace);

        if (!$collaborator) {
            // Create collaborator role
            $this->log('Adding collaborator role for workspace '.$workspace->getCode().'...', LogLevel::DEBUG);
            $role = $this->createWorkspaceRole(
                'ROLE_WS_COLLABORATOR_'.$workspace->getUuid(),
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
                'ROLE_WS_MANAGER_'.$workspace->getUuid(),
                'manager',
                $workspace,
                true
            );
            $operationExecuted = true;
        }

        $creator = $workspace->getCreator();
        if ($creator) {
            $creator->addRole($manager);
        }

        /** @var Role[] $roles */
        $roles = $this->container->get('Claroline\AppBundle\API\FinderProvider')->fetch(Role::class, ['name' => $workspace->getUuid()]);

        foreach ($roles as $role) {
            if (!$role->getWorkspace()) {
                $role->setWorkspace($workspace);
                $this->log('Restoring workspace link for role . '.$role->getName().'...', LogLevel::ERROR);
                $operationExecuted = true;
            }
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
            /** @var ResourceNode $root */
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
