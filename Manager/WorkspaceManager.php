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

use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Entity\Workspace\WorkspaceFavourite;
use Claroline\CoreBundle\Entity\Workspace\WorkspaceOptions;
use Claroline\CoreBundle\Entity\Workspace\WorkspaceRegistrationQueue;
use Claroline\CoreBundle\Entity\Model\WorkspaceModel;
use Claroline\CoreBundle\Event\StrictDispatcher;
use Claroline\CoreBundle\Library\Security\Utilities;
use Claroline\CoreBundle\Library\Utilities\ClaroUtilities;
use Claroline\CoreBundle\Library\Workspace\Configuration;
use Claroline\CoreBundle\Manager\HomeTabManager;
use Claroline\CoreBundle\Manager\MaskManager;
use Claroline\CoreBundle\Manager\ResourceManager;
use Claroline\CoreBundle\Manager\RoleManager;
use Claroline\CoreBundle\Pager\PagerFactory;
use Claroline\CoreBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Repository\OrderedToolRepository;
use Claroline\CoreBundle\Repository\ResourceNodeRepository;
use Claroline\CoreBundle\Repository\ResourceRightsRepository;
use Claroline\CoreBundle\Repository\ResourceTypeRepository;
use Claroline\CoreBundle\Repository\RoleRepository;
use Claroline\CoreBundle\Repository\WorkspaceRepository;
use Claroline\CoreBundle\Repository\UserRepository;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Filesystem\Filesystem;
use Claroline\BundleRecorder\Log\LoggableTrait;
use Psr\Log\LoggerInterface;

/**
 * @DI\Service("claroline.manager.workspace_manager")
 */
class WorkspaceManager
{
    use LoggableTrait;

    const MAX_WORKSPACE_BATCH_SIZE = 10;

    /** @var HomeTabManager */
    private $homeTabManager;
    /** @var MaskManager */
    private $maskManager;
    /** @var OrderedToolRepository */
    private $orderedToolRepo;
    /** @var RoleManager */
    private $roleManager;
    /** @var ResourceManager */
    private $resourceManager;
    /** @var ResourceNodeRepository */
    private $resourceRepo;
    /** @var ResourceRightsRepository */
    private $resourceRightsRepo;
    /** @var ResourceTypeRepository */
    private $resourceTypeRepo;
    /** @var RoleRepository */
    private $roleRepo;
    /** @var UserRepository */
    private $userRepo;
    /** @var WorkspaceRepository */
    private $workspaceRepo;
    /** @var WorkspaceOptionsRepository */
    private $workspaceOptionsRepo;
    /** @var StrictDispatcher */
    private $dispatcher;
    /** @var ObjectManager */
    private $om;
    /** @var ClaroUtilities */
    private $ut;
    private $sut;
    /** @var string */
    private $templateDir;
    /** @var PagerFactory */
    private $pagerFactory;
    private $workspaceFavouriteRepo;
    private $container;

