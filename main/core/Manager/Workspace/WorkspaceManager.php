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
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\AuthenticationBundle\Security\Authentication\Token\ViewAsToken;
use Claroline\BundleRecorder\Log\LoggableTrait;
use Claroline\CoreBundle\Entity\AbstractRoleSubject;
use Claroline\CoreBundle\Entity\Organization\Organization;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Shortcuts;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Entity\Workspace\WorkspaceOptions;
use Claroline\CoreBundle\Entity\Workspace\WorkspaceRegistrationQueue;
use Claroline\CoreBundle\Library\Security\Utilities;
use Claroline\CoreBundle\Manager\ResourceManager;
use Claroline\CoreBundle\Manager\RoleManager;
use Claroline\CoreBundle\Repository\UserRepository;
use Claroline\CoreBundle\Repository\WorkspaceRepository;
use Doctrine\Common\Persistence\ObjectRepository;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

class WorkspaceManager
{
    use LoggableTrait;

    const MAX_WORKSPACE_BATCH_SIZE = 10;

    /** @var RoleManager */
    private $roleManager;
    /** @var ResourceManager */
    private $resourceManager;
    /** @var StrictDispatcher */
    private $dispatcher;
    /** @var ObjectManager */
    private $om;
    private $sut;
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

    /**
     * WorkspaceManager constructor.
     *
     * @param RoleManager        $roleManager
     * @param ResourceManager    $resourceManager
     * @param StrictDispatcher   $dispatcher
     * @param ObjectManager      $om
     * @param Utilities          $sut
     * @param ContainerInterface $container
     */
    public function __construct(
        RoleManager $roleManager,
        ResourceManager $resourceManager,
        StrictDispatcher $dispatcher,
        ObjectManager $om,
        Utilities $sut,
        ContainerInterface $container
    ) {
        $this->roleManager = $roleManager;
        $this->resourceManager = $resourceManager;
        $this->sut = $sut;
        $this->om = $om;
        $this->dispatcher = $dispatcher;
        $this->container = $container;
        $this->crud = $container->get('Claroline\AppBundle\API\Crud');

        $this->userRepo = $om->getRepository(User::class);
        $this->workspaceRepo = $om->getRepository(Workspace::class);
        $this->workspaceOptionsRepo = $om->getRepository(WorkspaceOptions::class);
        $this->shortcutsRepo = $om->getRepository(Shortcuts::class);
    }

    /**
     * Rename a workspace.
     *
     * @param Workspace $workspace
     * @param string    $name
     */
    public function rename(Workspace $workspace, $name)
    {
        $workspace->setName($name);
        $root = $this->resourceManager->getWorkspaceRoot($workspace);
        if ($root) {
            $root = $this->resourceManager->getWorkspaceRoot($workspace);
            $root->setName($name);
            $this->om->persist($root);
        }

        $this->om->persist($workspace);

        $this->om->flush();
    }

    /**
     * @param User $user
     *
     * @return Workspace[]
     */
    public function getWorkspacesByUser(User $user)
    {
        return $this->workspaceRepo->findByUser($user);
    }

    /**
     * @return int
     */
    public function getNbPersonalWorkspaces()
    {
        return $this->workspaceRepo->countPersonalWorkspaces();
    }

    /**
     * @param Organization[] $organizations
     *
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
     * @param TokenInterface $token
     * @param Workspace[]    $workspaces
     * @param string|null    $toolName
     * @param string         $action
     *
     * @return bool[]
     */
    public function getAccesses(
        TokenInterface $token,
        array $workspaces,
        $toolName = null,
        $action = 'open'
    ) {
        $userRoleNames = $this->sut->getRoles($token);
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
        $workspaceRegistrationQueueRepo =
            $this->om->getRepository('ClarolineCoreBundle:Workspace\WorkspaceRegistrationQueue');
        $userQueued = $workspaceRegistrationQueueRepo->findOneBy(['workspace' => $workspace, 'user' => $user]);

        return !empty($userQueued);
    }

    /**
     * @param Workspace $workspace
     * @param User      $user
     *
     * @return User
     */
    public function addUser(Workspace $workspace, User $user)
    {
        $role = $workspace->getDefaultRole();
        $this->roleManager->associateRole($user, $role);

        // nope
        if ($user->getUuid() === $this->container->get('security.token_storage')->getToken()->getUser()->getUuid()) {
            $token = new UsernamePasswordToken($user, null, 'main', $user->getRoles());
            $this->container->get('security.token_storage')->setToken($token);
        }

        return $user;
    }

    /**
     * Get the workspace storage directory.
     *
     * @param Workspace $workspace
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
     * @param Workspace $workspace
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
                'hide_tools_menu' => false,
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

    public function setLogger(LoggerInterface $logger)
    {
        if (!$this->resourceManager->getLogger()) {
            $this->resourceManager->setLogger($logger);
        }

        $this->logger = $logger;
    }

    public function getLogger()
    {
        return $this->logger;
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

        //or we have the role_manager
        $managerRole = $workspace->getManagerRole();
        foreach ($token->getRoles() as $role) {
            if (($managerRole && $managerRole->getName() === $role->getRole()) || 'ROLE_ADMIN' === $role->getRole()) {
                return true;
            }
        }

        return false;
    }

    public function isImpersonated(TokenInterface $token)
    {
        if ($token instanceof ViewAsToken) {
            return true;
        }

        foreach ($token->getRoles() as $role) {
            if ('ROLE_USURPATE_WORKSPACE_ROLE' === $role->getRole()) {
                return true;
            }
        }

        return false;
    }

    /**
     * Gets the list of role which have access to the workspace.
     * (either workspace roles or a platform role with ws tool access).
     *
     * @param Workspace $workspace
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

            if ($user->getUuid() === $this->container->get('security.token_storage')->getToken()->getUser()->getUuid()) {
                $token = new UsernamePasswordToken($user, null, 'main', $user->getRoles());
                $this->container->get('security.token_storage')->setToken($token);
            }
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
            $this->log('Build and set default admin');
            $workspace->setCreator($this->container->get('claroline.manager.user_manager')->getDefaultClarolineAdmin());
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
            $this->roleManager->dissociateRole($subject, $role);
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

    public function setWorkspacesFlag()
    {
        /** @var Workspace[] $workspaces */
        $workspaces = $this->container->get('Claroline\AppBundle\API\FinderProvider')->fetch(Workspace::class, [
            'name' => 'Espace personnel',
            'meta.personal' => false,
            //maybe add personal user here
        ]);

        $i = 0;
        $total = count($workspaces);

        foreach ($workspaces as $workspace) {
            $workspace->setPersonal(true);
            $this->om->persist($workspace);

            ++$i;

            $this->log('Restore workspace personal flag for '.$workspace->getName().' '.$i.'/'.$total);

            if (0 === $i % 500) {
                $this->log('Flushing...');
                $this->om->flush();
            }
        }

        $this->log('Flushing...');
        $this->om->flush();
    }

    public function getShortcuts(Workspace $workspace, User $user = null)
    {
        $shortcuts = [];
        if ($user) {
            foreach ($workspace->getShortcuts() as $shortcut) {
                if ($user->hasRole($shortcut->getRole()->getName())) {
                    $shortcuts = array_merge($shortcuts, $shortcut->getData());
                }
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
