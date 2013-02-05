<?php

namespace Claroline\CoreBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace;
use Claroline\CoreBundle\Entity\User;
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

    public function getDesktopTools(User $user)
    {
        $dql = "
            SELECT tool FROM Claroline\CoreBundle\Entity\Tool\Tool tool
            JOIN tool.desktopTools desktopTool
            JOIN desktopTool.user user
            WHERE user.id = {$user->getId()}
            ORDER BY desktopTool.order";
        ;

        $query = $this->_em->createQuery($dql);

        return $query->getResult();
    }

    public function getDesktopUndisplayedTools(User $user)
    {
        $subSelect = "( SELECT tool_2 FROM Claroline\CoreBundle\Entity\Tool\Tool tool_2
                JOIN tool_2.desktopTools desktopTool_2
                JOIN desktopTool_2.user user_2
                WHERE user_2.id = {$user->getId()})";
        $dsOnly = Tool::DESKTOP_ONLY;
        $dsAndWs = Tool::WORKSPACE_AND_DESKTOP;

        $dql = "
            SELECT tool FROM Claroline\CoreBundle\Entity\Tool\Tool tool
            WHERE tool NOT IN ( SELECT tool_2 FROM Claroline\CoreBundle\Entity\Tool\Tool tool_2
                JOIN tool_2.desktopTools desktopTool_2
                JOIN desktopTool_2.user user_2
                WHERE user_2.id = {$user->getId()}) AND tool.displayability = {$dsOnly}
            OR tool NOT IN ( SELECT tool_3 FROM Claroline\CoreBundle\Entity\Tool\Tool tool_3
                JOIN tool_3.desktopTools desktopTool_3
                JOIN desktopTool_3.user user_3
                WHERE user_3.id = {$user->getId()}) AND tool.displayability = {$dsAndWs}
        ";

        $query = $this->_em->createQuery($dql);

        return $query->getResult();
    }

    public function getDesktopDisplayableTools()
    {
        $dsOnly = Tool::DESKTOP_ONLY;
        $dsAndWs = Tool::WORKSPACE_AND_DESKTOP;

        $dql = "
            SELECT tool FROM Claroline\CoreBundle\Entity\Tool\Tool tool
            WHERE tool.displayability = {$dsOnly} OR tool.displayability = {$dsAndWs}";

        $query = $this->_em->createQuery($dql);

        return $query->getResult();
    }


}