    /**
     * Constructor.
     *
     * @DI\InjectParams({
     *     "homeTabManager"        = @DI\Inject("claroline.manager.home_tab_manager"),
     *     "roleManager"           = @DI\Inject("claroline.manager.role_manager"),
     *     "maskManager"           = @DI\Inject("claroline.manager.mask_manager"),
     *     "resourceManager"       = @DI\Inject("claroline.manager.resource_manager"),
     *     "dispatcher"            = @DI\Inject("claroline.event.event_dispatcher"),
     *     "om"                    = @DI\Inject("claroline.persistence.object_manager"),
     *     "ut"                    = @DI\Inject("claroline.utilities.misc"),
     *     "sut"                   = @DI\Inject("claroline.security.utilities"),
     *     "templateDir"           = @DI\Inject("%claroline.param.templates_directory%"),
     *     "pagerFactory"          = @DI\Inject("claroline.pager.pager_factory"),
     *     "container"             = @DI\Inject("service_container")
     * })
     */
    public function __construct(
        HomeTabManager $homeTabManager,
        RoleManager $roleManager,
        MaskManager $maskManager,
        ResourceManager $resourceManager,
        StrictDispatcher $dispatcher,
        ObjectManager $om,
        ClaroUtilities $ut,
        Utilities $sut,
        $templateDir,
        PagerFactory $pagerFactory,
        ContainerInterface $container
    )
    {
        $this->homeTabManager = $homeTabManager;
        $this->maskManager = $maskManager;
        $this->roleManager = $roleManager;
        $this->resourceManager = $resourceManager;
        $this->ut = $ut;
        $this->sut = $sut;
        $this->om = $om;
        $this->dispatcher = $dispatcher;
        $this->templateDir = $templateDir;
        $this->resourceTypeRepo = $om->getRepository('ClarolineCoreBundle:Resource\ResourceType');
        $this->roleRepo = $om->getRepository('ClarolineCoreBundle:Role');
        $this->orderedToolRepo = $om->getRepository('ClarolineCoreBundle:Tool\OrderedTool');
        $this->resourceRepo = $om->getRepository('ClarolineCoreBundle:Resource\ResourceNode');
        $this->resourceRightsRepo = $om->getRepository('ClarolineCoreBundle:Resource\ResourceRights');
        $this->roleRepo = $om->getRepository('ClarolineCoreBundle:Role');
        $this->userRepo = $om->getRepository('ClarolineCoreBundle:User');
        $this->workspaceRepo = $om->getRepository('ClarolineCoreBundle:Workspace\Workspace');
        $this->workspaceOptionsRepo = $om->getRepository('ClarolineCoreBundle:Workspace\WorkspaceOptions');
        $this->workspaceFavouriteRepo = $om->getRepository('ClarolineCoreBundle:Workspace\WorkspaceFavourite');
        $this->pagerFactory = $pagerFactory;
        $this->container = $container;
    }

    /**
     * Rename a workspace.
     *
     * @param \Claroline\CoreBundle\Entity\Workspace\Workspace $workspace
     * @param string                                                   $name
     */
    public function rename(Workspace $workspace, $name)
    {
        $workspace->setName($name);
        $root = $this->resourceManager->getWorkspaceRoot($workspace);
        $root->setName($name);
        $this->om->persist($workspace);
        $this->om->persist($root);
        $this->om->flush();
    }

    /**
     * Creates a workspace.
     *
     * @param \Claroline\CoreBundle\Library\Workspace\Configuration $configuration
     * @param \Claroline\CoreBundle\Entity\User                     $manager
     * @param bool                                                  $createUsers
     * @param bool                                                  $importUsers
     *
     * @return \Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace
     */
    public function create(Configuration $configuration, User $manager)
    {
        $transfertManager = $this->container->get('claroline.manager.transfert_manager');
        if ($this->logger) $transfertManager->setLogger($this->logger);
        $workspace = $transfertManager->createWorkspace($configuration, $manager);

        return $workspace;
    }

    /**
     * @param \Claroline\CoreBundle\Entity\Workspace\Workspace $workspace
     */
    public function createWorkspace(Workspace $workspace)
    {
        $ch = $this->container->get('claroline.config.platform_config_handler');
        $workspace->setMaxUploadResources($ch->getParameter('max_upload_resources'));
        $workspace->setMaxStorageSize($ch->getParameter('max_storage_size'));
        $workspace->setMaxUsers($ch->getParameter('max_workspace_users'));
        $this->editWorkspace($workspace);
    }

    public function editWorkspace(Workspace $workspace)
    {
        $this->om->persist($workspace);
        $this->om->flush();
    }

    public function createWorkspaceFromModel(
        WorkspaceModel $model,
        User $user,
        $name,
        $code,
        $description = null,
        $displayable = false,
        $selfRegistration = false,
        $selfUnregistration = false,
        &$errors = array()
    )
    {
        $this->om->startFlushSuite();
        $this->log('Workspace from model beginning.');
        $workspaceModelManager = $this->container->get('claroline.manager.workspace_model_manager');

        $workspace = new Workspace();
        $workspace->setName($name);
        $workspace->setCode($code);
        $workspace->setDescription($description);
        $workspace->setDisplayable($displayable);
        $workspace->setSelfRegistration($selfRegistration);
        $workspace->setSelfUnregistration($selfUnregistration);
        $guid = $this->ut->generateGuid();
        $workspace->setGuid($guid);
        $date = new \Datetime(date('d-m-Y H:i'));
        $workspace->setCreationDate($date->getTimestamp());
        $workspace->setCreator($user);

        $errors = [];

        $this->createWorkspace($workspace);
        $workspaceModelManager->addDataFromModel($model, $workspace, $user, $errors);
        $this->log('Workspace from model end.');
        $this->om->endFlushSuite();

        return $workspace;
    }

