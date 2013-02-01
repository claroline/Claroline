<?php

namespace Claroline\CoreBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace;
use Claroline\CoreBundle\Entity\Tool\Tool;

class ToolRepository extends EntityRepository
{
    public function getToolsForWorkspace(AbstractWorkspace $workspace)
    {
        $dql = "SELECT DISTINCT tool FROM Claroline\CoreBundle\Entity\Tool\Tool tool
            JOIN tool.workspaceTools workspaceTool
            JOIN workspaceTool.workspace workspace
            WHERE workspace.id = {$workspace->getId()}";

        $query = $this->_em->createQuery($dql);

        return $query->getResult();
    }

    public function getToolsForRolesInWorkspace (array $roles, AbstractWorkspace $workspace)
    {

        if (null === $firstRole = array_shift($roles)) {
            throw new \RuntimeException('The roles array cannot be empty');
        }

        $dql = "SELECT tool FROM Claroline\CoreBundle\Entity\Tool\Tool tool
            JOIN tool.workspaceTools wsTool
            JOIN wsTool.workspaceToolRoles wsToolRole
            JOIN wsTool.workspace ws
            JOIN wsToolRole.role role
            WHERE role.name = '{$firstRole}' and ws.id = {$workspace->getId()}";

        foreach ($roles as $role) {
            $dql .= " OR role.name = '{$role}' and ws.id = {$workspace->getId()}";
        }

        $query = $this->_em->createQuery($dql);

        return $query->getResult();
    }

    public function getDesktopTools()
    {
        $dql = "
            SELECT tool FROM Claroline\CoreBundle\Entity\Tool\Tool tool
            WHERE tool.displayability = " . Tool::DESKTOP_ONLY
        ;
    }
}