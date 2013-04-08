<?php

namespace Claroline\CoreBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Tool\Tool;
use Claroline\CoreBundle\Entity\Role;

class ToolRepository extends EntityRepository
{
    /**
     * Returns the tools list for an array of role for a workspace.
     *
     * @param array $roles
     * @param \Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace $workspace
     * @param boolean $isVisible is the tool visible
     *
     * @return array
     *
     * @throws \RuntimeException
     */
    public function findByRolesAndWorkspace (array $roles, AbstractWorkspace $workspace, $isVisible)
    {
        $dql = $isVisible ?
            $this->getDisplayedToolsForRolesInWorkspaceQuery($roles, $workspace):
            $this->getUndisplayedToolsForRolesInWorkspaceQuery($roles, $workspace);

        $query = $this->_em->createQuery($dql);

        return $query->getResult();
    }

    private function getDisplayedToolsForRolesInWorkspaceQuery(array $roles, AbstractWorkspace $workspace)
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
                JOIN tool.workspaceOrderedTools wot
                JOIN wot.workspaceToolRoles wtr
                JOIN wot.workspace ws
                JOIN wtr.role role
                WHERE role.name = '{$firstRole}' and ws.id = {$workspace->getId()}";

            foreach ($roles as $role) {
                $dql .= " OR role.name = '{$role}' and ws.id = {$workspace->getId()}";
            }

            $dql .= " ORDER BY wot.order";

            return $dql;
        }

        return "SELECT tool FROM Claroline\CoreBundle\Entity\Tool\Tool tool
            WHERE tool.isDisplayableInWorkspace = true";
    }

    private function getUndisplayedToolsForRolesInWorkspaceQuery(array $roles, AbstractWorkspace $workspace)
    {
        if (null === $firstRole = array_shift($roles)) {
            throw new \RuntimeException('The roles array cannot be empty');
        }

        $dql = "SELECT tool FROM Claroline\CoreBundle\Entity\Tool\Tool tool
            LEFT JOIN tool.workspaceToolRoles wtr
            WHERE tool NOT IN (SELECT tool2 FROM Claroline\CoreBundle\Entity\Tool\Tool tool2
            JOIN tool2.workspaceToolRoles wtr2
            JOIN wtr2.workspace ws2
            JOIN wtr2.role role2
            WHERE role2.name = '{$firstRole}' and ws2.id = {$workspace->getId()}
            and tool2.isDisplayableInWorkspace = TRUE";

        foreach ($roles as $role) {
            $dql .= " OR role2.name = '{$role}' and ws2.id = {$workspace->getId()}
            and tool2.isDisplayableInWorkspace = TRUE";
        }

        $dql .= " ) ORDER BY wtr.order";

        return $dql;
    }

    /**
     * Returns the tools in a user's desktop.
     *
     * @param \Claroline\CoreBundle\Entity\User $user
     * @param boolean $isVisible is the user visible
     *
     * @return array
     */
    public function findByUser(User $user, $isVisible)
    {
        if ($isVisible) {
            $dql = $this->getDesktopDisplayedToolsQuery($user);
        } else {
            $dql = $this->getDesktopUndisplayedToolsQuery($user);
        }

        $query = $this->_em->createQuery($dql);

        return $query->getResult();
    }

    private function getDesktopDisplayedToolsQuery(User $user)
    {
        return "
            SELECT tool FROM Claroline\CoreBundle\Entity\Tool\Tool tool
            JOIN tool.desktopTools desktopTool
            JOIN desktopTool.user user
            WHERE user.id = {$user->getId()}
            ORDER BY desktopTool.order
        ";
    }

    private function getDesktopUndisplayedToolsQuery(User $user)
    {
        return "
            SELECT tool
            FROM Claroline\CoreBundle\Entity\Tool\Tool tool
            WHERE tool NOT IN (
                SELECT tool_2
                FROM Claroline\CoreBundle\Entity\Tool\Tool tool_2
                JOIN tool_2.desktopTools desktopTool_2
                JOIN desktopTool_2.user user_2
                WHERE user_2.id = {$user->getId()}
            )
            AND tool.isDisplayableInDesktop = true
        ";
    }

    public function findByWorkspace(AbstractWorkspace $workspace, $isVisible)
    {
        $dql = $isVisible ?
            $this->getWorkspaceDisplayedToolsQuery($workspace):
            $this->getWorkspaceUndisplayedToolsQuery($workspace);

        $query = $this->_em->createQuery($dql);

        return $query->getResult();
    }

    private function getWorkspaceUndisplayedToolsQuery($workspace)
    {
        return "
            SELECT tool
            FROM Claroline\CoreBundle\Entity\Tool\Tool tool
            WHERE tool NOT IN (
                SELECT tool_2 FROM Claroline\CoreBundle\Entity\Tool\Tool tool_2
                JOIN tool_2.workspaceOrderedTools wot
                JOIN wot.workspace ws
                WHERE ws.id = {$workspace->getId()}
                AND tool.isDisplayableInWorkspace = true
            )
        ";
    }

    private function getWorkspaceDisplayedToolsQuery($workspace)
    {
        return "
            SELECT tool
            FROM Claroline\CoreBundle\Entity\Tool\Tool tool
            JOIN tool.workspaceOrderedTools wot
            JOIN wot.workspace ws
            JOIN wot.workspaceToolRoles wtr
            WHERE ws.id = {$workspace->getId()}
        ";
    }

    public function isToolVisibleForRoleInWorkspace(Tool $tool, Role $role, AbstractWorkspace $workspace)
    {
        $dql = "
            SELECT tool
            FROM Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace tool
            JOIN tool.workspaceOrderedTools wot
            JOIN wot.tool tool_2
            JOIN wot.workspace ws
            JOIN wot.workspaceToolRoles wtr
            JOIN wtr.role r
            WHERE tool_2.id = {$tool->getId()}
            AND r.id = {$role->getId()}
            AND ws.id = {$workspace->getId()}
        ";

        $query = $this->_em->createQuery($dql);

        return $query->getResult() !== null;
    }
}