    /**
     * Delete a workspace.
     *
     * @param \Claroline\CoreBundle\Entity\Workspace\Workspace $workspace
     */
    public function deleteWorkspace(Workspace $workspace)
    {
        $root = $this->resourceManager->getWorkspaceRoot($workspace);

        if ($root) {
            $children = $root->getChildren();

            if ($children) {
                foreach ($children as $node) {
                    $this->resourceManager->delete($node);
                }
            }
        }
        $this->dispatcher->dispatch(
            'claroline_workspaces_delete',
            'GenericDatas',
            array(array($workspace))
        );
        $this->om->remove($workspace);
        $this->om->flush();
    }

    /**
     * Appends a role list to a right array.
     *
     * @param array $rights
     * @param array $roles
     *
     * @return array
     */
    public function prepareRightsArray(array $rights, array $roles)
    {
        $preparedRightsArray = array();

        foreach ($rights as $key => $right) {
            $preparedRights = $right;
            $preparedRights['role'] = $roles[$key];
            $preparedRightsArray[] = $preparedRights;
        }

        return $preparedRightsArray;
    }

    /**
     * Adds a favourite workspace.
     *
     * @param \Claroline\CoreBundle\Entity\Workspace\Workspace $workspace
     * @param \Claroline\CoreBundle\Entity\User                        $user
     */
    public function addFavourite(Workspace $workspace, User $user)
    {
        $favourite = new WorkspaceFavourite();
        $favourite->setWorkspace($workspace);
        $favourite->setUser($user);

        $this->om->persist($favourite);
        $this->om->flush();
    }

    /**
     * Removes a favourite workspace.
     *
     * @param \Claroline\CoreBundle\Entity\Workspace\WorkspaceFavourite $favourite
     */
    public function removeFavourite(WorkspaceFavourite $favourite)
    {
        $this->om->remove($favourite);
        $this->om->flush();
    }

    /**
     * @param \Claroline\CoreBundle\Entity\User $user
     *
     * @return \Claroline\CoreBundle\Entity\Workspace\Workspace[]
     */
    public function getWorkspacesByUser(User $user)
    {
        return $this->workspaceRepo->findByUser($user);
    }

    /**
     * @return \Claroline\CoreBundle\Entity\Workspace\Workspace
     */
    public function getNonPersonalWorkspaces()
    {
        return $this->workspaceRepo->findNonPersonal();
    }

    /**
     * @return \Claroline\CoreBundle\Entity\Workspace\Workspace[]
     */
    public function getWorkspacesByAnonymous($orderedToolType = 0)
    {
        return $this->workspaceRepo->findByAnonymous($orderedToolType);
    }

    public function getWorkspacesByManager(User $user)
    {
        return $this->workspaceRepo->findWorkspacesByManager($user);
    }

    /**
     * @return integer
     */
    public function getNbWorkspaces()
    {
        return $this->workspaceRepo->count();
    }

    /**
     * @return integer
     */
    public function getNbPersonalWorkspaces()
    {
        return $this->workspaceRepo->countPersonalWorkspaces();
    }

    /**
     * @return integer
     */
    public function getNbNonPersonalWorkspaces()
    {
        return $this->workspaceRepo->countNonPersonalWorkspaces();
    }

    /**
     * @param string[] $roles
     *
     * @return \Claroline\CoreBundle\Entity\Workspace\Workspace[]
     */
    public function getOpenableWorkspacesByRoles(array $roles)
    {

        return $this->workspaceRepo->findByRoles($roles);
    }

