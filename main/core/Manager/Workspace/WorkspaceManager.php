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

use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\Utils\FileBag;
use Claroline\AppBundle\Event\StrictDispatcher;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\BundleRecorder\Log\LoggableTrait;
use Claroline\CoreBundle\Entity\AbstractRoleSubject;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\Tab\HomeTab;
use Claroline\CoreBundle\Entity\Tool\Tool;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Shortcuts;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Entity\Workspace\WorkspaceOptions;
use Claroline\CoreBundle\Entity\Workspace\WorkspaceRegistrationQueue;
use Claroline\CoreBundle\Library\Security\Token\ViewAsToken;
use Claroline\CoreBundle\Library\Security\Utilities;
use Claroline\CoreBundle\Library\Utilities\ClaroUtilities;
use Claroline\CoreBundle\Manager\ResourceManager;
use Claroline\CoreBundle\Manager\RoleManager;
use Claroline\CoreBundle\Repository\UserRepository;
use Claroline\CoreBundle\Repository\WorkspaceRepository;
use Doctrine\Common\Persistence\ObjectRepository;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Role\SwitchUserRole;

class WorkspaceManager
{
    use LoggableTrait;

    const MAX_WORKSPACE_BATCH_SIZE = 10;

    /** @var RoleManager */
    private $roleManager;
    /** @var ResourceManager */
    private $resourceManager;
    /** @var UserRepository */
    private $userRepo;
    /** @var WorkspaceRepository */
    private $workspaceRepo;
    /** @var ObjectRepository */
    private $workspaceOptionsRepo;
    /** @var StrictDispatcher */
    private $dispatcher;
    /** @var ObjectManager */
    private $om;
    /** @var ClaroUtilities */
    private $ut;
    private $sut;
    private $container;
    /** @var array */
    private $importData;
    private $templateDirectory;
    private $shortcutsRepo;

