<?php

namespace Claroline\CoreBundle\Manager;

use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Resource\Directory;
use Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace;
use Claroline\CoreBundle\Entity\Workspace\Template;
use Claroline\CoreBundle\Event\Event\ExportToolEvent;
use Claroline\CoreBundle\Manager\RoleManager;
use Claroline\CoreBundle\Manager\ResourceManager;
use Claroline\CoreBundle\Repository\AbstractResourceRepository;
use Claroline\CoreBundle\Repository\OrderedToolRepository;
use Claroline\CoreBundle\Repository\ResourceRightsRepository;
use Claroline\CoreBundle\Repository\ResourceTypeRepository;
use Claroline\CoreBundle\Repository\RoleRepository;
use Claroline\CoreBundle\Repository\WorkspaceRepository;
use Claroline\CoreBundle\Library\Workspace\Configuration;
use Claroline\CoreBundle\Event\StrictDispatcher;
use Claroline\CoreBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Library\Utilities\ClaroUtilities;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Yaml\Yaml;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.manager.workspace_manager")
 */
class WorkspaceManager
{
    /** @var OrderedToolRepository */
    private $orderedToolRepo;
    /** @var RoleManager */
    private $roleManager;
    /** @var ResourceManager */
    private $resourceManager;
    /** @var AbstractResourceRepository */
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

    /**
     * Constructor.
     *
     * @DI\InjectParams({
     *     "roleManager"     = @DI\Inject("claroline.manager.role_manager"),
     *     "resourceManager" = @DI\Inject("claroline.manager.resource_manager"),
     *     "toolManager"     = @DI\Inject("claroline.manager.tool_manager"),
     *     "dispatcher"      = @DI\Inject("claroline.event.event_dispatcher"),
     *     "om"              = @DI\Inject("claroline.persistence.object_manager"),
     *     "ut"              = @DI\Inject("claroline.utilities.misc"),
     *     "templateDir"     = @DI\Inject("%claroline.param.templates_directory%")
     * })
     */
        
    public function __construct(
        RoleManager $roleManager,
        ResourceManager $resourceManager,
        ToolManager $toolManager,
        StrictDispatcher $dispatcher,
        ObjectManager $om,
        ClaroUtilities $ut,
        $templateDir
    )
    {
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
        $this->resourceRepo = $om->getRepository('ClarolineCoreBundle:Resource\AbstractResource');
        $this->resourceRightsRepo = $om->getRepository('ClarolineCoreBundle:Resource\ResourceRights');
        $this->roleRepo = $om->getRepository('ClarolineCoreBundle:Role');
        $this->workspaceRepo = $om->getRepository('ClarolineCoreBundle:Workspace\AbstractWorkspace');
    }

