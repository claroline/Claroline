<?php

namespace Claroline\CoreBundle\Manager;

use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Writer\WorkspaceWriter;
use Claroline\CoreBundle\Manager\RoleManager;
use Claroline\CoreBundle\Manager\ResourceManager;
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

    /**
     * Constructor.
     *
     * @DI\InjectParams({
     *     "writer" = @DI\Inject("claroline.writer.workspace_writer"),
     *     "roleManager" = @DI\Inject("claroline.manager.role_manager"),
     *     "resourceManager" = @DI\Inject("claroline.manager.resource_manager")
     * })
     */
    public function __construct(
        WorkspaceWriter $writer,
        RoleManager $roleManager,
        ResourceManager $resourceManager
    )
    {
        $this->writer = $writer;
        $this->roleManager = $roleManager;
        $this->resourceManager = $resourceManager;
    }

    public function create(Configuration $config, User $manager)
    {
        $workspace = $this->writer->create($config);
        $baseRoles = $this->roleManager->initWorkspaceBaseRole($config->getRole(), $workspace);
        $root = $this->resourceManager->create();
    }
}
