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
use Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace;
use Claroline\CoreBundle\Entity\Workspace\WorkspaceFavourite;
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
    /** @var WorkspaceRepository */
    private $userRepo;
    /** @var UserRepository */
    private $workspaceRepo;
    /** @var ToolManager */
    private $toolManager;
    /** @var StrictDispatcher */
    private $dispatcher;
    /** @var ObjectManager */
    private $om;
    /** @var ClaroUtilities */
    private $ut;
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
     *     "homeTabManager"  = @DI\Inject("claroline.manager.home_tab_manager"),
     *     "roleManager"     = @DI\Inject("claroline.manager.role_manager"),
     *     "maskManager"     = @DI\Inject("claroline.manager.mask_manager"),
     *     "resourceManager" = @DI\Inject("claroline.manager.resource_manager"),
     *     "toolManager"     = @DI\Inject("claroline.manager.tool_manager"),
     *     "dispatcher"      = @DI\Inject("claroline.event.event_dispatcher"),
     *     "om"              = @DI\Inject("claroline.persistence.object_manager"),
     *     "ut"              = @DI\Inject("claroline.utilities.misc"),
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
        $this->workspaceRepo = $om->getRepository('ClarolineCoreBundle:Workspace\AbstractWorkspace');
        $this->workspaceFavouriteRepo = $om->getRepository('ClarolineCoreBundle:Workspace\WorkspaceFavourite');
        $this->pagerFactory = $pagerFactory;
        $this->container = $container;
    }

    /**
     * Rename a workspace.
     *
     * @param \Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace $workspace
     * @param string                                                   $name
     */
    public function rename(AbstractWorkspace $workspace, $name)
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
     * @param \Claroline\CoreBundle\Library\Workspace\Configuration $config
     * @param \Claroline\CoreBundle\Entity\User                     $manager
     *
     * @return \Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace
     *
     * @throws UnknownToolException
     */
    public function create(Configuration $config, User $manager)
    {
        $config->check();
        $this->om->startFlushSuite();
        $workspace = $this->om->factory('Claroline\CoreBundle\Entity\Workspace\SimpleWorkspace');
        $workspace->setCreator($manager);
        $workspace->setName($config->getWorkspaceName());
        $workspace->setCode($config->getWorkspaceCode());
        $workspace->setDescription($config->getWorkspaceDescription());
        $workspace->setGuid($this->ut->generateGuid());
        $workspace->setDisplayable($config->isDisplayable());
        $workspace->setSelfRegistration($config->getSelfRegistration());
        $workspace->setSelfUnregistration($config->getSelfUnregistration());
        $date = new \Datetime(date('d-m-Y H:i'));
        $workspace->setCreationDate($date->getTimestamp());
        $baseRoles = $this->roleManager->initWorkspaceBaseRole($config->getRoles(), $workspace);
        $baseRoles['ROLE_ANONYMOUS'] = $this->roleRepo->findOneBy(array('name' => 'ROLE_ANONYMOUS'));
        $this->roleManager->associateRole($manager, $baseRoles['ROLE_WS_MANAGER']);
        $dir = $this->om->factory('Claroline\CoreBundle\Entity\Resource\Directory');
        $dir->setName($workspace->getName());
        $rights = $config->getPermsRootConfiguration();
        $preparedRights = $this->prepareRightsArray($rights, $baseRoles);
        $root = $this->resourceManager->create(
            $dir,
            $this->resourceTypeRepo->findOneByName('directory'),
            $manager,
            $workspace,
            null,
            null,
            $preparedRights
        );

        $toolsConfig = $config->getToolsConfiguration();
        $toolsPermissions = $config->getToolsPermissions();

        $position = 1;

        foreach ($toolsPermissions as $toolName => $perms) {
            $rolesToAdd = array();

            foreach ($perms['perms'] as $role) {
                $rolesToAdd[] = $baseRoles[$role];
            }

            $confTool = isset($toolsConfig[$toolName]) ?  $toolsConfig[$toolName] : array();

            $tool = $this->toolManager->getOneToolByName($toolName);

            if ($tool === null) {
                throw new UnknownToolException("The tool {$toolName} does'nt exists.");
            }

            $this->toolManager->import(
                $confTool,
                $rolesToAdd,
                $baseRoles,
                $perms['name'],
                $workspace,
                $root,
                $tool,
                $manager,
                $position,
                $config->getArchive()
            );
            $position++;
        }

        $this->dispatcher->dispatch('log', 'Log\LogWorkspaceCreate', array($workspace));
        $this->om->persist($workspace);
        $this->om->endFlushSuite();

        $this->homeTabManager->generateCopyOfAdminWorkspaceHomeTabs($workspace);

        return $workspace;
    }

    /**
     * Perist and flush a workspace.
     *
     * @param \Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace $workspace
     */
    public function createWorkspace(AbstractWorkspace $workspace)
    {
        $this->om->persist($workspace);
        $this->om->flush();
    }

    /**
     * Delete a workspace.
     *
     * @param \Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace $workspace
     */
    public function deleteWorkspace(AbstractWorkspace $workspace)
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
     * @param \Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace $workspace
     * @param string                                                   $configName
     *
     * @throws \Exception
     */
    public function export(AbstractWorkspace $workspace, $configName)
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
     * @param \Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace $workspace
     *
     * @return array
     */
    public function exportRolesSection(AbstractWorkspace $workspace)
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
     * @param \Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace $workspace
     *
     * @return array
     */
    public function exportRootPermsSection(AbstractWorkspace $workspace)
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
     * @param \Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace $workspace
     *
     * @return array
     */
    public function exportToolsInfosSection(AbstractWorkspace $workspace)
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
     * @param \Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace $workspace
     * @param \ZipArchive                                              $archive
     *
     * @return array
     */
    public function exportToolsSection(AbstractWorkspace $workspace, \ZipArchive $archive)
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
     * @param \Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace $workspace
     * @param \Claroline\CoreBundle\Entity\User                        $user
     */
    public function addFavourite(AbstractWorkspace $workspace, User $user)
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
     * @return \Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace[]
     */
    public function getWorkspacesByUser(User $user)
    {
        return $this->workspaceRepo->findByUser($user);
    }

    /**
     * @return \Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace
     */
    public function getNonPersonalWorkspaces()
    {
        return $this->workspaceRepo->findNonPersonal();
    }

    /**
     * @return \Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace[]
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
     * @return \Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace[]
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
            $workspaces = $this->workspaceRepo->findByRoleNames($roleNames);
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
     * @return \Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace[]
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
     * @return \Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace[]
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
        return $this->workspaceRepo->findLatestWorkspacesByUser($user, $roles, $max);
    }

    /**
     * @param integer $max
     *
     * @return \Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace[]
     */
    public function getWorkspacesWithMostResources($max)
    {
        return $this->workspaceRepo->findWorkspacesWithMostResources($max);
    }

    /**
     * @param integer $workspaceId
     *
     * @return \Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace
     */
    public function getWorkspaceById($workspaceId)
    {
        return $this->workspaceRepo->find($workspaceId);
    }

    /**
     * @param string $guid
     *
     * @return \Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace
     */
    public function getOneByGuid($guid)
    {
        return $this->workspaceRepo->findOneByGuid($guid);
    }

    /**
     * @return \Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace[]
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
     * @return \Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace[]
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
     * @param \Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace $workspace
     * @param \Claroline\CoreBundle\Entity\Role[]                      $roles
     *
     * @return \Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace[]
     */
    public function getWorkspaceByWorkspaceAndRoles(
        AbstractWorkspace $workspace,
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
     * @return \Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace[]
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
     * @param \Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace $workspace
     * @param \Claroline\CoreBundle\Entity\User                        $user
     *
     * @return \Claroline\CoreBundle\Entity\Workspace\WorkspaceFavourite
     */
    public function getFavouriteByWorkspaceAndUser(
        AbstractWorkspace $workspace,
        User $user
    )
    {
        return $this->workspaceFavouriteRepo
            ->findOneBy(array('workspace' => $workspace, 'user' => $user));
    }

    /**
     * @param \Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace $workspace
     *
     * @return \Claroline\CoreBundle\Entity\User|null
     */
    public function findPersonalUser(AbstractWorkspace $workspace)
    {
        $user = $this->userRepo->findBy(array('personalWorkspace' => $workspace));

        return (count($user) === 1) ? $user[0]: null;
    }

    /**
     * @param \Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace $workspace
     * @param \Claroline\CoreBundle\Entity\User $user
     *
     * @return \Claroline\CoreBundle\Entity\User
     */
    public function addUserAction(AbstractWorkspace $workspace, User $user)
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
     * @param integer $page
     * @param integer $max
     * @param string  $orderedBy
     *
     * @return \Pagerfanta\Pagerfanta;
     */
    public function findAllWorkspaces($page, $max = 20, $orderedBy = 'id', $order = null)
    {
        $order = $order === 'DESC' ? 'DESC' : 'ASC';
        $tab = array($orderedBy => $order);
        $result = $this->workspaceRepo->findBy(array(), array('id' => $order));

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
