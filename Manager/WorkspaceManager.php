<?php

namespace Claroline\CoreBundle\Manager;

use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace;
use Claroline\CoreBundle\Manager\RoleManager;
use Claroline\CoreBundle\Manager\ResourceManager;
use Claroline\CoreBundle\Manager\MaskManager;
use Claroline\CoreBundle\Repository\ResourceNodeRepository;
use Claroline\CoreBundle\Repository\OrderedToolRepository;
use Claroline\CoreBundle\Repository\ResourceRightsRepository;
use Claroline\CoreBundle\Repository\ResourceTypeRepository;
use Claroline\CoreBundle\Repository\RoleRepository;
use Claroline\CoreBundle\Repository\WorkspaceRepository;
use Claroline\CoreBundle\Library\Workspace\Configuration;
use Claroline\CoreBundle\Event\StrictDispatcher;
use Claroline\CoreBundle\Pager\PagerFactory;
use Claroline\CoreBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Library\Utilities\ClaroUtilities;
use Claroline\CoreBundle\Manager\Exception\UnknownToolException;
use Symfony\Component\Yaml\Yaml;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.manager.workspace_manager")
 */
class WorkspaceManager
{
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

    /**
     * Constructor.
     *
     * @DI\InjectParams({
     *     "roleManager"     = @DI\Inject("claroline.manager.role_manager"),
     *     "maskManager"     = @DI\Inject("claroline.manager.mask_manager"),
     *     "resourceManager" = @DI\Inject("claroline.manager.resource_manager"),
     *     "toolManager"     = @DI\Inject("claroline.manager.tool_manager"),
     *     "dispatcher"      = @DI\Inject("claroline.event.event_dispatcher"),
     *     "om"              = @DI\Inject("claroline.persistence.object_manager"),
     *     "ut"              = @DI\Inject("claroline.utilities.misc"),
     *     "templateDir"     = @DI\Inject("%claroline.param.templates_directory%"),
     *     "pagerFactory"    = @DI\Inject("claroline.pager.pager_factory")
     * })
     */

    public function __construct(
        RoleManager $roleManager,
        MaskManager $maskManager,
        ResourceManager $resourceManager,
        ToolManager $toolManager,
        StrictDispatcher $dispatcher,
        ObjectManager $om,
        ClaroUtilities $ut,
        $templateDir,
        PagerFactory $pagerFactory
    )
    {
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
        $this->workspaceRepo = $om->getRepository('ClarolineCoreBundle:Workspace\AbstractWorkspace');
        $this->pagerFactory = $pagerFactory;
    }

    public function create(Configuration $config, User $manager)
    {
        $config->check();
        $this->om->startFlushSuite();
        $workspace = $this->om->factory('Claroline\CoreBundle\Entity\Workspace\SimpleWorkspace');
        $workspace->setName($config->getWorkspaceName());
        $workspace->setPublic($config->isPublic());
        $workspace->setCode($config->getWorkspaceCode());
        $workspace->setGuid($this->ut->generateGuid());
        $workspace->setDisplayable($config->isDisplayable());
        $workspace->setSelfRegistration($config->getSelfRegistration());
        $workspace->setSelfUnregistration($config->getSelfUnregistration());
        $baseRoles = $this->roleManager->initWorkspaceBaseRole($config->getRoles(), $workspace);
        $baseRoles['ROLE_ANONYMOUS'] = $this->roleRepo->findOneBy(array('name' => 'ROLE_ANONYMOUS'));
        $this->roleManager->associateRole($manager, $baseRoles["ROLE_WS_MANAGER"]);
        $dir = $this->om->factory('Claroline\CoreBundle\Entity\Resource\Directory');
        $dir->setName("{$workspace->getName()} - {$workspace->getCode()}");
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

        return $workspace;
    }

    public function createWorkspace(AbstractWorkspace $workspace)
    {
        $this->om->persist($workspace);
        $this->om->flush();
    }

    public function deleteWorkspace(AbstractWorkspace $workspace)
    {
        $root = $this->resourceManager->getWorkspaceRoot($workspace);
        
        foreach ($root->getChildren() as $node) {
            $this->resourceManager->delete($node);
        }
        $this->om->remove($workspace);
        $this->om->flush();
    }

    /**
     * @param  string      $configName
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

    public function exportToolsSection(AbstractWorkspace $workspace, $archive)
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
     * Repository functions
     */

    public function getWorkspacesByUser(User $user)
    {
        return $this->workspaceRepo->findByUser($user);
    }

    public function getNonPersonalWorkspaces()
    {
        return $this->workspaceRepo->findNonPersonal();
    }

    public function getWorkspacesByAnonymous()
    {
        return $this->workspaceRepo->findByAnonymous();
    }

    public function getNbWorkspaces()
    {
        return $this->workspaceRepo->count();
    }

    public function getWorkspacesByRoles(array $roles)
    {
        return $this->workspaceRepo->findByRoles($roles);
    }

    public function getWorkspaceIdsByUserAndRoleNames(User $user, array $roleNames)
    {
        return $this->workspaceRepo->findIdsByUserAndRoleNames($user, $roleNames);
    }

    public function getWorkspacesByUserAndRoleNames(User $user, array $roleNames)
    {
        return $this->workspaceRepo->findByUserAndRoleNames($user, $roleNames);
    }

    public function getWorkspacesByUserAndRoleNamesNotIn(
        User $user,
        array $roleNames,
        array $restrictionIds = null
    )
    {
        return $this->workspaceRepo->findByUserAndRoleNamesNotIn($user, $roleNames, $restrictionIds);
    }

    public function getLatestWorkspacesByUser(User $user, array $roles, $max = 5)
    {
        return $this->workspaceRepo->findLatestWorkspacesByUser($user, $roles, $max);
    }

    public function getWorkspacesWithMostResources($max)
    {
        return $this->workspaceRepo->findWorkspacesWithMostResources($max);
    }

    public function getWorkspaceById($workspaceId)
    {
        return $this->workspaceRepo->find($workspaceId);
    }

    public function getOneByGuid($guid)
    {
        return $this->workspaceRepo->findOneByGuid($guid);
    }

    public function getDisplayableWorkspaces()
    {
        return $this->workspaceRepo->findDisplayableWorkspaces();
    }

    public function getWorkspacesWithSelfRegistration()
    {
        return $this->workspaceRepo->findWorkspacesWithSelfRegistration();
    }

    public function getDisplayableWorkspacesBySearch($search)
    {
        return $this->workspaceRepo->findDisplayableWorkspacesBySearch($search);
    }

    public function getDisplayableWorkspacesBySearchPager($search, $page)
    {
        $workspaces = $this->workspaceRepo->findDisplayableWorkspacesBySearch($search);

        return $this->pagerFactory->createPagerFromArray($workspaces, $page);
    }

    public function getWorkspacesWithSelfUnregistrationByRoles($roles, $page)
    {
        $workspaces = $this->workspaceRepo
            ->findWorkspacesWithSelfUnregistrationByRoles($roles);

        return $this->pagerFactory->createPagerFromArray($workspaces, $page);
    }
}
