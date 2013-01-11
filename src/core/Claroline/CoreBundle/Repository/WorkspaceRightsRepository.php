<?php

namespace Claroline\CoreBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace;
use Claroline\CoreBundle\Entity\User;

class WorkspaceRightsRepository extends EntityRepository
{
    /**
     * Gets the workspace rights of a user !
     *
     * @param \Claroline\CoreBundle\Entity\User $user
     * @param \Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace $workspace
     *
     * @return type
     */
    public function getRights(User $user, AbstractWorkspace $workspace)
    {

        $dql = "
            SELECT
                MAX(wsr.canView) as canView,
                MAX(wsr.canEdit) as canEdit,
                MAX(wsr.canManage) as canManage,
                MAX(wsr.canDelete) as canDelete
            FROM Claroline\CoreBundle\Entity\Workspace\WorkspaceRights wsr

            JOIN wsr.role role
            JOIN wsr.workspace workspace
            LEFT JOIN role.users user

            WHERE workspace.id = {$workspace->getId()}
            AND user.id = {$user->getId()}
            ORDER BY wsr.id";

       $query = $this->_em->createQuery($dql);

       return $query->getSingleResult();
    }

    public function getAnonymousRights(AbstractWorkspace $workspace)
    {
        $dql = "
            SELECT
                MAX(wsr.canView) as canView,
                MAX(wsr.canEdit) as canEdit,
                MAX(wsr.canManage) as canManage,
                MAX(wsr.canDelete) as canDelete
            FROM Claroline\CoreBundle\Entity\Workspace\WorkspaceRights wsr
            JOIN wsr.role role
            JOIN wsr.workspace workspace
            WHERE workspace.id = {$workspace->getId()}";

       $query = $this->_em->createQuery($dql);

       return $query->getSingleResult();
    }
}