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
     * Returns the visible tools list for an array of role for a workspace.
     *
     * @param array $roles
     * @param \Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace $workspace
     *
     * @return array
     *
     * @throws \RuntimeException
     */
    public function findDisplayedByRolesAndWorkspace(array $roles, AbstractWorkspace $workspace)
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
        }
        else {
            $dql = "
                SELECT tool
                FROM Claroline\CoreBundle\Entity\Tool\Tool tool
                WHERE tool.isDisplayableInWorkspace = true
            ";
        }
        $query = $this->_em->createQuery($dql);

        return $query->getResult();
    }

    /**
     * Returns the visible tools in a user's desktop.
     *
     * @param \Claroline\CoreBundle\Entity\User $user
     *
     * @return array
     */
    public function findDesktopDisplayedToolsByUser(User $user)
    {
        $dql = "
            SELECT tool FROM Claroline\CoreBundle\Entity\Tool\Tool tool
            JOIN tool.desktopTools desktopTool
            JOIN desktopTool.user user
            WHERE user.id = {$user->getId()}
            ORDER BY desktopTool.order
        ";
        $query = $this->_em->createQuery($dql);

        return $query->getResult();
    }

    /**
     * Returns the non-visible tools in a user's desktop.
     *
     * @param \Claroline\CoreBundle\Entity\User $user
     *
     * @return array
     */
    public function findDesktopUndisplayedToolsByUser(User $user)
    {
        $dql = "
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
        $query = $this->_em->createQuery($dql);

        return $query->getResult();
    }

    public function findUndisplayedToolsByWorkspace($workspace)
    {
        $dql = "
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
        $query = $this->_em->createQuery($dql);

        return $query->getResult();
    }

    public function findDisplayedToolsByWorkspace($workspace)
    {
        $dql = "
            SELECT tool
            FROM Claroline\CoreBundle\Entity\Tool\Tool tool
            JOIN tool.workspaceOrderedTools wot
            JOIN wot.workspace ws
            JOIN wot.workspaceToolRoles wtr
            WHERE ws.id = {$workspace->getId()}
        ";
        $query = $this->_em->createQuery($dql);

        return $query->getResult();
    }
}