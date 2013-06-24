<?php

namespace Claroline\CoreBundle\Manager;

use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Resource\Directory;
use Claroline\CoreBundle\Writer\WorkspaceWriter;
use Claroline\CoreBundle\Library\Workspace\Configuration;
use Claroline\CoreBundle\Manager\RoleManager;
use Claroline\CoreBundle\Manager\ResourceManager;
use Claroline\CoreBundle\Repository\ResourceTypeRepository;
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

    /**
     * Constructor.
     *
     * @DI\InjectParams({
     *     "writer" = @DI\Inject("claroline.writer.workspace_writer"),
     *     "roleManager" = @DI\Inject("claroline.manager.role_manager"),
     *     "resourceManager" = @DI\Inject("claroline.manager.resource_manager"),
     *     "resourceTypeRepo" = @DI\Inject("resource_type_repository")
     * })
     */
    public function __construct(
        WorkspaceWriter $writer,
        RoleManager $roleManager,
        ResourceManager $resourceManager,
        ResourceTypeRepository $resourceTypeRepo
    )
    {
        $this->writer = $writer;
        $this->roleManager = $roleManager;
        $this->resourceManager = $resourceManager;
        $this->resourceTypeRepo = $resourceTypeRepo;
    }

    public function create(Configuration $config, User $manager)
    {
        $workspace = $this->writer->create(
            $config->getWorkspaceName(),
            $config->getWorkspaceCode(),
            $config->isPublic()
        );

        $baseRoles = $this->roleManager->initWorkspaceBaseRole($config->getRoles(), $workspace);
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
}
