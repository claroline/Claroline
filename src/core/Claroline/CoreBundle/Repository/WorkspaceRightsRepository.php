<?php

namespace Claroline\CoreBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace;

class WorkspaceRightsRepository extends EntityRepository
{
    /**
     * Returns the sum of workspace rights granted to a collection of roles.
     *
     * @param array[string] $roles An array of role names
     * @param \Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace $workspace The workspace to be tested
     *
     * @return array
     */
    public function getRights(array $roles, AbstractWorkspace $workspace)
    {
        if (null === $firstRole = array_shift($roles)) {
            throw new \RuntimeException('The roles array cannot be empty');
        }

        $dql = "
            SELECT
                MAX(wsr.canView) as canView,
                MAX(wsr.canEdit) as canEdit,
                MAX(wsr.canManage) as canManage,
                MAX(wsr.canDelete) as canDelete
            FROM Claroline\CoreBundle\Entity\Rights\WorkspaceRights wsr
            JOIN wsr.role role
            JOIN wsr.workspace workspace
            LEFT JOIN role.users user
            WHERE workspace.id = {$workspace->getId()} AND role.name = '{$firstRole}'
        ";

        foreach ($roles as $role) {
            $dql.= " OR workspace.id = {$workspace->getId()} AND role.name = '{$role}'";
        }

        return $this->_em
            ->createQuery($dql)
            ->getSingleResult();
    }
}