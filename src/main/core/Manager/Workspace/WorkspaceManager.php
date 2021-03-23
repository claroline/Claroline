<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Manager\Workspace;

use Claroline\AppBundle\API\Crud;
use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\Utils\FileBag;
use Claroline\AppBundle\Event\StrictDispatcher;
use Claroline\AppBundle\Log\LoggableTrait;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\AbstractRoleSubject;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Shortcuts;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Entity\Workspace\WorkspaceOptions;
use Claroline\CoreBundle\Entity\Workspace\WorkspaceRegistrationQueue;
use Claroline\CoreBundle\Event\CatalogEvents\SecurityEvents;
use Claroline\CoreBundle\Event\Security\AddRoleEvent;
use Claroline\CoreBundle\Manager\ResourceManager;
use Claroline\CoreBundle\Manager\RoleManager;
use Claroline\CoreBundle\Repository\User\UserRepository;
use Claroline\CoreBundle\Repository\WorkspaceRepository;
use Doctrine\Persistence\ObjectRepository;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class WorkspaceManager implements LoggerAwareInterface
{
    use LoggableTrait;

    /** @var TranslatorInterface */
    private $translator;
    /** @var RoleManager */
    private $roleManager;
    /** @var ResourceManager */
    private $resourceManager;
    /** @var StrictDispatcher */
    private $dispatcher;
    /** @var ObjectManager */
    private $om;
    /** @var Crud */
    private $crud;
    private $container;

    private $shortcutsRepo;
    /** @var UserRepository */
    private $userRepo;
    /** @var WorkspaceRepository */
    private $workspaceRepo;
    /** @var ObjectRepository */
    private $workspaceOptionsRepo;

    public function __construct(
        TranslatorInterface $translator,
        Crud $crud,
        RoleManager $roleManager,
        ResourceManager $resourceManager,
        StrictDispatcher $dispatcher,
        ObjectManager $om,
        ContainerInterface $container
    ) {
        $this->translator = $translator;
        $this->roleManager = $roleManager;
        $this->resourceManager = $resourceManager;
        $this->om = $om;
        $this->dispatcher = $dispatcher;
        $this->container = $container;
        $this->crud = $crud;

        $this->userRepo = $om->getRepository(User::class);
        $this->workspaceRepo = $om->getRepository(Workspace::class);
        $this->workspaceOptionsRepo = $om->getRepository(WorkspaceOptions::class);
        $this->shortcutsRepo = $om->getRepository(Shortcuts::class);
    }

    /**
     * Rename a workspace.
     *
     * @param string $name
     */
    public function rename(Workspace $workspace, $name)
    {
        $workspace->setName($name);

        $root = $this->resourceManager->getWorkspaceRoot($workspace);
        if ($root) {
            $root->setName($name);
            $this->om->persist($root);
        }

        $this->om->persist($workspace);
        $this->om->flush();
    }

    /**
     * Creates the personal workspace of a user.
     */
    public function setPersonalWorkspace(User $user, Workspace $model = null)
    {
        if ($user->getLocale()) {
            $this->translator->setLocale($user->getLocale());
        }
        $created = $this->om->getRepository(Workspace::class)->findOneBy(['code' => $user->getUsername()]);

        if ($created) {
            $code = $user->getUsername().'~'.uniqid();
        } else {
            $code = $user->getUsername();
        }

        $personalWorkspaceName = $this->translator->trans('personal_workspace', [], 'platform').' - '.$user->getUsername();
        $workspace = new Workspace();
        $workspace->setCode($code);
        $workspace->setName($personalWorkspaceName);
        $workspace->setCreator($user);

        $workspace = !$model ?
            $this->copy($this->getDefaultModel(true), $workspace) :
            $this->copy($model, $workspace);

        $workspace->setPersonal(true);

        $user->setPersonalWorkspace($workspace);
        $user->addRole($workspace->getManagerRole());
        $this->dispatcher->dispatch(SecurityEvents::ADD_ROLE, AddRoleEvent::class, [$user, $workspace->getManagerRole()]);

        $this->om->persist($user);
        $this->om->flush();
    }

    /**
     * @return Workspace[]
     */
    public function getWorkspacesByUser(User $user)
    {
        return $this->workspaceRepo->findByUser($user);
    }

    /**
     * @return int
     */
    public function getNbNonPersonalWorkspaces($organizations = null)
    {
        return $this->workspaceRepo->countNonPersonalWorkspaces($organizations);
    }

    /**
     * Returns the accesses rights of a given token for a set of workspaces.
     * If a tool name is passed in, the check will be limited to that tool,
     * otherwise workspaces with at least one accessible tool will be
     * considered open. Access to any tool is always granted to platform
     * administrators and workspace managers.
     *
     * The returned value is an associative array in which
     * keys are workspace ids and values are boolean indicating if the
     * workspace is open.
     *
     * @param Workspace[] $workspaces
     * @param string|null $toolName
     * @param string      $action
     *
     * @return bool[]
     */
    public function getAccesses(
        TokenInterface $token,
        array $workspaces,
        $toolName = null,
        $action = 'open'
    ) {
        $userRoleNames = $token->getRoleNames();
        $accesses = [];

        if (in_array('ROLE_ADMIN', $userRoleNames)) {
            foreach ($workspaces as $workspace) {
                $accesses[$workspace->getId()] = true;
            }

            return $accesses;
        }

        $hasAllAccesses = true;
        $workspacesWithoutManagerRole = [];

        foreach ($workspaces as $workspace) {
            if (in_array('ROLE_WS_MANAGER_'.$workspace->getUuid(), $userRoleNames)) {
                $accesses[$workspace->getId()] = true;
            } else {
                $accesses[$workspace->getId()] = $hasAllAccesses = false;
                $workspacesWithoutManagerRole[] = $workspace;
            }
        }

        if (!$hasAllAccesses) {
            $openWsIds = $this->workspaceRepo->findOpenWorkspaceIds(
                $userRoleNames,
                $workspacesWithoutManagerRole,
                $toolName,
                $action
            );

            foreach ($openWsIds as $idRow) {
                $accesses[$idRow['id']] = true;
            }
        }

        return $accesses;
    }

    /**
     * @param int $max
     *
     * @return Workspace[]
     */
    public function getWorkspacesWithMostResources($max, $organizations = null)
    {
        return $this->workspaceRepo->findWorkspacesWithMostResources($max, $organizations);
    }

    public function addUserQueue(Workspace $workspace, User $user) // TODO : move in WorkspaceUserQueueManager
    {
        $wksrq = new WorkspaceRegistrationQueue();
        $wksrq->setUser($user);
        $role = $workspace->getDefaultRole();
        $wksrq->setRole($role);
        $wksrq->setWorkspace($workspace);
        $this->dispatcher->dispatch(
            'log',
            'Log\LogWorkspaceRegistrationQueue',
            [$wksrq]
        );
        $this->om->persist($wksrq);
        $this->om->flush();
    }

    public function isUserInValidationQueue(Workspace $workspace, User $user) // TODO : move in WorkspaceUserQueueManager
    {
        $workspaceRegistrationQueueRepo = $this->om->getRepository(WorkspaceRegistrationQueue::class);
        $userQueued = $workspaceRegistrationQueueRepo->findOneBy(['workspace' => $workspace, 'user' => $user]);

        return !empty($userQueued);
    }

    public function addUser(Workspace $workspace, User $user): User
    {
        if ($workspace->getDefaultRole() && !$user->hasRole($workspace->getDefaultRole()->getName())) {
            $this->crud->patch($user, 'role', Crud::COLLECTION_ADD, [$workspace->getDefaultRole()]);
        }

        return $user;
    }

    /**
     * Get the workspace storage directory.
     *
     * @return string
     */
    public function getStorageDirectory(Workspace $workspace)
    {
        $ds = DIRECTORY_SEPARATOR;

        return $this->container->getParameter('claroline.param.files_directory').$ds.'WORKSPACE_'.$workspace->getId();
    }

    /**
     * Get the current used storage in a workspace.
     *
     * @return int
     */
    public function getUsedStorage(Workspace $workspace)
    {
        $dir = $this->getStorageDirectory($workspace);
        $size = 0;

        if (!is_dir($dir)) {
            return $size;
        }

        foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dir)) as $file) {
            $size += $file->getSize();
        }

        return $size;
    }

    public function getWorkspaceOptions(Workspace $workspace)
    {
        $workspaceOptions = $this->workspaceOptionsRepo->findOneBy(['workspace' => $workspace]);

        //might not be required
        if (!$workspaceOptions) {
            $scheduledForInsert = $this->om->getUnitOfWork()->getScheduledEntityInsertions();

            foreach ($scheduledForInsert as $entity) {
                if (WorkspaceOptions::class === get_class($entity)) {
                    if ($entity->getWorkspace() && $entity->getWorkspace()->getCode() === $workspace->getCode()) {
                        $workspaceOptions = $entity;
                    }
                }
            }
        }

        if (!$workspaceOptions) {
            $workspaceOptions = new WorkspaceOptions();
            $workspaceOptions->setWorkspace($workspace);
            $details = [
                'hide_tools_menu' => null,
                'background_color' => null,
                'hide_breadcrumb' => false,
                'use_workspace_opening_resource' => false,
                'workspace_opening_resource' => null,
            ];
            $workspaceOptions->setDetails($details);
            $workspace->setOptions($workspaceOptions);
            $this->om->persist($workspaceOptions);
            $this->om->persist($workspace);
            $this->om->flush();
        }

        return $workspaceOptions;
    }

    public function setLogger(LoggerInterface $logger = null)
    {
        $this->logger = $logger;
        $this->resourceManager->setLogger($logger);
    }

    public function getPersonalWorkspaceExcludingRoles(array $roles, $includeOrphans, $empty = false, $offset = null, $limit = null)
    {
        return $this->workspaceRepo->findPersonalWorkspaceExcludingRoles($roles, $includeOrphans, $empty, $offset, $limit);
    }

    public function getPersonalWorkspaceByRolesIncludingGroups(array $roles, $includeOrphans, $empty = false, $offset = null, $limit = null)
    {
        return $this->workspaceRepo->findPersonalWorkspaceByRolesIncludingGroups($roles, $includeOrphans, $empty, $offset, $limit);
    }

    public function getNonPersonalByCodeAndName($code, $name, $offset = null, $limit = null)
    {
        return !$code && !$name ?
            $this->workspaceRepo->findBy(['personal' => false]) :
            $this->workspaceRepo->findNonPersonalByCodeAndName($code, $name, $offset, $limit);
    }

    //this is not a very effective method =/
    public function isRegistered(Workspace $workspace, User $user)
    {
        $userRoles = $user->getRoles();
        $workspaceRoles = $workspace->getRoles();

        foreach ($userRoles as $userRole) {
            foreach ($workspaceRoles as $workspaceRole) {
                if ($workspaceRole->getName() === $userRole) {
                    return true;
                }
            }
        }

        return false;
    }

    public function isManager(Workspace $workspace, TokenInterface $token)
    {
        if (!$token->getUser() instanceof User) {
            return false;
        }

        if (in_array('ROLE_ADMIN', $token->getRoleNames())) {
            // this should be checked at a higher level
            return true;
        }

        //or we have the role_manager
        $managerRole = $workspace->getManagerRole();
        if ($managerRole && in_array($managerRole->getName(), $token->getRoleNames())) {
            return true;
        }

        if (!$this->isImpersonated($token)) {
            /** @var User $user */
            $user = $token->getUser();

            // we are the creator of the workspace
            if ($workspace->getCreator() === $user) {
                return true;
            }

            //if we're amongst the administrators of the organizations
            $adminOrganizations = $user->getAdministratedOrganizations();
            $workspaceOrganizations = $workspace->getOrganizations();

            foreach ($adminOrganizations as $adminOrganization) {
                foreach ($workspaceOrganizations as $workspaceOrganization) {
                    if ($workspaceOrganization === $adminOrganization) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    public function isImpersonated(TokenInterface $token)
    {
        return in_array('ROLE_USURPATE_WORKSPACE_ROLE', $token->getRoleNames());
    }

    public function getTokenRoles(TokenInterface $token, Workspace $workspace)
    {
        return array_values(array_filter($workspace->getRoles()->toArray(), function (Role $role) use ($token) {
            return in_array($role->getName(), $token->getRoleNames());
        }));
    }

    /**
     * Gets the list of role which have access to the workspace.
     * (either workspace roles or a platform role with ws tool access).
     *
     * @return Role[]
     */
    public function getRolesWithAccess(Workspace $workspace)
    {
        return $this->roleManager->getWorkspaceRoleWithToolAccess($workspace);
    }

    /**
     * Copies a Workspace.
     *
     * @param Workspace $workspace    - the original workspace to copy
     * @param Workspace $newWorkspace - the copy
     * @param bool      $model        - if true, the new workspace will be a model
     *
     * @return Workspace
     */
    public function copy(Workspace $workspace, Workspace $newWorkspace, $model = false)
    {
        /** @var TransferManager $transferManager */
        $transferManager = $this->container->get('claroline.manager.workspace.transfer');

        $fileBag = new FileBag();
        //these are the new workspace data
        $data = $transferManager->serialize($workspace);
        $data = $transferManager->exportFiles($data, $fileBag, $workspace);

        if ($this->logger) {
            $transferManager->setLogger($this->logger);
        }

        if ($newWorkspace->getCode()) {
            unset($data['code']);
        }

        if ($newWorkspace->getName()) {
            unset($data['name']);
        }

        $workspaceCopy = $transferManager->deserialize($data, $newWorkspace, [Options::REFRESH_UUID], $fileBag);

        $workspaceCopy->setModel($model);

        // set the manager role
        $managerRole = $this->roleManager->getManagerRole($workspaceCopy);
        if ($managerRole && $workspaceCopy->getCreator()) {
            $user = $workspaceCopy->getCreator();
            $user->addRole($managerRole);
            $this->om->persist($user);
            $this->dispatcher->dispatch(SecurityEvents::ADD_ROLE, AddRoleEvent::class, [$user, $managerRole]);
        }

        $root = $this->resourceManager->getWorkspaceRoot($workspaceCopy);

        if ($root) {
            $this->resourceManager->createRights($root, [], true, false);
        }

        // Copy workspace shortcuts
        /** @var Shortcuts[] $workspaceShortcuts */
        $workspaceShortcuts = $this->shortcutsRepo->findBy(['workspace' => $workspace]);

        foreach ($workspaceShortcuts as $shortcuts) {
            $role = $shortcuts->getRole();

            $roleName = preg_replace('/'.$workspace->getUuid().'$/', '', $role->getName()).$workspaceCopy->getUuid();
            $roleCopy = $this->roleManager->getRoleByName($roleName);

            if ($roleCopy) {
                $shortcutsCopy = new Shortcuts();
                $shortcutsCopy->setWorkspace($workspaceCopy);
                $shortcutsCopy->setRole($roleCopy);
                $shortcutsCopy->setData($shortcuts->getData());
                $this->om->persist($shortcutsCopy);
            }
        }

        $this->om->persist($workspaceCopy);
        $this->om->flush();

        $transferManager->dispatch('create', 'post', [$workspaceCopy]);

        return $workspaceCopy;
    }

    public function archive(Workspace $workspace)
    {
        //rename with [archive] and ids
        $workspace->setName('[archive]'.$workspace->getName());
        $workspace->setCode('[archive]'.$workspace->getCode().uniqid());
        $workspace->setArchived(true);

        $this->om->persist($workspace);

        return $workspace;
    }

    public function unarchive(Workspace $workspace)
    {
        $workspace->setArchived(false);

        $this->om->persist($workspace);

        return $workspace;
    }

    public function getDefaultModel($isPersonal = false, $restore = false)
    {
        $name = $isPersonal ? 'default_personal' : 'default_workspace';
        $this->log('Search old default workspace '.$name);
        $workspace = $this->workspaceRepo->findOneBy(['code' => $name]);

        if (!$workspace || $restore) {
            $this->log('Rebuilding...');
            //don't log this or it'll crash everything during the platform installation
            //(some database tables aren't already created because they come from plugins)
            if ($workspace && $restore) {
                $this->log('Removing workspace...');
                $this->om->remove($workspace);
                $this->om->flush();
            }

            $this->container->get('Claroline\CoreBundle\Listener\Log\LogListener')->disable();

            $this->log('Build from json...');
            $zip = new \ZipArchive();
            $zip->open($this->container->getParameter('claroline.param.workspace.default'));
            $json = $zip->getFromName('workspace.json');
            $data = json_decode($json, true);
            $data['code'] = $data['name'] = $name;
            $this->container->get('claroline.manager.workspace.transfer')->setLogger($this->logger);
            $workspace = new Workspace();
            $workspace->setName($name);
            $workspace->setPersonal($isPersonal);
            $workspace->setCode($name);
            /** @var Workspace $workspace */
            $workspace = $this->container->get('claroline.manager.workspace.transfer')->create($data, $workspace);
            //just in case
            $workspace->setName($name);
            $workspace->setPersonal($isPersonal);
            $workspace->setCode($name);
            $workspace->setModel(true);
            $this->log('Add tools...');
            $this->container->get('claroline.manager.tool_manager')->addMissingWorkspaceTools($workspace);
            $this->log('Build...');
            $this->container->get('Claroline\CoreBundle\Listener\Log\LogListener')->setDefaults();

            if (0 === count($this->shortcutsRepo->findBy(['workspace' => $workspace]))) {
                $this->log('Generating default shortcuts...');
                $managerRole = $this->roleManager->getManagerRole($workspace);
                $collaboratorRole = $this->roleManager->getCollaboratorRole($workspace);

                if ($managerRole) {
                    $shortcuts = new Shortcuts();
                    $shortcuts->setWorkspace($workspace);
                    $shortcuts->setRole($managerRole);
                    $shortcuts->setData([
                        ['type' => 'tool', 'name' => 'home'],
                        ['type' => 'tool', 'name' => 'resources'],
                        ['type' => 'tool', 'name' => 'agenda'],
                        ['type' => 'tool', 'name' => 'community'],
                        ['type' => 'tool', 'name' => 'dashboard'],
                        ['type' => 'action', 'name' => 'favourite'],
                        ['type' => 'action', 'name' => 'configure'],
                        ['type' => 'action', 'name' => 'impersonation'],
                    ]);
                    $this->om->persist($shortcuts);
                }
                if ($collaboratorRole) {
                    $shortcuts = new Shortcuts();
                    $shortcuts->setWorkspace($workspace);
                    $shortcuts->setRole($collaboratorRole);
                    $shortcuts->setData([
                        ['type' => 'tool', 'name' => 'home'],
                        ['type' => 'tool', 'name' => 'resources'],
                        ['type' => 'tool', 'name' => 'agenda'],
                        ['type' => 'action', 'name' => 'favourite'],
                    ]);
                    $this->om->persist($shortcuts);
                }
            }

            if ($restore) {
                $this->om->persist($workspace);
                $this->om->flush();
            }
        }

        $this->om->forceFlush();

        return $workspace;
    }

    public function unregister(AbstractRoleSubject $subject, Workspace $workspace)
    {
        $rolesToRemove = array_filter($workspace->getRoles()->toArray(), function (Role $role) use ($workspace) {
            return $role->getWorkspace()->getId() === $workspace->getId();
        });

        foreach ($rolesToRemove as $role) {
            if ($subject->hasRole($role->getName())) {
                $this->crud->patch($subject, 'role', Crud::COLLECTION_REMOVE, [$role]);
            }
        }
    }

    public function countUsersForRoles(Workspace $workspace)
    {
        $roles = $workspace->getRoles();

        $usersInRoles = [];
        foreach ($roles as $role) {
            $usersInRoles[] = [
                'name' => $role->getTranslationKey(),
                'total' => floatval($this->userRepo->countUsersByRole($role)),
            ];
        }

        return $usersInRoles;
    }

    public function getShortcuts(Workspace $workspace, array $roleNames = [])
    {
        $shortcuts = [];
        foreach ($workspace->getShortcuts() as $shortcut) {
            if (in_array($shortcut->getRole()->getName(), $roleNames)) {
                $shortcuts = array_merge($shortcuts, $shortcut->getData());
            }
        }

        return $shortcuts;
    }

    public function addShortcuts(Workspace $workspace, Role $role, array $toAdd)
    {
        $workspaceShortcuts = $this->shortcutsRepo->findOneBy(['workspace' => $workspace, 'role' => $role]);

        if (!$workspaceShortcuts) {
            $workspaceShortcuts = new Shortcuts();
            $workspaceShortcuts->setWorkspace($workspace);
            $workspaceShortcuts->setRole($role);
        }
        $data = $workspaceShortcuts->getData();

        foreach ($toAdd as $shortcut) {
            if (Shortcuts::SHORTCUTS_LIMIT > count($data)) {
                $filteredArray = array_filter($data, function ($element) use ($shortcut) {
                    return $element['type'] === $shortcut['type'] && $element['name'] === $shortcut['name'];
                });

                if (0 === count($filteredArray)) {
                    $data[] = $shortcut;
                }
            }
        }
        $workspaceShortcuts->setData($data);
        $this->om->persist($workspaceShortcuts);
        $this->om->flush();
    }

    public function removeShortcut(Workspace $workspace, Role $role, $type, $name)
    {
        $workspaceShortcuts = $this->shortcutsRepo->findOneBy(['workspace' => $workspace, 'role' => $role]);

        if ($workspaceShortcuts) {
            $data = $workspaceShortcuts->getData();
            $newData = [];

            foreach ($data as $shortcut) {
                if ($shortcut['type'] !== $type || $shortcut['name'] !== $name) {
                    $newData[] = $shortcut;
                }
            }
            $workspaceShortcuts->setData($newData);
            $this->om->persist($workspaceShortcuts);
            $this->om->flush();
        }
    }

    /**
     * Generates an unique workspace code from given one by iterating it.
     *
     * @param string $code
     *
     * @return string
     */
    public function getUniqueCode($code)
    {
        $existingCodes = $this->workspaceRepo->findWorkspaceCodesWithPrefix($code);

        $index = count($existingCodes) + 1;
        $currentCode = $code.'_'.$index;
        $upperCurrentCode = strtoupper($currentCode);

        while (in_array($upperCurrentCode, $existingCodes)) {
            ++$index;
            $currentCode = $code.'_'.$index;
            $upperCurrentCode = strtoupper($currentCode);
        }

        return $currentCode;
    }
}