    public function create(Configuration $config, User $manager)
    {
        $this->om->startFlushSuite();
        $workspace = $this->om->factory('Claroline\CoreBundle\Entity\Workspace\SimpleWorkspace');
        $workspace->setName($config->getWorkspaceName());
        $workspace->setPublic($config->isPublic());
        $workspace->setCode($config->getWorkspaceCode());
        $workspace->setGuid($this->ut->generateGuid());
        $baseRoles = $this->roleManager->initWorkspaceBaseRole($config->getRoles(), $workspace);
        $baseRoles['ROLE_ANONYMOUS'] = $this->roleRepo->findOneBy(array('name' => 'ROLE_ANONYMOUS'));
        $this->roleManager->associateRole($manager, $baseRoles["ROLE_WS_MANAGER"]);
        $dir = new Directory();
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

        $filePaths = $this->extractFiles($config);
        $toolsConfig = $config->getToolsConfiguration();
        $toolsPermissions = $config->getToolsPermissions();

        $position = 1;

        foreach ($toolsPermissions as $toolName => $perms) {
            $rolesToAdd = array();

            foreach ($perms['perms'] as $role) {
                $rolesToAdd[] = $baseRoles[$role];
            }

            $confTool = isset($toolsConfig[$toolName]) ?  $toolsConfig[$toolName] : array();

            $this->toolManager->import(
                $confTool,
                $rolesToAdd,
                $filePaths,
                $perms['name'],
                $workspace,
                $root,
                $this->toolManager->findOneByName($toolName),
                $manager,
                $position
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
        $this->om->flush($workspace);
    }

    public function export(AbstractWorkspace $workspace, $configName)
    {
        if (!is_writable($this->templateDir)) {
            throw new \Exception("{$this->templatir} is not writable");
        }

        $this->om->startFlushSuite();
        $archive = new \ZipArchive();
        $hash = $this->resourceManager->generateGuid();
        $pathArch = $this->templateDir."{$hash}.zip";
        $template = new Template();
        $template->setHash("{$hash}.zip");
        $template->setName($configName);
        $archive->open($pathArch, \ZipArchive::CREATE);
        $arTools = array();
        $description = array();
        $workspaceTools = $this->orderedToolRepo->findBy(array('workspace' => $workspace));
        $roles = $this->roleRepo->findByWorkspace($workspace);
        $root = $this->resourceRepo->findWorkspaceRoot($workspace);

        foreach ($roles as $role) {
            $name = rtrim(str_replace(range(0, 9), '', $role->getName()), '_');
            $arRole[$name] = $role->getTranslationKey();
        }

        foreach ($roles as $role) {
            $perms = $this->resourceRightsRepo
                ->findMaximumRights(array($role->getName()), $root);
            $perms['canCreate'] = $this->resourceRightsRepo
                ->findCreationRights(array($role->getName()), $root);

            $description['root_perms'][rtrim(str_replace(range(0, 9), '', $role->getName()), '_')] = $perms;
        }

        foreach ($workspaceTools as $workspaceTool) {

            $tool = $workspaceTool->getTool();
            $roles = $this->roleRepo->findByWorkspaceAndTool($workspace, $tool);
            $arToolRoles = array();

            foreach ($roles as $role) {
                $arToolRoles[] = rtrim(str_replace(range(0, 9), '', $role->getName()), '_');
            }

            $arTools[$tool->getName()]['perms'] = $arToolRoles;
            $arTools[$tool->getName()]['name'] = $workspaceTool->getName();

            if ($workspaceTool->getTool()->isExportable()) {
                $this->dispatcher->dispatch('tool_'.$tool->getName().'_to_template', 'ExportTool', array($workspace));
                $description['tools'][$tool->getName()] = $event->getConfig();
                $description['tools'][$tool->getName()]['files'] = $event->getFilenamesFromArchive();

                foreach ($event->getFiles() as $file) {
                    $archive->addFile($file['original_path'], $file['archive_path']);
                }
            }
        }

        $description['roles'] = $arRole;
        $description['creator_role'] = 'ROLE_WS_MANAGER';
        $description['tools_infos'] = $arTools;
        $description['name'] = $configName;
        $yaml = Yaml::dump($description, 10);
        $archive->addFromString('config.yml', $yaml);
        $archive->close();
        $this->om->endFlushSuite();
    }

    private function prepareRightsArray(array $rights, array $roles)
    {
        $preparedRightsArray = array();

        foreach ($rights as $key => $right) {
            $preparedRights = $right;
            $preparedRights['role'] = $roles[$key];
            $preparedRightsArray[] = $preparedRights;
        }

        return $preparedRightsArray;
    }

    private function extractFiles(Configuration $config)
    {
        $archpath = $config->getArchive();
        $extractPath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . uniqid('claro_ws_tmp_', true);
        $archive = new \ZipArchive();
        $archive->open($archpath);
        $archive->extractTo($extractPath);
        $realPaths = array();
        $confTools = $config->getToolsConfiguration();

        if (isset($confTools['files'])) {
            foreach ($config['files'] as $path) {
                $realPaths[] = $extractPath . DIRECTORY_SEPARATOR . $path;
            }
        }

        return $realPaths;
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
}
