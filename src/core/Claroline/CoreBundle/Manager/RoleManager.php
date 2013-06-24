<?php

namespace Claroline\CoreBundle\Manager;

use JMS\DiExtraBundle\Annotation as DI;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace;
use Claroline\CoreBundle\Writer\RoleWriter;

/**
 * @DI\Service("claroline.manager.role_manager")
 */
class RoleManager
{
    private $writer;

    /**
     * Constructor.
     *
     * @DI\InjectParams({
     *     "writer" = @DI\Inject("claroline.writer.role_writer")
     * })
     */
    public function __construct(RoleWriter $writer)
    {
        $this->writer = $writer;
    }

    public function initWorkspaceBaseRole(array $roles, AbstractWorkspace $workspace)
    {
        foreach ($roles as $name => $translation) {
            $role = $this->writer->create("{$name}_{$workspace->getId()}", $translation, Role::WS_ROLE, $workspace);
            $entityRoles[$name] = $role;
        }

        return $entityRoles;
    }
}