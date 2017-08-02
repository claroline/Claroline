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

use Claroline\BundleRecorder\Log\LoggableTrait;
use Claroline\CoreBundle\Entity\Home\HomeTab;
use Claroline\CoreBundle\Entity\Home\HomeTabConfig;
use Claroline\CoreBundle\Entity\Resource\AbstractResource;
use Claroline\CoreBundle\Entity\Resource\Directory;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\Resource\ResourceRights;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Widget\WidgetDisplayConfig;
use Claroline\CoreBundle\Entity\Widget\WidgetHomeTabConfig;
use Claroline\CoreBundle\Entity\Widget\WidgetInstance;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Entity\Workspace\WorkspaceFavourite;
use Claroline\CoreBundle\Entity\Workspace\WorkspaceOptions;
use Claroline\CoreBundle\Entity\Workspace\WorkspaceRegistrationQueue;
use Claroline\CoreBundle\Event\NotPopulatedEventException;
use Claroline\CoreBundle\Event\StrictDispatcher;
use Claroline\CoreBundle\Library\Security\Utilities;
use Claroline\CoreBundle\Library\Transfert\Resolver;
use Claroline\CoreBundle\Library\Utilities\ClaroUtilities;
use Claroline\CoreBundle\Pager\PagerFactory;
use Claroline\CoreBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Repository\OrderedToolRepository;
use Claroline\CoreBundle\Repository\ResourceNodeRepository;
use Claroline\CoreBundle\Repository\ResourceRightsRepository;
use Claroline\CoreBundle\Repository\ResourceTypeRepository;
use Claroline\CoreBundle\Repository\RoleRepository;
use Claroline\CoreBundle\Repository\UserRepository;
use Claroline\CoreBundle\Repository\WorkspaceRepository;
use Doctrine\Common\Persistence\ObjectRepository;
use JMS\DiExtraBundle\Annotation as DI;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

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
    /** @var ObjectRepository */
    private $workspaceOptionsRepo;
    /** @var StrictDispatcher */
    private $dispatcher;
    /** @var ObjectManager */
    private $om;
    /** @var ClaroUtilities */
    private $ut;
    private $sut;
    /** @var PagerFactory */
    private $pagerFactory;
    /** @var WorkspaceFavouriteRepository */
    private $workspaceFavouriteRepo;
    private $container;
    /** @var array */
    private $importData;
    private $templateDirectory;

    /**
     * WorkspaceManager constructor.
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
     *     "pagerFactory"          = @DI\Inject("claroline.pager.pager_factory"),
     *     "container"             = @DI\Inject("service_container")
     * })
     *
     * @param HomeTabManager     $homeTabManager
     * @param RoleManager        $roleManager
     * @param MaskManager        $maskManager
     * @param ResourceManager    $resourceManager
     * @param StrictDispatcher   $dispatcher
     * @param ObjectManager      $om
     * @param ClaroUtilities     $ut
     * @param Utilities          $sut
     * @param PagerFactory       $pagerFactory
     * @param ContainerInterface $container
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
        PagerFactory $pagerFactory,
        ContainerInterface $container
    ) {
        $this->homeTabManager = $homeTabManager;
        $this->maskManager = $maskManager;
        $this->roleManager = $roleManager;
        $this->resourceManager = $resourceManager;
        $this->ut = $ut;
        $this->sut = $sut;
        $this->om = $om;
        $this->dispatcher = $dispatcher;
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
        $this->importData = [];
        $this->templateDirectory = $container->getParameter('claroline.param.templates_directory');
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
        $root->setName($name);

        $this->om->persist($workspace);
        $this->om->persist($root);

        $this->om->flush();
    }

    /**
     * Creates a workspace.
     *
     * @param Workspace $workspace
     * @param File      $template
     *
     * @return Workspace
     */
    public function create(Workspace $workspace, File $template)
    {
        $transferManager = $this->container->get('claroline.manager.transfer_manager');

        if ($this->logger) {
            $transferManager->setLogger($this->logger);
        }

        $workspace = $transferManager->createWorkspace($workspace, $template, false);

        return $workspace;
    }

    /**
     * Creates a workspace.
     *
     * @param Workspace $workspace
     * @param $template uncompressed template
     *
     * @return \Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace
     */
    public function createFromTemplate(Workspace $workspace, $templateDirectory)
    {
        $transferManager = $this->container->get('claroline.manager.transfer_manager');

        if ($this->logger) {
            $transferManager->setLogger($this->logger);
        }

        $workspace = $transferManager->createWorkspaceFromTemplate($workspace, $templateDirectory, false);

        return $workspace;
    }

    public function createWorkspace(Workspace $workspace)
    {
        if (count($workspace->getOrganizations()) === 0) {
            $organizationManager = $this->container->get('claroline.manager.organization.organization_manager');
            $default = $organizationManager->getDefault();
            $workspace->addOrganization($default);
        }

        $ch = $this->container->get('claroline.config.platform_config_handler');
        if (!$workspace->getGuid()) {
            $workspace->setGuid(uniqid('', true));
        }
        $workspace->setMaxUploadResources($ch->getParameter('max_upload_resources'));
        $workspace->setMaxStorageSize($ch->getParameter('max_storage_size'));
        $workspace->setMaxUsers($ch->getParameter('max_workspace_users'));
        $this->editWorkspace($workspace);

        return $workspace;
    }

    public function editWorkspace(Workspace $workspace)
    {
        $this->om->persist($workspace);
        $this->om->flush();
    }

    /**
     * Delete a workspace.
     *
     * @param Workspace $workspace
     */
    public function deleteWorkspace(Workspace $workspace)
    {
        $this->om->startFlushSuite();
        $root = $this->resourceManager->getWorkspaceRoot($workspace);

        if ($root) {
            $this->log('Removing root directory '.$root->getName().'[id:'.$root->getId().']');
            $children = $root->getChildren();
            $this->log('Looping through '.count($children).' children...');

            if ($children) {
                foreach ($children as $node) {
                    $this->resourceManager->delete($node);
                }
            }
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
     * Appends a role list to a right array.
     *
     * @param array $rights
     * @param array $roles
     *
     * @return array
     */
    public function prepareRightsArray(array $rights, array $roles)
    {
        $preparedRightsArray = [];

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
     * @param Workspace $workspace
     * @param User      $user
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
     * @param WorkspaceFavourite $favourite
     */
    public function removeFavourite(WorkspaceFavourite $favourite)
    {
        $this->om->remove($favourite);
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

    public function exportWorkspace(Workspace $workspace)
    {
        return [
          'id' => $workspace->getId(),
          'guid' => $workspace->getGuid(),
          'name' => $workspace->getName(),
          'description' => $workspace->getDescription(),
          'code' => $workspace->getCode(),
          'maxStorageSize' => $workspace->getMaxStorageSize(),
          'maxUploadResources' => $workspace->getMaxUploadResources(),
          'maxUsers' => $workspace->getMaxUsers(),
          'displayable' => $workspace->isDisplayable(),
          'creatorId' => $workspace->getCreator()->getId(),
          'selfRegistration' => $workspace->getSelfRegistration(),
          'registrationValidation' => $workspace->getRegistrationValidation(),
          'selfUnregistration' => $workspace->getSelfUnregistration(),
          'creationDate' => $workspace->getCreationDate(),
          'isPersonal' => $workspace->isPersonal(),
          'startDate' => $workspace->getStartDate(),
          'endDate' => $workspace->getEndDate(),
          'isAccessDate' => $workspace->getIsAccessDate(),
          'type' => $workspace->getWorkspaceType(),
        ];
    }

    /**
     * @return Workspace
     */
    public function getNonPersonalWorkspaces()
    {
        return $this->workspaceRepo->findNonPersonal();
    }

    /**
     * @param int $orderedToolType
     *
     * @return Workspace[]
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
     * @return int
     */
    public function getNbWorkspaces()
    {
        return $this->workspaceRepo->countWorkspaces();
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
    public function getNbNonPersonalWorkspaces()
    {
        return $this->workspaceRepo->countNonPersonalWorkspaces();
    }

    /**
     * @param string[] $roles
     *
     * @return Workspace[]
     */
    public function getOpenableWorkspacesByRoles(array $roles)
    {
        return $this->workspaceRepo->findByRoles($roles);
    }

    /**
     * @param string   $search
     * @param string[] $roles
     *
     * @return Workspace[]
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
            if (in_array('ROLE_WS_MANAGER_'.$workspace->getGuid(), $userRoleNames)) {
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
     * @param string[] $roles
     * @param int      $page
     * @param int      $max
     *
     * @return \PagerFanta\PagerFanta
     */
    public function getOpenableWorkspacesByRolesPager(array $roles, $page, $max)
    {
        $workspaces = $this->getOpenableWorkspacesByRoles($roles);

        return $this->pagerFactory->createPagerFromArray($workspaces, $page, $max);
    }

    /**
     * @param string   $search
     * @param string[] $roles
     * @param int      $page
     * @param int      $max
     *
     * @return \PagerFanta\PagerFanta
     */
    public function getOpenableWorkspacesBySearchAndRolesPager($search, array $roles, $page, $max)
    {
        $workspaces = $this->getOpenableWorkspacesByRolesAndSearch($search, $roles);

        return $this->pagerFactory->createPagerFromArray($workspaces, $page, $max);
    }

    /**

     * @param User     $user
     * @param string[] $roleNames
     *
     * @return int[]
     */
    public function getWorkspaceIdsByUserAndRoleNames(User $user, array $roleNames)
    {
        return $this->workspaceRepo->findIdsByUserAndRoleNames($user, $roleNames);
    }

    /**
     * @param User     $user
     * @param string[] $roleNames
     *
     * @return Workspace[]
     */
    public function getWorkspacesByUserAndRoleNames(User $user, array $roleNames)
    {
        return $this->workspaceRepo->findByUserAndRoleNames($user, $roleNames);
    }

    /**
     * @param User     $user
     * @param string[] $roleNames
     * @param int[]    $restrictionIds
     *
     * @return Workspace[]
     */
    public function getWorkspacesByUserAndRoleNamesNotIn(
        User $user,
        array $roleNames,
        array $restrictionIds = null
    ) {
        return $this->workspaceRepo->findByUserAndRoleNamesNotIn($user, $roleNames, $restrictionIds);
    }

    /**
     * Returns an array containing.
     *
     * @param User  $user
     * @param array $roles
     * @param int   $max
     *
     * @return array
     */
    public function getLatestWorkspacesByUser(User $user, array $roles, $max = 5)
    {
        return count($roles) > 0 ?
            $this->workspaceRepo->findLatestWorkspacesByUser($user, $roles, $max) :
            [];
    }

    /**
     * @param int $max
     *
     * @return Workspace[]
     */
    public function getWorkspacesWithMostResources($max)
    {
        return $this->workspaceRepo->findWorkspacesWithMostResources($max);
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
     * @param string $guid
     *
     * @return Workspace
     */
    public function getOneByGuid($guid)
    {
        return $this->workspaceRepo->findOneBy([
            'guid' => $guid,
        ]);
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

    /**
     * @return Workspace[]
     */
    public function getDisplayableWorkspaces()
    {
        return $this->workspaceRepo->findDisplayableWorkspaces();
    }

    /**
     * @param int $page
     * @param int $max
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
     * @return Workspace[]
     */
    public function getDisplayableWorkspacesBySearch($search)
    {
        return $this->workspaceRepo->findDisplayableWorkspacesBySearch($search);
    }

    /**
     * @param string $search
     * @param int    $page
     * @param int    $max
     *
     * @return \PagerFanta\PagerFanta
     */
    public function getDisplayableWorkspacesBySearchPager($search, $page, $max = 20)
    {
        $workspaces = $this->workspaceRepo->findDisplayableWorkspacesBySearch($search);

        return $this->pagerFactory->createPagerFromArray($workspaces, $page, $max);
    }

    /**
     * @param Role[] $roles
     * @param int    $page
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
     * @param Workspace $workspace
     * @param Role[]    $roles
     * @param int       $orderedToolType
     *
     * @return Workspace[]
     */
    public function getWorkspaceByWorkspaceAndRoles(
        Workspace $workspace,
        array $roles,
        $orderedToolType = 0
    ) {
        return $this->workspaceRepo->findWorkspaceByWorkspaceAndRoles(
            $workspace,
            $roles,
            $orderedToolType
        );
    }

    /**
     * @param User $user
     *
     * @return Workspace[]
     */
    public function getFavouriteWorkspacesByUser(User $user)
    {
        $workspaces = [];

        /** @var WorkspaceFavourite[] $favourites */
        $favourites = $this->om
            ->getRepository('ClarolineCoreBundle:Workspace\WorkspaceFavourite')
            ->findBy([
                'user' => $user,
            ]);

        foreach ($favourites as $favourite) {
            $workspace = $favourite->getWorkspace();
            $workspaces[$workspace->getId()] = $workspace;
        }

        return $workspaces;
    }

    /**
     * @param \Claroline\CoreBundle\Entity\Workspace\Workspace $workspace
     * @param \Claroline\CoreBundle\Entity\User                $user
     *
     * @return \Claroline\CoreBundle\Entity\Workspace\WorkspaceFavourite
     */
    public function getFavouriteByWorkspaceAndUser(
        Workspace $workspace,
        User $user
    ) {
        return $this->workspaceFavouriteRepo->findOneBy([
            'workspace' => $workspace,
            'user' => $user,
        ]);
    }

    /**
     * @param \Claroline\CoreBundle\Entity\Workspace\Workspace $workspace
     *
     * @return \Claroline\CoreBundle\Entity\User|null
     */
    public function findPersonalUser(Workspace $workspace)
    {
        $user = $this->userRepo->findBy(['personalWorkspace' => $workspace]);

        return (count($user) === 1) ? $user[0] : null;
    }

    public function addUserQueue(Workspace $workspace, User $user)
    {
        $wksrq = new WorkspaceRegistrationQueue();
        $wksrq->setUser($user);
        $role = $this->roleManager->getCollaboratorRole($workspace);
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

    /**
     * @param Workspace $workspace
     * @param User      $user
     *
     * @return User
     */
    public function addUserAction(Workspace $workspace, User $user)
    {
        $role = $this->roleManager->getCollaboratorRole($workspace);
        $userRoles = $this->roleManager->getWorkspaceRolesForUser($user, $workspace);

        if (count($userRoles) === 0) {
            $this->roleManager->associateRole($user, $role);
            $this->dispatcher->dispatch(
                'claroline_workspace_register_user',
                'WorkspaceAddUser',
                [$role, $user]
            );
        }

        $token = new UsernamePasswordToken($user, null, 'main', $user->getRoles());
        $this->container->get('security.context')->setToken($token);

        return $user;
    }

    public function countUsers(Workspace $workspace, $includeGroups = false)
    {
        if ($includeGroups) {
            $wsRoles = $this->roleManager->getRolesByWorkspace($workspace);

            return $this->container->get('claroline.manager.user_manager')->countByRoles($wsRoles, true);
        }

        return $this->workspaceRepo->countUsers($workspace->getId());
    }

    /**
     * Import a workspace list from a csv data.
     *
     * @param array    $workspaces
     * @param callable $logger
     * @param bool     $update
     */
    public function importWorkspaces(array $workspaces, $logger = null, $update = false)
    {
        $i = 0;
        $this->om->startFlushSuite();

        foreach ($workspaces as $workspace) {
            ++$i;
            $endDate = null;
            $model = null;
            $name = $workspace[0];
            $code = $workspace[1];
            $isVisible = $workspace[2];
            $selfRegistration = $workspace[3];
            $registrationValidation = $workspace[4];
            $selfUnregistration = $workspace[5];

            if (isset($workspace[6]) && trim($workspace[6]) !== '') {
                $user = $this->om
                    ->getRepository('ClarolineCoreBundle:User')
                    ->findOneBy([
                        'username' => $workspace[6],
                    ]);
            } else {
                $user = $this->container->get('security.context')->getToken()->getUser();
            }

            if (isset($workspace[7])) {
                $model = $this->workspaceRepo->findOneBy([
                    'code' => $workspace[7],
                ]);
            }

            if (isset($workspace[8])) {
                $endDate = new \DateTime();
                $endDate->setTimestamp($workspace[8]);
            }

            if ($update) {
                $workspace = $this->getOneByCode($code);
                if (!$workspace) {
                    //if the workspace doesn't exists, just keep going...
                    continue;
                }
                if ($logger) {
                    $logger('Updating '.$code.' ('.$i.'/'.count($workspaces).') ...');
                }
            } else {
                $workspace = new Workspace();
            }

            $workspace->setName($name);
            $workspace->setCode($code);
            $workspace->setDisplayable($isVisible);
            $workspace->setSelfRegistration($selfRegistration);
            $workspace->setSelfUnregistration($selfUnregistration);
            $workspace->setRegistrationValidation($registrationValidation);
            $workspace->setCreator($user);

            if ($endDate) {
                $workspace->setEndDate($endDate);
            }

            if (!$update) {
                if ($logger) {
                    $logger('Creating '.$code.' ('.$i.'/'.count($workspaces).') ...');
                }
                if ($model) {
                    $guid = $this->ut->generateGuid();
                    $workspace->setGuid($guid);
                    $date = new \Datetime(date('d-m-Y H:i'));
                    $workspace->setCreationDate($date->getTimestamp());
                    $this->copy($model, $workspace, $user);
                } else {
                    $template = new File($this->container->getParameter('claroline.param.default_template'));
                    $this->container->get('claroline.manager.transfer_manager')->createWorkspace($workspace, $template, true);
                }
            } else {
                if ($model) {
                    $this->duplicateOrderedTools($model, $workspace);
                }
            }

            $this->om->persist($workspace);

            if ($logger) {
                $logger('UOW: '.$this->om->getUnitOfWork()->size());
            }

            if ($i % 100 === 0) {
                $this->om->forceFlush();
                $user = $this->om->getRepository('ClarolineCoreBundle:User')->find($user->getId());
                $this->om->merge($user);
                $this->om->refresh($user);
            }
        }

        if ($logger) {
            $logger('Final flush...');
        }

        $this->om->endFlushSuite();
    }

    public function getDisplayableNonPersonalWorkspaces(
        $page = 1,
        $max = 50,
        $search = ''
    ) {
        $workspaces = $search === '' ?
            $this->workspaceRepo->findDisplayableNonPersonalWorkspaces() :
            $this->workspaceRepo->findDisplayableNonPersonalWorkspacesBySearch($search);

        return $this->pagerFactory->createPagerFromArray($workspaces, $page, $max);
    }

    public function getDisplayablePersonalWorkspaces(
        $page = 1,
        $max = 50,
        $search = ''
    ) {
        $workspaces = $search === '' ?
            $this->workspaceRepo->findDisplayablePersonalWorkspaces() :
            $this->workspaceRepo->findDisplayablePersonalWorkspacesBySearch($search);

        return $this->pagerFactory->createPagerFromArray($workspaces, $page, $max);
    }

    public function getAllNonPersonalWorkspaces(
        $page = 1,
        $max = 50,
        $search = '',
        $orderedBy = 'name',
        $order = 'ASC'
    ) {
        $workspaces = $search === '' ?
            $this->workspaceRepo
                ->findAllNonPersonalWorkspaces(
                    $orderedBy,
                    $order,
                    $this->container->get('security.context')->getToken()->getUser()
                ) :
            $this->workspaceRepo
                ->findAllNonPersonalWorkspacesBySearch(
                    $search,
                    $orderedBy,
                    $order,
                    $this->container->get('security.context')->getToken()->getUser()
                );

        return $this->pagerFactory->createPagerFromArray($workspaces, $page, $max);
    }

    public function getWorkspaceByCode($workspaceCode, $executeQuery = true)
    {
        return $this->workspaceRepo
            ->findWorkspaceByCode($workspaceCode, $executeQuery);
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

    public function getWorkspaceCodesWithPrefix($prefix, $executeQuery = true)
    {
        return $this->workspaceRepo->findWorkspaceCodesWithPrefix(
            $prefix,
            $executeQuery
        );
    }

    public function toArray(Workspace $workspace)
    {
        $data = [];
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
            $details = [
                'hide_tools_menu' => false,
                'background_color' => null,
            ];
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
        $rm = $this->container->get('claroline.manager.resource_manager');
        $tm = $this->container->get('claroline.manager.transfer_manager');

        if (!$rm->getLogger()) {
            $rm->setLogger($logger);
        }

        if (!$tm->getLogger()) {
            $tm->setLogger($logger);
        }

        $this->logger = $logger;
    }

    public function getLogger()
    {
        return $this->logger;
    }

    public function getTemplateData(File $file, $refresh = false)
    {
        //from cache
        if (!$refresh) {
            return $this->importData;
        }

        $archive = new \ZipArchive();
        $fileName = $file->getBasename('.zip');
        $extractPath = $this->templateDirectory.$fileName;

        if ($archive->open($file->getPathname())) {
            $fs = new FileSystem();
            $fs->mkdir($extractPath);
            $this->log("Extracting workspace to {$extractPath}...");

            if (!$archive->extractTo($extractPath)) {
                throw new \Exception("The workspace archive couldn't be extracted");
            }

            $archive->close();
            $resolver = new Resolver($extractPath);
            $this->importData = $resolver->resolve();

            return $this->importData;
        }

        throw new \Exception("The workspace archive couldn't be opened");
    }

    public function removeTemplate(File $file)
    {
        $fileName = $file->getBasename('.zip');
        $extractPath = $this->templateDirectory.DIRECTORY_SEPARATOR.$fileName;
        $this->removeTemplateDirectory($extractPath);
    }

    public function removeTemplateDirectory($extractPath)
    {
        $fs = new FileSystem();
        $fs->remove($extractPath);
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
            $this->workspaceRepo->findBy(['isPersonal' => false]) :
            $this->workspaceRepo->findNonPersonalByCodeAndName($code, $name, $offset, $limit);
    }

    /**
     * This method will bind each workspaces that don't already have an organization to the default one.
     */
    public function bindWorkspaceToOrganization()
    {
        $limit = 250;
        $offset = 0;
        $organizationManager = $this->container->get('claroline.manager.organization.organization_manager');
        $this->log('Add organizations to workspaces...');
        $this->om->startFlushSuite();
        $countWorkspaces = $this->om->count('ClarolineCoreBundle:Workspace\Workspace');

        while ($offset < $countWorkspaces) {
            //if there is too many workspaces, we retrieve them by small amounts
            $workspaces = $this->workspaceRepo->findBy([], null, $limit, $offset);
            $default = $organizationManager->getDefault();
            $this->om->merge($default);

            foreach ($workspaces as $workspace) {
                if (count($workspace->getOrganizations()) === 0) {
                    $this->log('Add default organization for workspace '.$workspace->getCode());
                    $workspace->addOrganization($default);
                    $this->om->persist($workspace);
                } else {
                    $this->log('Organization already exists for workspace '.$workspace->getCode());
                }
            }

            $this->log("Flushing... [UOW = {$this->om->getUnitOfWork()->size()}]");
            $this->om->forceFlush();
            $this->om->clear();

            $offset += $limit;
        }

        $this->om->endFlushSuite();
    }

    public function isManager(Workspace $workspace, TokenInterface $token)
    {
        $roles = array_map(
          function ($role) {
              return $role->getRole();
          },
          $token->getRoles()
      );

        $managerRole = $this->roleManager->getManagerRole($workspace);

        if ($workspace->getCreator() === $token->getUser()) {
            return true;
        }

        foreach ($roles as $role) {
            if (is_object($role) && $role->getName() === $managerRole) {
                return true;
            }
        }

        return false;
    }

    //used for cli copy
    public function copyFromCode(Workspace $workspace, $code)
    {
        $newWorkspace = new Workspace();
        $newWorkspace->setCode($code);
        $newWorkspace->setName($code);

        return $this->copy($workspace, $newWorkspace);
    }

    public function copy(Workspace $workspace, Workspace $newWorkspace)
    {
        $newWorkspace->setGuid(uniqid('', true));
        $this->createWorkspace($newWorkspace);
        $token = $this->container->get('security.token_storage')->getToken();
        $user = null;
        $resourceInfos = ['copies' => []];

        if ($token && $token->getUser() !== 'anon.') {
            $user = $workspace->getCreator() ?
            $newWorkspace->getCreator() :
            $this->container->get('security.token_storage')->getToken()->getUser();
        }

        //last fool proof check in case something weird happens
        if (!$user) {
            $user = $this->container->get('claroline.manager.user_manager')->getDefaultUser();
        }

        $this->om->startFlushSuite();
        $this->duplicateWorkspaceOptions($workspace, $newWorkspace);
        $this->duplicateWorkspaceRoles($workspace, $newWorkspace, $user);
        $baseRoot = $this->duplicateRoot($workspace, $newWorkspace, $user);
        $resourceNodes = $this->resourceManager->getWorkspaceRoot($workspace)->getChildren()->toArray();
        $toCopy = [];

        foreach ($resourceNodes as $resourceNode) {
            $toCopy[$resourceNode->getGuid()] = $resourceNode;
        }

        foreach ($resourceNodes as $resourceNode) {
            if ($resourceNode->getResourceType()->getName() === 'activity' && $this->resourceManager->getResourceFromNode($resourceNode)) {
                $primRes = $this->resourceManager->getResourceFromNode($resourceNode)->getPrimaryResource();
                $parameters = $this->resourceManager->getResourceFromNode($resourceNode)->getParameters();
                if ($primRes) {
                    unset($toCopy[$primRes->getGuid()]);
                }
                if ($parameters) {
                    foreach ($parameters->getSecondaryResources() as $secRes) {
                        unset($toCopy[$secRes->getGuid()]);
                    }
                }
                unset($toCopy[$resourceNode->getGuid()]);
            }
        }

        $this->duplicateResources(
          $toCopy,
          $this->getArrayRolesByWorkspace($newWorkspace),
          $user,
          $baseRoot,
          $resourceInfos
        );
        $this->duplicateOrderedTools($workspace, $newWorkspace, $resourceInfos);
        $this->om->endFlushSuite();

        return $newWorkspace;
    }

    public function duplicateRoot(Workspace $source, Workspace $workspace, User $user)
    {
        $this->log('Duplicating root directory...');
        $rootDirectory = new Directory();
        $rootDirectory->setName($workspace->getName());
        $directoryType = $this->resourceManager->getResourceTypeByName('directory');

        $rootCopy = $this->resourceManager->create(
            $rootDirectory,
            $directoryType,
            $user,
            $workspace,
            null,
            null,
            []
        );

        $workspaceRoles = $this->getArrayRolesByWorkspace($workspace);
        $baseRoot = $this->resourceManager->getWorkspaceRoot($source);

        /*** Copies rights ***/

        $this->duplicateRights(
            $baseRoot,
            $rootCopy->getResourceNode(),
            $workspaceRoles
        );

        return $rootCopy->getResourceNode();
    }

    /**
     * @param ResourceNode[] $resourceNodes
     * @param Role[]         $workspaceRoles
     * @param User           $user
     * @param ResourceNode   $rootNode
     */
    public function duplicateResources(
        array $resourceNodes,
        array $workspaceRoles,
        User $user,
        ResourceNode $rootNode,
        &$resourceInfos
    ) {
        $ids = [];
        $resourceNodes = array_filter($resourceNodes, function ($node) use ($ids) {
            if (!in_array($node->getId(), $ids)) {
                $ids[] = $node->getId();

                return true;
            }

            return false;
        });
        $this->om->flush();
        $this->om->startFlushSuite();
        $copies = [];
        $resourcesErrors = [];
        $this->log('Duplicating '.count($resourceNodes).' children...');
        foreach ($resourceNodes as $resourceNode) {
            try {
                $this->log('Duplicating '.$resourceNode->getName().' - '.$resourceNode->getId().' - from type '.$resourceNode->getResourceType()->getName().' into '.$rootNode->getName());
                //activities will be removed anyway
                if ($resourceNode->getResourceType()->getName() !== 'activity') {
                    $copy = $this->resourceManager->copy(
                      $resourceNode,
                      $rootNode,
                      $user,
                      false,
                      false
                  );
                    if ($copy) {
                        $copy->getResourceNode()->setIndex($resourceNode->getIndex());
                        $this->om->persist($copy->getResourceNode());
                        $resourceInfos['copies'][] = ['original' => $resourceNode, 'copy' => $copy->getResourceNode()];
                        /*** Copies rights ***/
                        $this->duplicateRights(
                            $resourceNode,
                            $copy->getResourceNode(),
                            $workspaceRoles
                        );
                    }
                }
            } catch (NotPopulatedEventException $e) {
                $resourcesErrors[] = [
                    'resourceName' => $resourceNode->getName(),
                    'resourceType' => $resourceNode->getResourceType()->getName(),
                    'type' => 'copy',
                    'error' => $e->getMessage(),
                ];
                continue;
            }
        }

        /*** Sets previous and next for each copied resource ***/
        $this->linkResourcesArray($copies);
        $this->om->endFlushSuite();
    }

    /**
     * @param AbstractResource[] $resources
     */
    public function linkResourcesArray(array $resources)
    {
        for ($i = 1; $i < count($resources); ++$i) {
            $node = $resources[$i]->getResourceNode();
            $node->setIndex($i);
            $this->om->persist($node);
        }
    }

    /**
     * @param ResourceNode $resourceNode
     * @param ResourceNode $copy
     * @param array        $workspaceRoles
     */
    public function duplicateRights(
        ResourceNode $resourceNode,
        ResourceNode $copy,
        array $workspaceRoles
    ) {
        $this->log('Start duplicate');
        $rights = $resourceNode->getRights();

        foreach ($rights as $right) {
            $role = $right->getRole();
            $key = $role->getTranslationKey();
            $newRight = new ResourceRights();
            $newRight->setResourceNode($copy);
            $newRight->setMask($right->getMask());
            $newRight->setCreatableResourceTypes(
                $right->getCreatableResourceTypes()->toArray()
            );
            if ($role->getWorkspace()) {
                if (
                isset($workspaceRoles[$key]) &&
                !empty($workspaceRoles[$key])
                ) {
                    $usedRole = $copy->getWorkspace()->getGuid() === $workspaceRoles[$key]->getWorkspace()->getGuid() ?
                      $workspaceRoles[$key] : $role;
                    $newRight->setRole($role);
                    $this->log('Duplicating resource rights for '.$copy->getName().' - '.$copy->getId().' - '.$usedRole->getName().'...');
                    $this->om->persist($newRight);
                } else {
                    $this->log('Dont do anything');
                }
            }
        }
        $this->om->flush();
    }

    /**
     * @param Workspace $source
     * @param Workspace $workspace
     */
    public function duplicateOrderedTools(Workspace $source, Workspace $workspace, $resourceInfos = ['copies' => []])
    {
        $this->log('Duplicating tools...');
        $orderedTools = $source->getOrderedTools();
        $workspaceRoles = $this->getArrayRolesByWorkspace($workspace);

        foreach ($orderedTools as $orderedTool) {
            $workspaceOrderedTool = $this->container->get('claroline.manager.tool_manager')->setWorkspaceTool(
                $orderedTool->getTool(),
                $orderedTool->getOrder(),
                $orderedTool->getName(),
                $workspace
            );
            $workspaceOrderedTool->setOrder($orderedTool->getOrder());
            $rights = $orderedTool->getRights();

            foreach ($rights as $right) {
                $role = $right->getRole();

                if ($role->getType() === 1) {
                    $this->container->get('claroline.manager.tool_rights_manager')->setToolRights(
                        $workspaceOrderedTool,
                        $role,
                        $right->getMask()
                    );
                } else {
                    $key = $role->getTranslationKey();
                    if (isset($workspaceRoles[$key]) && !empty($workspaceRoles[$key])) {
                        $this->container->get('claroline.manager.tool_rights_manager')->setToolRights(
                            $workspaceOrderedTool,
                            $workspaceRoles[$key],
                            $right->getMask()
                        );
                    }
                }
            }
        }

        $homeTabs = $this->container->get('claroline.manager.home_tab_manager')->getHomeTabByWorkspace($source);
        //get home tabs from source

        $this->duplicateHomeTabs($source, $workspace, $homeTabs, $resourceInfos);
    }

    /**
     * @param \Claroline\CoreBundle\Entity\Workspace\Workspace $source
     * @param \Claroline\CoreBundle\Entity\Workspace\Workspace $workspace
     * @param array                                            $homeTabs
     */
    private function duplicateHomeTabs(
        Workspace $source,
        Workspace $workspace,
        array $homeTabs,
        &$resourceInfos,
        &$tabInfos = []
    ) {
        $this->log('Duplicating home tabs...');
        $this->om->startFlushSuite();
        $homeTabConfigs = $this->homeTabManager
            ->getHomeTabConfigsByWorkspaceAndHomeTabs($source, $homeTabs);
        $order = 1;
        $widgetCongigErrors = [];
        $widgetDisplayConfigs = [];
        $widgets = [];
        $widgetManager = $this->container->get('claroline.manager.widget_manager');

        foreach ($homeTabConfigs as $homeTabConfig) {
            $homeTab = $homeTabConfig->getHomeTab();
            $widgetHomeTabConfigs = $homeTab->getWidgetHomeTabConfigs();
            $wdcs = $widgetManager->getWidgetDisplayConfigsByWorkspaceAndWidgetHTCs(
                $source,
                $widgetHomeTabConfigs->toArray()
            );
            foreach ($wdcs as $wdc) {
                $widgetInstanceId = $wdc->getWidgetInstance()->getId();
                $widgetDisplayConfigs[$widgetInstanceId] = $wdc;
            }
            $newHomeTab = new HomeTab();
            $newHomeTab->setType('workspace');
            $newHomeTab->setWorkspace($workspace);
            $newHomeTab->setName($homeTab->getName());
            $this->om->persist($newHomeTab);
            $tabsInfos[] = ['original' => $homeTab, 'copy' => $newHomeTab];
            $newHomeTabConfig = new HomeTabConfig();
            $newHomeTabConfig->setHomeTab($newHomeTab);
            $newHomeTabConfig->setWorkspace($workspace);
            $newHomeTabConfig->setType('workspace');
            $newHomeTabConfig->setVisible($homeTabConfig->isVisible());
            $newHomeTabConfig->setLocked($homeTabConfig->isLocked());
            $newHomeTabConfig->setTabOrder($order);
            $this->om->persist($newHomeTabConfig);
            ++$order;
            foreach ($widgetHomeTabConfigs as $widgetConfig) {
                $widgetInstance = $widgetConfig->getWidgetInstance();
                $widgetInstanceId = $widgetInstance->getId();
                $widget = $widgetInstance->getWidget();
                $newWidgetInstance = new WidgetInstance();
                $newWidgetInstance->setIsAdmin(false);
                $newWidgetInstance->setIsDesktop(false);
                $newWidgetInstance->setWorkspace($workspace);
                $newWidgetInstance->setWidget($widget);
                $newWidgetInstance->setName($widgetInstance->getName());
                $this->om->persist($newWidgetInstance);
                $newWidgetConfig = new WidgetHomeTabConfig();
                $newWidgetConfig->setType('workspace');
                $newWidgetConfig->setWorkspace($workspace);
                $newWidgetConfig->setHomeTab($newHomeTab);
                $newWidgetConfig->setWidgetInstance($newWidgetInstance);
                $newWidgetConfig->setVisible($widgetConfig->isVisible());
                $newWidgetConfig->setLocked($widgetConfig->isLocked());
                $newWidgetConfig->setWidgetOrder($widgetConfig->getWidgetOrder());
                $this->om->persist($newWidgetConfig);
                $newWidgetDisplayConfig = new WidgetDisplayConfig();
                $newWidgetDisplayConfig->setWorkspace($workspace);
                $newWidgetDisplayConfig->setWidgetInstance($newWidgetInstance);
                if (isset($widgetDisplayConfigs[$widgetInstanceId])) {
                    $newWidgetDisplayConfig->setColor(
                        $widgetDisplayConfigs[$widgetInstanceId]->getColor()
                    );
                    $newWidgetDisplayConfig->setRow(
                        $widgetDisplayConfigs[$widgetInstanceId]->getRow()
                    );
                    $newWidgetDisplayConfig->setColumn(
                        $widgetDisplayConfigs[$widgetInstanceId]->getColumn()
                    );
                    $newWidgetDisplayConfig->setWidth(
                        $widgetDisplayConfigs[$widgetInstanceId]->getWidth()
                    );
                    $newWidgetDisplayConfig->setHeight(
                        $widgetDisplayConfigs[$widgetInstanceId]->getHeight()
                    );
                } else {
                    $newWidgetDisplayConfig->setWidth($widget->getDefaultWidth());
                    $newWidgetDisplayConfig->setHeight($widget->getDefaultHeight());
                }
                $widgets[] = ['widget' => $widget, 'original' => $widgetInstance, 'copy' => $newWidgetInstance];
                $this->om->persist($newWidgetDisplayConfig);
            }
        }
        $this->om->endFlushSuite();
        $this->om->forceFlush();
        foreach ($widgets as $widget) {
            if ($widget['widget']->isConfigurable()) {
                try {
                    $this->dispatcher->dispatch(
                        'copy_widget_config_'.$widget['widget']->getName(),
                        'CopyWidgetConfiguration',
                        [$widget['original'], $widget['copy'], $resourceInfos, $tabsInfos]
                    );
                } catch (NotPopulatedEventException $e) {
                    $widgetCongigErrors[] = [
                        'widgetName' => $widget['widget']->getName(),
                        'widgetInstanceName' => $widget['original']->getName(),
                        'error' => $e->getMessage(),
                    ];
                }
            }
        }

        return $widgetCongigErrors;
    }

    /**
     * @param \Claroline\CoreBundle\Entity\Workspace\Workspace $source
     * @param \Claroline\CoreBundle\Entity\Workspace\Workspace $workspace
     * @param \Claroline\CoreBundle\Entity\User                $user
     */
    public function duplicateWorkspaceRoles(
        Workspace $source,
        Workspace $workspace,
        User $user
    ) {
        $this->log('Duplicating roles...');
        $guid = $workspace->getGuid();
        $roles = $source->getRoles();
        foreach ($roles as $role) {
            $unusedRolePartName = '_'.$role->getWorkspace()->getGuid();
            $roleName = str_replace($unusedRolePartName, '', $role->getName());
            $this->log('Duplicating '.$role->getName().' as '.$roleName.'_'.$guid);
            $createdRole = $this->roleManager->createWorkspaceRole(
                $roleName.'_'.$guid,
                $role->getTranslationKey(),
                $workspace,
                $role->isReadOnly()
            );

            $this->om->persist($createdRole);
            if ($roleName === 'ROLE_WS_MANAGER') {
                $user->addRole($createdRole);
                $this->om->persist($user);
            }
        }
    }

    /**
     * @param \Claroline\CoreBundle\Entity\Workspace\Workspace $source
     * @param \Claroline\CoreBundle\Entity\Workspace\Workspace $workspace
     */
    public function duplicateWorkspaceOptions(Workspace $source, Workspace $workspace)
    {
        $sourceOptions = $source->getOptions();
        if (!is_null($sourceOptions)) {
            $options = new WorkspaceOptions();
            $options->setWorkspace($workspace);
            $details = $sourceOptions->getDetails();
            if (!is_null($details)) {
                $details['use_workspace_opening_resource'] = false;
                $details['workspace_opening_resource'] = null;
            }
            $options->setDetails($details);
            $workspace->setOptions($options);
            $this->om->persist($options);
            $this->om->persist($workspace);
            $this->om->flush();
        }
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
            if (get_class($entity) === 'Claroline\CoreBundle\Entity\Role') {
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

    public function getDefaultModel($isPersonal = false)
    {
        $name = $isPersonal ? 'default_personal' : 'default_workspace';
        $workspace = $this->workspaceRepo->findOneBy(['code' => $name, 'isPersonal' => $isPersonal, 'isModel' => true]);
        if (!$workspace) {
            //don't log this or it'll crash everything during the platform installation
            //(some database tables aren't already created because they come from plugins)
            $this->container->get('claroline.core_bundle.listener.log.log_listener')->disable();
            $workspace = new Workspace();
            $workspace->setName($name);
            $workspace->setIsPersonal($isPersonal);
            $workspace->setCode($name);
            $workspace->setIsModel(true);
            $workspace->setCreator($this->container->get('claroline.manager.user_manager')->getDefaultUser());
            $template = new File($this->container->getParameter('claroline.param.personal_template'));
            $this->container->get('claroline.manager.transfer_manager')->createWorkspace($workspace, $template, true);
            $this->container->get('claroline.core_bundle.listener.log.log_listener')->setDefaults();
        }

        return $workspace;
    }
}