    /**
     * @param string $search
     * @param string[] $roles
     *
     * @return \Claroline\CoreBundle\Entity\Workspace\Workspace[]
     */
    public function getOpenableWorkspacesByRolesAndSearch($search, array $roles)
    {

        return $this->workspaceRepo->findBySearchAndRoles($search, $roles);
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
     * @param TokenInterface    $token
     * @param array[Workspace]  $workspaces
     * @param string|null       $toolName
     * @return array[boolean]
     */
    public function getAccesses(
        TokenInterface $token,
        array $workspaces,
        $toolName = null,
        $action = 'open',
        $orderedToolType = 0
    )
    {
        $userRoleNames = $this->sut->getRoles($token);
        $accesses = array();

        if (in_array('ROLE_ADMIN', $userRoleNames)) {
            foreach ($workspaces as $workspace) {
                $accesses[$workspace->getId()] = true;
            }

            return $accesses;
        }

        $hasAllAccesses = true;
        $workspacesWithoutManagerRole = array();

        foreach ($workspaces as $workspace) {
            if (in_array('ROLE_WS_MANAGER_' . $workspace->getGuid(), $userRoleNames)) {
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

        //remove accessess if workspace is personal and right was not given

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
                        in_array($conf->getRole()->getName(), $userRoleNames) &&
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
     * @param string[] $roles
     * @param integer $page
     * @param integer $max
     *
     * @return \PagerFanta\PagerFanta
     */
    public function getOpenableWorkspacesByRolesPager(array $roles, $page, $max)
    {
        $workspaces = $this->getOpenableWorkspacesByRoles($roles);

        return $this->pagerFactory->createPagerFromArray($workspaces, $page, $max);
    }

    /**
     * @param string $search
     * @param string[] $roles
     * @param integer $page
     * @param integer $max
     *
     * @return \PagerFanta\PagerFanta
     */
    public function getOpenableWorkspacesBySearchAndRolesPager($search, array $roles, $page, $max)
    {
        $workspaces = $this->getOpenableWorkspacesByRolesAndSearch($search, $roles);

        return $this->pagerFactory->createPagerFromArray($workspaces, $page, $max);
    }

    /**
     * @param string[] $roleNames
     * @param integer  $page
     *
     * @return \PagerFanta\PagerFanta
     */
    public function getWorkspacesByRoleNamesPager(array $roleNames, $page)
    {
        if (count($roleNames) > 0) {
            $workspaces = $this->workspaceRepo->findMyWorkspacesByRoleNames($roleNames);
        } else {
            $workspaces = array();
        }

        return $this->pagerFactory->createPagerFromArray($workspaces, $page);
    }

    /**
     * @param string[] $roleNames
     * @param string   $search
     * @param integer  $page
     *
     * @return \PagerFanta\PagerFanta
     */
    public function getWorkspacesByRoleNamesBySearchPager(
        array $roleNames,
        $search,
        $page,
        $orderedToolType = 0
    )
    {
        if (count($roleNames) > 0) {
            $workspaces = $this->workspaceRepo
                ->findByRoleNamesBySearch($roleNames, $search, $orderedToolType);
        } else {
            $workspaces = array();
        }

        return $this->pagerFactory->createPagerFromArray($workspaces, $page);
    }

    /**
     * @param \Claroline\CoreBundle\Entity\User $user
     * @param string[]                          $roleNames
     *
     * @return integer[]
     */
    public function getWorkspaceIdsByUserAndRoleNames(User $user, array $roleNames)
    {
        return $this->workspaceRepo->findIdsByUserAndRoleNames($user, $roleNames);
    }

    /**
     * @param \Claroline\CoreBundle\Entity\User $user
     * @param string[]                          $roleNames
     *
     * @return \Claroline\CoreBundle\Entity\Workspace\Workspace[]
     */
    public function getWorkspacesByUserAndRoleNames(User $user, array $roleNames)
    {
        return $this->workspaceRepo->findByUserAndRoleNames($user, $roleNames);
    }

    /**
     * @param \Claroline\CoreBundle\Entity\User $user
     * @param string[]                          $roleNames
     * @param integer[]                         $restrictionIds
     *
     * @return \Claroline\CoreBundle\Entity\Workspace\Workspace[]
     */
    public function getWorkspacesByUserAndRoleNamesNotIn(
        User $user,
        array $roleNames,
        array $restrictionIds = null
    )
    {
        return $this->workspaceRepo->findByUserAndRoleNamesNotIn($user, $roleNames, $restrictionIds);
    }

    /**
     * Returns an array containing
     *
     * @param \Claroline\CoreBundle\Entity\User $user
     * @param array                             $roles
     * @param integer                           $max
     *
     * @return array
     */
    public function getLatestWorkspacesByUser(User $user, array $roles, $max = 5)
    {
        return count($roles) > 0 ?
            $this->workspaceRepo->findLatestWorkspacesByUser($user, $roles, $max) :
            array();
    }

    /**
     * @param integer $max
     *
     * @return \Claroline\CoreBundle\Entity\Workspace\Workspace[]
     */
    public function getWorkspacesWithMostResources($max)
    {
        return $this->workspaceRepo->findWorkspacesWithMostResources($max);
    }

    /**
     * @param integer $workspaceId
     *
     * @return \Claroline\CoreBundle\Entity\Workspace\Workspace
     */
    public function getWorkspaceById($workspaceId)
    {
        return $this->workspaceRepo->find($workspaceId);
    }

    /**
     * @param string $guid
     *
     * @return \Claroline\CoreBundle\Entity\Workspace\Workspace
     */
    public function getOneByGuid($guid)
    {
        return $this->workspaceRepo->findOneByGuid($guid);
    }

    /**
     * @return \Claroline\CoreBundle\Entity\Workspace\Workspace[]
     */
    public function getDisplayableWorkspaces()
    {
        return $this->workspaceRepo->findDisplayableWorkspaces();
    }

    /**
     * @param integer $page
     *
     * @return \PagerFanta\PagerFanta
     */
    public function getDisplayableWorkspacesPager($page, $max = 20)
    {
        $workspaces = $this->workspaceRepo->findDisplayableWorkspaces();

        return $this->pagerFactory->createPagerFromArray($workspaces, $page, $max);
    }

    /**
     * @param string $search
     *
     * @return \Claroline\CoreBundle\Entity\Workspace\Workspace[]
     */
    public function getDisplayableWorkspacesBySearch($search)
    {
        return $this->workspaceRepo->findDisplayableWorkspacesBySearch($search);
    }

    /**
     * @param string  $search
     * @param integer $page
     * @param User $user
     *
     * @return \PagerFanta\PagerFanta
     */
    public function getDisplayableWorkspacesBySearchPager($search, $page, $max = 20)
    {
        $workspaces = $this->workspaceRepo->findDisplayableWorkspacesBySearch($search);

        return $this->pagerFactory->createPagerFromArray($workspaces, $page, $max);
    }

    /**
     * @param \Claroline\CoreBundle\Entity\Role[] $roles
     * @param integer                             $page
     *
     * @return \PagerFanta\PagerFanta
     */
    public function getWorkspacesWithSelfUnregistrationByRoles($roles, $page)
    {
        $workspaces = $this->workspaceRepo
            ->findWorkspacesWithSelfUnregistrationByRoles($roles);

        return $this->pagerFactory->createPagerFromArray($workspaces, $page);
    }

    /**
     * @param \Claroline\CoreBundle\Entity\Workspace\Workspace $workspace
     * @param \Claroline\CoreBundle\Entity\Role[]                      $roles
     *
     * @return \Claroline\CoreBundle\Entity\Workspace\Workspace[]
     */
    public function getWorkspaceByWorkspaceAndRoles(
        Workspace $workspace,
        array $roles,
        $orderedToolType = 0
    )
    {
        return $this->workspaceRepo->findWorkspaceByWorkspaceAndRoles(
            $workspace,
            $roles,
            $orderedToolType
        );
    }

    /**
     * @param \Claroline\CoreBundle\Entity\User $user
     *
     * @return \Claroline\CoreBundle\Entity\Workspace\Workspace[]
     */
    public function getFavouriteWorkspacesByUser(User $user)
    {
        $workspaces = array();
        $favourites = $this->workspaceFavouriteRepo
            ->findFavouriteWorkspacesByUser($user);

        foreach ($favourites as $favourite) {
            $workspace = $favourite->getWorkspace();
            $workspaces[$workspace->getId()] = $workspace;
        }

        return $workspaces;
    }

    /**
     * @param \Claroline\CoreBundle\Entity\Workspace\Workspace $workspace
     * @param \Claroline\CoreBundle\Entity\User                        $user
     *
     * @return \Claroline\CoreBundle\Entity\Workspace\WorkspaceFavourite
     */
    public function getFavouriteByWorkspaceAndUser(
        Workspace $workspace,
        User $user
    )
    {
        return $this->workspaceFavouriteRepo
            ->findOneBy(array('workspace' => $workspace, 'user' => $user));
    }

    /**
     * @param \Claroline\CoreBundle\Entity\Workspace\Workspace $workspace
     *
     * @return \Claroline\CoreBundle\Entity\User|null
     */
    public function findPersonalUser(Workspace $workspace)
    {
        $user = $this->userRepo->findBy(array('personalWorkspace' => $workspace));

        return (count($user) === 1) ? $user[0]: null;
    }

    public function addUserQueue(Workspace $workspace, User $user)
    {
        $wksrq = new WorkspaceRegistrationQueue();
        $wksrq->setUser($user);
        $role = $this->roleManager->getCollaboratorRole($workspace);
        $wksrq->setRole($role);
        $wksrq->setWorkspace($workspace);
        $this->om->persist($wksrq);
        $this->om->flush();
    }

    /**
     * @param \Claroline\CoreBundle\Entity\Workspace\Workspace $workspace
     * @param \Claroline\CoreBundle\Entity\User $user
     *
     * @return \Claroline\CoreBundle\Entity\User
     */
    public function addUserAction(Workspace $workspace, User $user)
    {
        $role = $this->roleManager->getCollaboratorRole($workspace);
        $userRoles = $this->roleManager->getWorkspaceRolesForUser($user, $workspace);

        if (count($userRoles) === 0) {
            $this->roleManager->associateRole($user, $role);
            $this->dispatcher->dispatch(
                'log',
                'Log\LogRoleSubscribe',
                array($role, $user)
            );
            $this->dispatcher->dispatch(
                'claroline_workspace_register_user',
                'WorkspaceAddUser',
                array($role, $user)
            );
        }

        $token = new UsernamePasswordToken($user, null, 'main', $user->getRoles());
        $this->container->get('security.context')->setToken($token);

        return $user;
    }

    /**
     * @param integer $page
     * @param integer $max
     * @param string  $orderedBy
     *
     * @return \Pagerfanta\Pagerfanta;
     */
    public function findAllWorkspaces($page, $max = 20, $orderedBy = 'id', $order = null)
    {
        $result = $this->workspaceRepo->findBy(array(), array($orderedBy => $order));

        return $this->pagerFactory->createPagerFromArray($result, $page, $max);
    }

    /**
     * @param string  $search
     * @param integer $page
     * @param integer $max
     * @param string  $orderedBy
     *
     * @return \Pagerfanta\Pagerfanta;
     */
    public function getWorkspaceByName(
        $search,
        $page,
        $max = 20,
        $orderedBy = 'id',
        $order = 'ASC'
    )
    {
        $query = $this->workspaceRepo->findByName($search, false, $orderedBy, $order);

        return $this->pagerFactory->createPager($query, $page, $max);
    }

    public function countUsers(Workspace $workspace, $includeGrps = false)
    {
        if ($includeGrps) {
            $wsRoles = $this->roleManager->getRolesByWorkspace($workspace);

            return $this->container->get('claroline.manager.user_manager')->countByRoles($wsRoles, true);
        }

        return $this->workspaceRepo->countUsers($workspace->getId());
    }

    /**
     * Import the content of an archive in a workspace.
     *
     * @param Configuration $configuration
     * @param Workspace $workspace
     * @return Workspace
     */
    public function importInExistingWorkspace(Configuration $configuration, Workspace $workspace)
    {
        $root = $this->resourceManager->getResourceFromNode($this->resourceManager->getWorkspaceRoot($workspace));
        $wsRoles = $this->roleManager->getRolesByWorkspace($workspace);
        $entityRoles = [];

        foreach ($wsRoles as $wsRole) {
            $entityRoles[$this->roleManager->getWorkspaceRoleBaseName($wsRole)] = $wsRole;
        }

        $workspace = $this->container->get('claroline.manager.transfert_manager')->populateWorkspace(
            $workspace,
            $configuration,
            $root,
            $entityRoles
        );

        $this->importRichText();

        return $workspace;
    }

    /**
     * This function must be fired right after a workspace is "populated".
     * Don't use it otherwise !!!!
     */
    public function importRichText()
    {
        $this->container->get('claroline.manager.transfert_manager')->importRichText();
    }

    /**
     * Import a workspace list from a csv data.
     *
     * @param array $workspaces
     */
    public function importWorkspaces(array $workspaces, $logger = null)
    {
        //$this->om->clear();
        $ds = DIRECTORY_SEPARATOR;
        $i = 0;
        $j = 0;
        $workspaceModelManager = $this->container->get('claroline.manager.workspace_model_manager');

        foreach ($workspaces as $workspace) {
            $this->om->startFlushSuite();
            $model = null;
            $name = $workspace[0];
            $code = $workspace[1];
            $isVisible = $workspace[2];
            $selfRegistration = $workspace[3];
            $registrationValidation = $workspace[4];
            $selfUnregistration = $workspace[5];
            $errors = array();

            if (isset($workspace[6]) && trim($workspace[6]) !== '') {
                $user = $this->om->getRepository('ClarolineCoreBundle:User')
                    ->findOneByUsername($workspace[6]);
            } else {
                $user = $this->container->get('security.context')->getToken()->getUser();
            }

            if (isset($workspace[7])) $model = $this->om->getRepository('ClarolineCoreBundle:Model\WorkspaceModel')
                ->findOneByName($workspace[7]);

            if ($model) {
                $guid = $this->ut->generateGuid();
                $workspace = new Workspace();
                $this->createWorkspace($workspace);
                $workspace->setName($name);
                $workspace->setCode($code);
                $workspace->setDisplayable($isVisible);
                $workspace->setSelfRegistration($selfRegistration);
                $workspace->setSelfUnregistration($selfUnregistration);
                $workspace->setRegistrationValidation($registrationValidation);
                $workspace->setGuid($guid);
                $date = new \Datetime(date('d-m-Y H:i'));
                $workspace->setCreationDate($date->getTimestamp());
                $workspace->setCreator($user);
                $workspaceModelManager->addDataFromModel($model, $workspace, $user, $errors);
            } else {
                //this should be changed later
                $configuration = new Configuration($this->templateDir . $ds . 'default.zip');
                $configuration->setWorkspaceName($name);
                $configuration->setWorkspaceCode($code);
                $configuration->setDisplayable($isVisible);
                $configuration->setSelfRegistration($selfRegistration);
                $configuration->setSelfUnregistration($registrationValidation);
                $this->container->get('claroline.manager.transfert_manager')->createWorkspace($configuration, $user);
            }

            $i++;
            $j++;

            if ($i % self::MAX_WORKSPACE_BATCH_SIZE === 0) {
                $this->om->forceFlush();
                $this->om->clear();
            }

            $this->om->endFlushSuite();
        }
    }

    public function getDisplayableNonPersonalWorkspaces(
        $page = 1,
        $max = 50,
        $search = ''
    )
    {
        $workspaces = $search === '' ?
            $this->workspaceRepo->findDisplayableNonPersonalWorkspaces() :
            $this->workspaceRepo->findDisplayableNonPersonalWorkspacesBySearch($search);

        return $this->pagerFactory->createPagerFromArray($workspaces, $page, $max);
    }

    public function getDisplayablePersonalWorkspaces(
        $page = 1,
        $max = 50,
        $search = ''
    )
    {
        $workspaces = $search === '' ?
            $this->workspaceRepo->findDisplayablePersonalWorkspaces() :
            $this->workspaceRepo->findDisplayablePersonalWorkspacesBySearch($search);

        return $this->pagerFactory->createPagerFromArray($workspaces, $page, $max);
    }

    public function getAllPersonalWorkspaces(
        $page = 1,
        $max = 50,
        $search = '',
        $orderedBy = 'name',
        $order = 'ASC'
    )
    {
        $workspaces = $search === '' ?
            $this->workspaceRepo
                ->findAllPersonalWorkspaces($orderedBy, $order) :
            $this->workspaceRepo
                ->findAllPersonalWorkspacesBySearch($search, $orderedBy, $order);

        return $this->pagerFactory->createPagerFromArray($workspaces, $page, $max);
    }

    public function getAllNonPersonalWorkspaces(
        $page = 1,
        $max = 50,
        $search = '',
        $orderedBy = 'name',
        $order = 'ASC'
    )
    {
        $workspaces = $search === '' ?
            $this->workspaceRepo
                ->findAllNonPersonalWorkspaces($orderedBy, $order) :
            $this->workspaceRepo
                ->findAllNonPersonalWorkspacesBySearch($search, $orderedBy, $order);

        return $this->pagerFactory->createPagerFromArray($workspaces, $page, $max);
    }

    public function getWorkspaceByCode($workspaceCode, $executeQuery = true)
    {
        return $this->workspaceRepo
            ->findWorkspaceByCode($workspaceCode, $executeQuery);
    }

    /**
     * Count the number of resources in a workspace
     *
     * @param Workspace $workspace
     *
     * @return integer
     */
    public function countResources(Workspace $workspace)
    {
        //@todo count directory from dql
        $root = $this->resourceManager->getWorkspaceRoot($workspace);
        if (!$root) return 0;
        $descendants = $this->resourceManager->getDescendants($root);

        return count($descendants);
    }

    /**
     * Get the workspace storage directory
     *
     * @param Workspace $workspace
     *
     * @return string
     */

    public function getStorageDirectory(Workspace $workspace)
    {
        $ds = DIRECTORY_SEPARATOR;

        return $this->container->getParameter('claroline.param.files_directory') . $ds . 'WORKSPACE_' . $workspace->getId();
    }

    /**
     * Get the current used storage in a workspace.
     *
     * @param Workspace $workspace
     *
     * @return integer
     */
    public function getUsedStorage(Workspace $workspace)
    {
        $dir = $this->getStorageDirectory($workspace);
        $size = 0;

        if (!is_dir($dir)) return $size;

        foreach(new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dir)) as $file){
            $size += $file->getSize();
        }

        return $size;
    }

    public function getWorkspaceCodesWithPrefix($prefix, $executeQuery = true)
    {
        return $this->workspaceRepo->findWorkspaceCodesWithPrefix(
            $prefix,
            $executeQuery
        );
    }

    public function toArray(Workspace $workspace)
    {
        $data = array();
        $data['id'] = $workspace->getId();
        $data['name'] = $workspace->getName();
        $data['code'] = $workspace->getCode();
        $data['expiration_date'] = $workspace->getEndDate()->getTimeStamp();

        return $data;
    }

    public function getFirstOpenableTool(Workspace $workspace)
    {
        $token = $this->container->get('security.token_storage')->getToken();
        $roles = $this->container->get('claroline.security.utilities')->getRoles($token);
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

        if (is_null($workspaceOptions)) {
            $workspaceOptions = new WorkspaceOptions();
            $workspaceOptions->setWorkspace($workspace);
            $details = array(
                'hide_tools_menu' => false,
                'background_color' => null
            );
            $workspaceOptions->setDetails($details);
            $workspace->setOptions($workspaceOptions);
            $this->om->persist($workspaceOptions);
            $this->om->persist($workspace);
            $this->om->flush();
        }

        return $workspaceOptions;
    }

    public function persistworkspaceOptions(WorkspaceOptions $workspaceOptions)
    {
        $this->om->persist($workspaceOptions);
        $this->om->flush();
    }

    public function isToolsMenuHidden(Workspace $workspace)
    {
        $workspaceOptions = $this->getWorkspaceOptions($workspace);
        $details = $workspaceOptions->getDetails();

        return isset($details['hide_tools_menu']) && $details['hide_tools_menu'];
    }

    public function setLogger(LoggerInterface $logger)
    {
        $this->container->get('claroline.manager.workspace_model_manager')->setLogger($logger);
        $this->logger = $logger;
    }
}
