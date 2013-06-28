<?php

namespace Claroline\CoreBundle\Manager;

use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Resource\Directory;
use Claroline\CoreBundle\Manager\RoleManager;
use Claroline\CoreBundle\Manager\ResourceManager;
use Claroline\CoreBundle\Repository\ResourceTypeRepository;
use Claroline\CoreBundle\Repository\RoleRepository;
use Claroline\CoreBundle\Library\Workspace\Configuration;
use Claroline\CoreBundle\Writer\WorkspaceWriter;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.manager.workspace_manager")
 */
class WorkspaceManager
{
    /** @var WorkspaceWriter */
    private $writer;
    /** @var RoleManager */
    private $roleManager;
    /** @var ResourceManager */
    private $resourceManager;
    /** @var ResourceTypeRepository */
    private $resourceTypeRepo;
    /** @var RoleRepository */
    private $roleRepo;
    /** @var ToolManager */
    private $toolManager;

    /**
     * Constructor.
     *
     * @DI\InjectParams({
     *     "writer" = @DI\Inject("claroline.writer.workspace_writer"),
     *     "roleManager" = @DI\Inject("claroline.manager.role_manager"),
     *     "resourceManager" = @DI\Inject("claroline.manager.resource_manager"),
     *     "toolManager" = @DI\Inject("claroline.manager.tool_manager"),
     *     "resourceTypeRepo" = @DI\Inject("resource_type_repository"),
     *     "roleRepo" = @DI\Inject("role_repository"),
     * })
     */
    public function __construct(
        WorkspaceWriter $writer,
        RoleManager $roleManager,
        ResourceManager $resourceManager,
        ToolManager $toolManager,
        ResourceTypeRepository $resourceTypeRepo,
        RoleRepository $roleRepo
    )
    {
        $this->writer = $writer;
        $this->roleManager = $roleManager;
        $this->resourceManager = $resourceManager;
        $this->resourceTypeRepo = $resourceTypeRepo;
        $this->toolManager = $toolManager;
        $this->roleRepo = $roleRepo;
    }

    public function create(Configuration $config, User $manager)
    {
        $workspace = $this->writer->create(
            $config->getWorkspaceName(),
            $config->getWorkspaceCode(),
            $config->isPublic()
        );

        $baseRoles = $this->roleManager->initWorkspaceBaseRole($config->getRoles(), $workspace);
        $baseRoles['ROLE_ANONYMOUS'] = $this->roleRepo->findOneBy(array('name' => 'ROLE_ANONYMOUS'));
        $this->roleManager->bind($baseRoles["ROLE_WS_MANAGER"], $manager);
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

        $extractPath = $this->extractTemplate($config->getArchive());
        $toolsConfig = $config->getToolsConfiguration();
        $toolsPermissions = $config->getToolsPermissions();

        $position = 0;
        foreach ($toolsPermissions as $toolName => $perms) {
            $confTool = isset($toolsConfig[$toolName]) ?  $toolsConfig[$toolName] : array();
            $this->toolManager->import(
                $perms,
                $confTool,
                $baseRoles,
                $toolName,
                $workspace,
                $root,
                $manager,
                $extractPath,
                $position
            );
            $position++;
        }
        
        return $workspace;
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

    private function extractTemplate($archpath)
    {
        $extractPath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . uniqid('claro_ws_tmp_', true);
        $archive = new \ZipArchive();
        $archive->open($archpath);
        $archive->extractTo($extractPath);

        return $extractPath;
    }
}
