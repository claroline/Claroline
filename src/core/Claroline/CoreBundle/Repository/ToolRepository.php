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
            JOIN tool.workspaceToolRoles wtr
            JOIN wtr.workspace workspace
            WHERE workspace.id = {$workspace->getId()}";

        $query = $this->_em->createQuery($dql);

        return $query->getResult();
    }

    public function getToolsForRolesInWorkspace (array $roles, AbstractWorkspace $workspace)
    {
        $isAdmin = false;

        foreach ($roles as $role) {
            if ($role === 'ROLE_ADMIN') {
                $isAdmin = true;
            }
        }

        if (!$isAdmin) {
            if (null === $firstRole = array_shift($roles)) {
                throw new \RuntimeException('The roles array cannot be empty');
            }

            $dql = "SELECT tool FROM Claroline\CoreBundle\Entity\Tool\Tool tool
                JOIN tool.workspaceToolRoles wtr
                JOIN wtr.workspace ws
                JOIN wtr.role role
                WHERE role.name = '{$firstRole}' and ws.id = {$workspace->getId()}";

            foreach ($roles as $role) {
                $dql .= " OR role.name = '{$role}' and ws.id = {$workspace->getId()}";
            }

            $dql .= "ORDER BY wtr.order";
            $query = $this->_em->createQuery($dql);

            return $query->getResult();
        } else {

            $dql = "SELECT tool FROM Claroline\CoreBundle\Entity\Tool\Tool tool";
            $query = $this->_em->createQuery($dql);

            return $query->getResult();
        }
    }

    public function getUndisplayedToolsForRolesInWorkspace(array $roles, AbstractWorkspace $workspace)
    {
        $wsOnly = Tool::WORKSPACE_ONLY;
        $dsAndWs = Tool::WORKSPACE_AND_DESKTOP;

        if (null === $firstRole = array_shift($roles)) {
            throw new \RuntimeException('The roles array cannot be empty');
        }

        $dql = "SELECT tool FROM Claroline\CoreBundle\Entity\Tool\Tool tool
            JOIN tool.workspaceToolRoles wtr
            WHERE tool NOT IN (SELECT tool2 FROM Claroline\CoreBundle\Entity\Tool\Tool tool2
            JOIN tool2.workspaceToolRoles wtr2
            JOIN wtr2.workspace ws2
            JOIN wtr2.role role2
            WHERE role2.name = '{$firstRole}' and ws2.id = {$workspace->getId()}
            and tool2.displayability = $wsOnly";

        foreach ($roles as $role) {
            $dql .= " OR role2.name = '{$role}' and ws2.id = {$workspace->getId()}
            and tool2.displayability = $wsOnly";
        }

        $dql .= " )
        AND tool NOT IN (SELECT tool3 FROM Claroline\CoreBundle\Entity\Tool\Tool tool3
        JOIN tool3.workspaceToolRoles wtr3
        JOIN wtr3.workspace ws3
        JOIN wtr3.role role3
        WHERE role3.name = '{$firstRole}'
        and ws3.id = {$workspace->getId()} and tool3.displayability = $dsAndWs";
        foreach ($roles as $role) {
            $dql .= " OR role3.name = '{$role}'
            and ws3.id = {$workspace->getId()} and tool3.displayability = $dsAndWs";
        }

        $dql .= " )

        ORDER BY wtr.order";

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