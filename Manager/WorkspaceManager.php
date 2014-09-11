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

use Claroline\CoreBundle\Entity\Resource\Directory;
use Claroline\CoreBundle\Entity\Resource\ResourceRights;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Entity\Workspace\WorkspaceFavourite;
use Claroline\CoreBundle\Entity\Workspace\WorkspaceRegistrationQueue;
use Claroline\CoreBundle\Library\Security\Utilities;
use Claroline\CoreBundle\Manager\HomeTabManager;
use Claroline\CoreBundle\Manager\RoleManager;
use Claroline\CoreBundle\Manager\ResourceManager;
use Claroline\CoreBundle\Manager\MaskManager;
use Claroline\CoreBundle\Repository\ResourceNodeRepository;
use Claroline\CoreBundle\Repository\OrderedToolRepository;
use Claroline\CoreBundle\Repository\ResourceRightsRepository;
use Claroline\CoreBundle\Repository\ResourceTypeRepository;
use Claroline\CoreBundle\Repository\RoleRepository;
use Claroline\CoreBundle\Repository\WorkspaceRepository;
use Claroline\CoreBundle\Repository\UserRepository;
use Claroline\CoreBundle\Library\Workspace\Configuration;
use Claroline\CoreBundle\Event\StrictDispatcher;
use Claroline\CoreBundle\Pager\PagerFactory;
use Claroline\CoreBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Library\Utilities\ClaroUtilities;
use Claroline\CoreBundle\Manager\Exception\UnknownToolException;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @DI\Service("claroline.manager.workspace_manager")
 */
class WorkspaceManager
{
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
    /** @var ToolManager */
    private $toolManager;
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
    private $trans;

