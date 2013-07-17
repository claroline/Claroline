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
     * Returns the workspace tools visible by a set of roles.
     *
     * @param array             $roles
     * @param AbstractWorkspace $workspace
     *
     * @return array[Tool]
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

            $dql = "
                SELECT tool FROM Claroline\CoreBundle\Entity\Tool\Tool tool
                JOIN tool.orderedTools ot
                JOIN ot.workspace ws
                JOIN ot.roles role
                WHERE role.name = '{$firstRole}' and ws.id = {$workspace->getId()}
            ";

            foreach ($roles as $role) {
                $dql .= " OR role.name = '{$role}' and ws.id = {$workspace->getId()}";
            }

            $dql .= ' ORDER BY ot.order';
        } else {
            $dql = '
                SELECT tool
                FROM Claroline\CoreBundle\Entity\Tool\Tool tool
                WHERE tool.isDisplayableInWorkspace = true
            ';
        }

        $query = $this->_em->createQuery($dql);

        return $query->getResult();
    }

    /**
     * Returns the visible tools in a user's desktop.
     *
     * @param User $user
     *
     * @return array[Tool]
     */
    public function findDesktopDisplayedToolsByUser(User $user)
    {
        $dql = "
            SELECT tool FROM Claroline\CoreBundle\Entity\Tool\Tool tool
            JOIN tool.orderedTools ot
            JOIN ot.user user
            WHERE user.id = {$user->getId()}
            ORDER BY ot.order
        ";
        $query = $this->_em->createQuery($dql);

        return $query->getResult();
    }

    /**
     * Returns the non-visible tools in a user's desktop.
     *
     * @param User $user
     *
     * @return array[Tool]
     */
    public function findDesktopUndisplayedToolsByUser(User $user)
    {
        $dql = "
            SELECT tool
            FROM Claroline\CoreBundle\Entity\Tool\Tool tool
            WHERE tool NOT IN (
                SELECT tool_2
                FROM Claroline\CoreBundle\Entity\Tool\Tool tool_2
                JOIN tool_2.orderedTools ot_2
                JOIN ot_2.user user_2
                WHERE user_2.id = {$user->getId()}
            )
            AND tool.isDisplayableInDesktop = true
        ";
        $query = $this->_em->createQuery($dql);

        return $query->getResult();
    }

    /**
     * Returns the non-visible tools in a workspace.
     *
     * @param AbstractWorkspace $workspace
     *
     * @return array[Tool]
     */
    public function findUndisplayedToolsByWorkspace(AbstractWorkspace $workspace)
    {
        $dql = "
            SELECT tool
            FROM Claroline\CoreBundle\Entity\Tool\Tool tool
            WHERE tool NOT IN (
                SELECT tool_2 FROM Claroline\CoreBundle\Entity\Tool\Tool tool_2
                JOIN tool_2.orderedTools ot
                JOIN ot.workspace ws
                WHERE ws.id = {$workspace->getId()}
                AND tool.isDisplayableInWorkspace = true
            )
        ";
        $query = $this->_em->createQuery($dql);

        return $query->getResult();
    }

    /**
     * Returns the visible tools in a workspace.
     *
     * @param AbstractWorkspace $workspace
     *
     * @return array[Tool]
     */
    public function findDisplayedToolsByWorkspace(AbstractWorkspace $workspace)
    {
        $dql = "
            SELECT tool
            FROM Claroline\CoreBundle\Entity\Tool\Tool tool
            JOIN tool.orderedTools ot
            JOIN ot.workspace ws
            WHERE ws.id = {$workspace->getId()}
        ";
        $query = $this->_em->createQuery($dql);

        return $query->getResult();
    }

    /**
     * Returns the number of tools visible in a workspace.
     *
     * @param AbstractWorkspace $workspace
     *
     * @return integer
     */
    public function countDisplayedToolsByWorkspace(AbstractWorkspace $workspace)
    {
        $dql = "
            SELECT count(tool)
            FROM Claroline\CoreBundle\Entity\Tool\Tool tool
            JOIN tool.orderedTools ot
            JOIN ot.workspace ws
            WHERE ws.id = {$workspace->getId()}
        ";
        $query = $this->_em->createQuery($dql);

        return $query->getSingleScalarResult();
    }
}
