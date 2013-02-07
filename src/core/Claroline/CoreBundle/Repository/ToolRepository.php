<?php

namespace Claroline\CoreBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace;
use Claroline\CoreBundle\Entity\User;

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

    /**
     * Returns the tools list for an array of role for a workspace.
     *
     * @todo removing the array of role and do it for a single role instead ?
     * @param array $roles
     * @param \Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace $workspace
     *
     * @return array
     *
     * @throws \RuntimeException
     */
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
        }

        $dql = "SELECT tool FROM Claroline\CoreBundle\Entity\Tool\Tool tool";
        $query = $this->_em->createQuery($dql);

        return $query->getResult();
    }

    public function getUndisplayedToolsForRolesInWorkspace(array $roles, AbstractWorkspace $workspace)
    {
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
            and tool2.isDisplayableInWorkspace = TRUE";

        foreach ($roles as $role) {
            $dql .= " OR role2.name = '{$role}' and ws2.id = {$workspace->getId()}
            and tool2.isDisplayableInWorkspace = TRUE";
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
        $dql = "
            SELECT tool FROM Claroline\CoreBundle\Entity\Tool\Tool tool
            WHERE tool NOT IN ( SELECT tool_2 FROM Claroline\CoreBundle\Entity\Tool\Tool tool_2
                JOIN tool_2.desktopTools desktopTool_2
                JOIN desktopTool_2.user user_2
                WHERE user_2.id = {$user->getId()} ) AND tool.isDisplayableInDesktop = true
        ";

        $query = $this->_em->createQuery($dql);

        return $query->getResult();
    }
}