    /**
     * WorkspaceManager constructor.
     *
     * @param RoleManager        $roleManager
     * @param ResourceManager    $resourceManager
     * @param StrictDispatcher   $dispatcher
     * @param ObjectManager      $om
     * @param ClaroUtilities     $ut
     * @param Utilities          $sut
     * @param ContainerInterface $container
     */
    public function __construct(
        RoleManager $roleManager,
        ResourceManager $resourceManager,
        StrictDispatcher $dispatcher,
        ObjectManager $om,
        ClaroUtilities $ut,
        Utilities $sut,
        ContainerInterface $container
    ) {
        $this->roleManager = $roleManager;
        $this->resourceManager = $resourceManager;
        $this->ut = $ut;
        $this->sut = $sut;
        $this->om = $om;
        $this->dispatcher = $dispatcher;
        $this->userRepo = $om->getRepository('ClarolineCoreBundle:User');
        $this->workspaceRepo = $om->getRepository('ClarolineCoreBundle:Workspace\Workspace');
        $this->workspaceOptionsRepo = $om->getRepository('ClarolineCoreBundle:Workspace\WorkspaceOptions');
        $this->container = $container;
        $this->importData = [];
        $this->templateDirectory = $container->getParameter('claroline.param.templates_directory');
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

    //todo: REMOVE me.
    public function createWorkspace(Workspace $workspace)
    {
        if (0 === count($workspace->getOrganizations())) {
            if (
                $this->container->get('security.token_storage')->getToken() &&
                $this->container->get('security.token_storage')->getToken()->getUser() instanceof User &&
                $this->container->get('security.token_storage')->getToken()->getUser()->getMainOrganization()
            ) {
                //we want a min organization
                $workspace->addOrganization($this->container->get('security.token_storage')->getToken()->getUser()->getMainOrganization());
                $this->om->persist($workspace);
                $this->om->flush();
            } else {
                $organizationManager = $this->container->get('claroline.manager.organization.organization_manager');
                $default = $organizationManager->getDefault();
                $workspace->addOrganization($default);
            }
        }

        $ch = $this->container->get('claroline.config.platform_config_handler');
        $workspace->setMaxUploadResources($ch->getParameter('max_upload_resources'));
        $workspace->setMaxStorageSize($ch->getParameter('max_storage_size'));
        $workspace->setMaxUsers($ch->getParameter('max_workspace_users'));

        $this->om->persist($workspace);
        $this->om->flush();

        return $workspace;
    }

    /**
     * Delete a workspace.
     *
     * @param Workspace $workspace
     */
    public function deleteWorkspace(Workspace $workspace)
    {
        // Log action
        $this->dispatcher->dispatch('log', 'Log\LogWorkspaceDelete', [$workspace]);

        $this->om->startFlushSuite();
        $roots = $this->om->getRepository(ResourceNode::class)->findBy(['workspace' => $workspace, 'parent' => null]);

        //in case 0 or multiple due to errors
        foreach ($roots as $root) {
            $this->log('Removing root directory '.$root->getName().'[id:'.$root->getId().']');
            $children = $root->getChildren();
            $this->log('Looping through '.count($children).' children...');

            if ($children) {
                foreach ($children as $node) {
                    $this->resourceManager->delete($node);
                }
            }
        }

        $tabs = $this->om->getRepository(HomeTab::class)->findBy(['workspace' => $workspace]);
        $crud = $this->container->get('claroline.api.crud');

        foreach ($tabs as $tab) {
            $crud->delete($tab);
        }

        $this->dispatcher->dispatch(
            'claroline_workspaces_delete',
            'GenericData',
            [[$workspace]]
        );
        $this->om->remove($workspace);
        $this->om->endFlushSuite();
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
     * @param int            $orderedToolType
     *
     * @return bool[]
     */
    public function getAccesses(
        TokenInterface $token,
        array $workspaces,
        $toolName = null,
        $action = 'open',
        $orderedToolType = 0
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
            $em = $this->container->get('doctrine.orm.entity_manager');
            $openWsIds = $em->getRepository('ClarolineCoreBundle:Workspace\Workspace')
                ->findOpenWorkspaceIds(
                    $userRoleNames,
                    $workspacesWithoutManagerRole,
                    $toolName,
                    $action,
                    $orderedToolType
                );

            foreach ($openWsIds as $idRow) {
                $accesses[$idRow['id']] = true;
            }
        }

        //remove accesses if workspace is personal and right was not given

        foreach ($workspaces as $workspace) {
            if ($workspace->isPersonal() && $toolName) {
                $pwc = $this->container->get('claroline.manager.tool_manager')
                    ->getPersonalWorkspaceToolConfigs();
                $canOpen = false;

                foreach ($pwc as $conf) {
                    if (!$toolName) {
                        $toolName = 'home';
                    }

                    if ($conf->getTool()->getName() === $toolName &&
                        in_array($conf->getRole()->getName(), $workspace->getCreator()->getRoles()) &&
                        ($conf->getMask() & 1)) {
                        $canOpen = true;
                    }
                }

                if (!$canOpen) {
                    $accesses[$workspace->getId()] = false;
                }
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

    /**
     * @param int $workspaceId
     *
     * @return Workspace
     */
    public function getWorkspaceById($workspaceId)
    {
        return $this->workspaceRepo->find($workspaceId);
    }

    /**
     * @param string $code
     *
     * @return Workspace
     */
    public function getOneByCode($code)
    {
        return $this->workspaceRepo->findOneBy([
            'code' => $code,
        ]);
    }

    public function addUserQueue(Workspace $workspace, User $user)
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

    public function isUserInValidationQueue(Workspace $workspace, User $user)
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
        $this->dispatcher->dispatch(
            'claroline_workspace_register_user',
            'WorkspaceAddUser',
            [$role, $user]
        );

        if ($user->getUuid() === $this->container->get('security.token_storage')->getToken()->getUser()->getUuid()) {
            $token = new UsernamePasswordToken($user, null, 'main', $user->getRoles());
            $this->container->get('security.token_storage')->setToken($token);
        }

        return $user;
    }

    /**
     * Count the number of resources in a workspace.
     *
     * @param Workspace $workspace
     *
     * @return int
     */
    public function countResources(Workspace $workspace)
    {
        //@todo count directory from dql
        $root = $this->resourceManager->getWorkspaceRoot($workspace);
        if (!$root) {
            return 0;
        }
        $descendants = $this->resourceManager->getDescendants($root);

        return count($descendants);
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

    /**
     * @param Workspace $workspace
     *
     * @return Tool
     */
    public function getFirstOpenableTool(Workspace $workspace)
    {
        $token = $this->container->get('security.token_storage')->getToken();
        $roles = $this->container->get('claroline.security.utilities')->getRoles($token);

        /** @var Tool[] $orderedTools */
        $orderedTools = $this->container->get('claroline.manager.tool_manager')->getDisplayedByRolesAndWorkspace($roles, $workspace);
        //loop through the tools till we can open one !
        $authorization = $this->container->get('security.authorization_checker');

        foreach ($orderedTools as $tool) {
            if ($authorization->isGranted($tool->getName(), $workspace)) {
                return $tool;
            }
        }
    }

    public function getWorkspaceOptions(Workspace $workspace)
    {
        $workspaceOptions = $this->workspaceOptionsRepo->findOneByWorkspace($workspace);

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
        $rm = $this->container->get('claroline.manager.resource_manager');

        if (!$rm->getLogger()) {
            $rm->setLogger($logger);
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
            if ($workspace->getCreator() === $token->getUser()) {
                return true;
            }

            //if we're amongst the administrators of the organizations
            $adminOrganizations = $token->getUser()->getAdministratedOrganizations();
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
        if ($managerRole) {
            foreach ($token->getRoles() as $role) {
                if ($managerRole->getName() === $role->getRole() || 'ROLE_ADMIN' === $role->getRole()) {
                    return true;
                }
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
            if ('ROLE_USURPATE_WORKSPACE_ROLE' === $role->getRole() || $role instanceof SwitchUserRole) {
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

    //used for cli copy debug tool
    public function copyFromCode(Workspace $workspace, $code)
    {
        if ($this->logger) {
            $this->container->get('claroline.api.serializer')->setLogger($this->logger);
        }

        $newWorkspace = new Workspace();
        $newWorkspace->setCode($code);
        $newWorkspace->setName($code);
        $this->copy($workspace, $newWorkspace);
        //override code & name

        return $newWorkspace;
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

        $options = [Options::LIGHT_COPY, Options::REFRESH_UUID];
        // gets entity from raw data.

        /** @var Workspace $workspace */
        $workspaceCopy = $transferManager->deserialize($data, $newWorkspace, $options, $fileBag);

        $workspaceCopy->setModel($model);

        //set the manager
        $managerRole = $this->roleManager->getManagerRole($workspaceCopy);

        if ($managerRole && $workspaceCopy->getCreator()) {
            $user = $workspaceCopy->getCreator();
            $user->addRole($managerRole);
            $this->om->persist($user);
        }

        $root = $this->resourceManager->getWorkspaceRoot($workspaceCopy);

        if ($root) {
            $this->resourceManager->createRights($root);
        }

        // Copy workspace shortcuts
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

        return $workspaceCopy;
    }

    /**
     * @param Workspace $workspace
     *
     * @return array
     */
    public function getArrayRolesByWorkspace(Workspace $workspace)
    {
        $workspaceRoles = [];
        $uow = $this->om->getUnitOfWork();
        $wRoles = $this->roleManager->getRolesByWorkspace($workspace);
        $scheduledForInsert = $uow->getScheduledEntityInsertions();

        foreach ($scheduledForInsert as $entity) {
            if ('Claroline\CoreBundle\Entity\Role' === get_class($entity)) {
                if ($entity->getWorkspace()) {
                    if ($entity->getWorkspace()->getGuid() === $workspace->getGuid()) {
                        $wRoles[] = $entity;
                    }
                }
            }
        }
        //now we build the array
        foreach ($wRoles as $wRole) {
            $workspaceRoles[$wRole->getTranslationKey()] = $wRole;
        }

        return $workspaceRoles;
    }

    public function archive(Workspace $workspace, array $options = [])
    {
        //rename with [archive] and ids
        $workspace->setName('[archive]'.$workspace->getName());
        $workspace->setCode('[archive]'.$workspace->getCode().uniqid());
        $workspace->setArchived(true);

        $this->om->persist($workspace);
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

    public function getRecentWorkspaceForUser(User $user, array $roles)
    {
        $wsLogs = $this->getLatestWorkspacesByUser($user, $roles);
        $workspaces = [];

        if (!empty($wsLogs)) {
            foreach ($wsLogs as $wsLog) {
                $workspaces[] = $wsLog['workspace'];
            }
        }

        return $workspaces;
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

    public function setWorkspacesFlag()
    {
        /** @var Workspace[] $workspaces */
        $workspaces = $this->container->get('claroline.api.finder')->fetch(Workspace::class, [
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
}