    /**
     * Constructor.
     *
     * @DI\InjectParams({
     *     "homeTabManager"  = @DI\Inject("claroline.manager.home_tab_manager"),
     *     "roleManager"     = @DI\Inject("claroline.manager.role_manager"),
     *     "maskManager"     = @DI\Inject("claroline.manager.mask_manager"),
     *     "resourceManager" = @DI\Inject("claroline.manager.resource_manager"),
     *     "toolManager"     = @DI\Inject("claroline.manager.tool_manager"),
     *     "dispatcher"      = @DI\Inject("claroline.event.event_dispatcher"),
     *     "om"              = @DI\Inject("claroline.persistence.object_manager"),
     *     "ut"              = @DI\Inject("claroline.utilities.misc"),
     *     "sut"             = @DI\Inject("claroline.security.utilities"),
     *     "templateDir"     = @DI\Inject("%claroline.param.templates_directory%"),
     *     "pagerFactory"    = @DI\Inject("claroline.pager.pager_factory"),
     *     "container"       = @DI\Inject("service_container")
     * })
     */
    public function __construct(
        HomeTabManager $homeTabManager,
        RoleManager $roleManager,
        MaskManager $maskManager,
        ResourceManager $resourceManager,
        ToolManager $toolManager,
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
        $this->toolManager = $toolManager;
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
    public function create(Configuration $configuration, User $manager, $createUsers = false, $importUsers = false)
    {
        $transfertManager = $this->container->get('claroline.manager.transfert_manager');
        $workspace = $transfertManager->createWorkspace($configuration, $manager, $createUsers, $importUsers);

        return $workspace;
    }

    /**
     * Perist and flush a workspace.
     *
     * @param \Claroline\CoreBundle\Entity\Workspace\Workspace $workspace
     */
    public function createWorkspace(Workspace $workspace)
    {
        $this->om->persist($workspace);
        $this->om->flush();
    }

    /**
     * Delete a workspace.
     *
     * @param \Claroline\CoreBundle\Entity\Workspace\Workspace $workspace
     */
    public function deleteWorkspace(Workspace $workspace)
    {
        $root = $this->resourceManager->getWorkspaceRoot($workspace);
        $children = $root->getChildren();

        if ($children) {
            foreach ($children as $node) {
                $this->resourceManager->delete($node);
            }
        }

        $this->om->remove($workspace);
        $this->om->flush();
    }

    /**
     * Open an archive and register it in the workspace template table.
     *
     * @param string $configName
     *
     * @return \ZipArchive
     */
    public function createArchive($configName)
    {
        $archive = $this->om->factory('\ZipArchive');
        $hash = $this->ut->generateGuid();
        $pathArch = $this->templateDir."{$hash}.zip";
        $template = $this->om->factory('Claroline\CoreBundle\Entity\Workspace\Template');
        $template->setHash("{$hash}.zip");
        $template->setName($configName);
        $this->om->persist($template);
        $this->om->flush();
        $archive->open($pathArch, \ZipArchive::CREATE);

        return $archive;
    }

    /**
     * Creates a workspace template.
     *
     * @param \Claroline\CoreBundle\Entity\Workspace\Workspace $workspace
     * @param string                                                   $configName
     *
     * @throws \Exception
     */
    public function export(Workspace $workspace, $configName)
    {
        if (!is_writable($this->templateDir)) {
            throw new \Exception("{$this->templateDir} is not writable");
        }

        $this->om->startFlushSuite();
        $archive = $this->createArchive($configName);
        $description = array();
        $description = array_merge($description, $this->exportRolesSection($workspace));
        $description = array_merge($description, $this->exportRootPermsSection($workspace));
        $description = array_merge($description, $this->exportToolsInfosSection($workspace));
        $description = array_merge($description, $this->exportToolsSection($workspace, $archive));
        $description['creator_role'] = 'ROLE_WS_MANAGER';
        $description['name'] = $configName;
        $yaml = Yaml::dump($description, 10);
        $archive->addFromString('config.yml', $yaml);
        $archive->close();
        $this->om->endFlushSuite();
    }

    /**
     * Generate the array concerning roles for a workspace template config.
     *
     * @param \Claroline\CoreBundle\Entity\Workspace\Workspace $workspace
     *
     * @return array
     */
    public function exportRolesSection(Workspace $workspace)
    {
        $description = array();

        $roles = $this->roleRepo->findByWorkspace($workspace);

        foreach ($roles as $role) {
            $name = $this->roleManager->getRoleBaseName($role->getName());
            $arRole[$name] = $role->getTranslationKey();
        }

        $description['roles'] = $arRole;

        return $description;
    }

    /**
     * Generate the array concerning the root permissions for a workspace template config.
     *
     * @param \Claroline\CoreBundle\Entity\Workspace\Workspace $workspace
     *
     * @return array
     */
    public function exportRootPermsSection(Workspace $workspace)
    {
        $description = array();
        $root = $this->resourceRepo->findWorkspaceRoot($workspace);
        $roles = $this->roleRepo->findByWorkspace($workspace);

        foreach ($roles as $role) {
            $mask = $this->resourceRightsRepo
                ->findMaximumRights(array($role->getName()), $root);
            $perms = $this->maskManager->decodeMask($mask, $root->getResourceType());
            $perms['create'] = $this->resourceRightsRepo
                ->findCreationRights(array($role->getName()), $root);

            $description['root_perms'][$this->roleManager->getRoleBaseName($role->getName())] = $perms;
        }

        return $description;
    }

    /**
     * Generate the array concerning the tool permissions for a workspace template config.
     *
     * @param \Claroline\CoreBundle\Entity\Workspace\Workspace $workspace
     *
     * @return array
     */
    public function exportToolsInfosSection(Workspace $workspace)
    {
        $arTools = array();
        $description = array();
        $workspaceTools = $this->orderedToolRepo->findBy(array('workspace' => $workspace), array('order' => 'ASC'));

        foreach ($workspaceTools as $workspaceTool) {
            $tool = $workspaceTool->getTool();

            $roles = $this->roleRepo->findByWorkspaceAndTool($workspace, $tool);
            $arToolRoles = array();

            foreach ($roles as $role) {
                $arToolRoles[] = $this->roleManager->getRoleBaseName($role->getName());
            }

            $toolsInfos['perms'] = $arToolRoles;
            $toolsInfos['name'] = $workspaceTool->getName();
            $arTools[$tool->getName()] = $toolsInfos;
        }

        $description['tools_infos'] = $arTools;

        return $description;
    }

    /**
     * Generate the array concerning the tool content for a workspace template config.
     *
     * @param \Claroline\CoreBundle\Entity\Workspace\Workspace $workspace
     * @param \ZipArchive                                              $archive
     *
     * @return array
     */
    public function exportToolsSection(Workspace $workspace, \ZipArchive $archive)
    {
        $description = array();
        $workspaceTools = $this->orderedToolRepo->findBy(array('workspace' => $workspace), array('order' => 'ASC'));

        foreach ($workspaceTools as $workspaceTool) {
            $tool = $workspaceTool->getTool();

            if ($workspaceTool->getTool()->isExportable()) {
                $event = $this->dispatcher->dispatch(
                    "tool_{$tool->getName()}_to_template",
                    'ExportTool',
                    array($workspace)
                );
                $description['tools'][$tool->getName()] = $event->getConfig();
                $description['tools'][$tool->getName()]['files'] = $event->getFilenamesFromArchive();

                foreach ($event->getFiles() as $file) {
                    $archive->addFile($file['original_path'], $file['archive_path']);
                }
            }
        }

        return $description;
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
    public function getWorkspacesByAnonymous()
    {
        return $this->workspaceRepo->findByAnonymous();
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
     * @param string[] $roles
     *
     * @return \Claroline\CoreBundle\Entity\Workspace\Workspace[]
     */
    public function getOpenableWorkspacesByRoles(array $roles)
    {
        $workspaces = $this->workspaceRepo->findByRoles($roles);

        foreach ($roles as $role) {

            if (strpos('_' . $role, 'ROLE_WS_MANAGER')) {
                $workspace = $this->roleRepo->findOneByName($role)->getWorkspace();
                $workspaces[] = $workspace;
            }
        }

        $ids = [];

        return array_filter($workspaces, function ($workspace) use (&$ids) {
            if (!in_array($workspace->getId(), $ids)) {
                $ids[] = $workspace->getId();

                return true;
            }
        });
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
    public function getAccesses(TokenInterface $token, array $workspaces, $toolName = null)
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
                ->findOpenWorkspaceIds($userRoleNames, $workspacesWithoutManagerRole, $toolName);

            foreach ($openWsIds as $idRow) {
                $accesses[$idRow['id']] = true;
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
        $page
    )
    {
        if (count($roleNames) > 0) {
            $workspaces = $this->workspaceRepo
                ->findByRoleNamesBySearch($roleNames, $search);
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
    public function getDisplayableWorkspacesPager($page)
    {
        $workspaces = $this->workspaceRepo->findDisplayableWorkspaces();

        return $this->pagerFactory->createPagerFromArray($workspaces, $page);
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
    public function getDisplayableWorkspacesBySearchPager($search, $page)
    {
        $workspaces = $this->workspaceRepo->findDisplayableWorkspacesBySearch($search);

        return $this->pagerFactory->createPagerFromArray($workspaces, $page);
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
        array $roles
    )
    {
        return $this->workspaceRepo->findWorkspaceByWorkspaceAndRoles(
            $workspace,
            $roles
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
        }

        $token = new UsernamePasswordToken($user, null, 'main', $user->getRoles());
        $this->container->get('security.context')->setToken($token);

        return $user;
    }

    /**
     * @param \Claroline\CoreBundle\Entity\Workspace\Workspace $source
     * @param \Claroline\CoreBundle\Entity\Workspace\Workspace $workspace
     */
    public function duplicateWorkspaceRoles(Workspace $source, Workspace $workspace)
    {
        $guid = $workspace->getGuid();
        $roles = $source->getRoles();
        $unusedRolePartName = '_' . $source->getGuid();

        $this->om->startFlushSuite();

        foreach ($roles as $role) {
            $roleName = str_replace($unusedRolePartName, '', $role->getName());

            $this->roleManager->createWorkspaceRole(
                $roleName . '_' . $guid,
                $role->getTranslationKey(),
                $workspace,
                $role->isReadOnly()
            );
        }
        $this->om->endFlushSuite();
    }

    /**
     * @param \Claroline\CoreBundle\Entity\Workspace\Workspace $source
     * @param \Claroline\CoreBundle\Entity\Workspace\Workspace $workspace
     */
    public function duplicateOrderedTools(Workspace $source, Workspace $workspace)
    {
        $orderedTools = $source->getOrderedTools();
        $workspaceRoles = array();
        $wRoles = $this->roleManager->getRolesByWorkspace($workspace);

        foreach ($wRoles as $wRole) {
            $workspaceRoles[$wRole->getTranslationKey()] = $wRole;
        }

        $this->om->startFlushSuite();

        foreach ($orderedTools as $orderedTool) {
            $workspaceOrderedTool = $this->toolManager->addWorkspaceTool(
                $orderedTool->getTool(),
                $orderedTool->getOrder(),
                $orderedTool->getName(),
                $workspace
            );

            $roles = $orderedTool->getRoles();

            foreach ($roles as $role) {

                if ($role->getType() === 1) {
                    $this->toolManager->addRoleToOrderedTool(
                        $workspaceOrderedTool,
                        $role
                    );
                } else {
                    $key = $role->getTranslationKey();

                    if (isset($workspaceRoles[$key]) && !empty($workspaceRoles[$key])) {
                        $this->toolManager->addRoleToOrderedTool(
                            $workspaceOrderedTool,
                            $workspaceRoles[$key]
                        );
                    }
                }
            }
        }
        $this->om->endFlushSuite();
    }

    /**
     * @param \Claroline\CoreBundle\Entity\Workspace\Workspace $source
     * @param \Claroline\CoreBundle\Entity\Workspace\Workspace $workspace
     * @param \Claroline\CoreBundle\Entity\User $user
     */
    public function duplicateRootDirectory(
        Workspace $source,
        Workspace $workspace,
        User $user
    )
    {
        $rootDirectory = new Directory();
        $rootDirectory->setName($workspace->getName());
        $directoryType = $this->resourceManager->getResourceTypeByName('directory');
        $this->resourceManager->create(
            $rootDirectory,
            $directoryType,
            $user,
            $workspace,
            null,
            null,
            array()
        );
//        $workspaceRoles = array();
//        $wRoles = $this->roleManager->getRolesByWorkspace($workspace);
//
//        foreach ($wRoles as $wRole) {
//            $workspaceRoles[$wRole->getTranslationKey()] = $wRole;
//        }

//        $root = $this->resourceManager->getWorkspaceRoot($source);
//        $rights = $root->getRights();
//
//        foreach ($rights as $right) {
//            $newRight = new ResourceRights();
//            $newRight->setResourceNode($rootDirectory->getResourceNode());
//            $newRight->setMask($right->getMask());
//            $newRight->setCreatableResourceTypes(
//                $right->getCreatableResourceTypes()->toArray()
//            );
//
//            $role = $right->getRole();
//
//            if ($role->getType() === 1) {
//                $newRight->setRole($role);
//            } else {
//                $key = $role->getTranslationKey();
//
//                if (isset($workspaceRoles[$key]) && !empty($workspaceRoles[$key])) {
//                    $newRight->setRole($workspaceRoles[$key]);
//                }
//            }
//            $this->om->persist($newRight);
//        }
//        $this->om->flush();
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
    public function getWorkspaceByName($search, $page, $max = 20, $orderedBy = 'id')
    {
        $query = $this->workspaceRepo->findByName($search, false, $orderedBy);

        return $this->pagerFactory->createPager($query, $page, $max);
    }

    public function countUsers($workspaceId)
    {
        return $this->workspaceRepo->countUsers($workspaceId);
    }
}
