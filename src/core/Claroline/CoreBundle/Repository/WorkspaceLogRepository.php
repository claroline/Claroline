<?php

namespace Claroline\CoreBundle\Repository;

use Claroline\CoreBundle\Entity\User;
use Doctrine\ORM\EntityRepository;

class WorkspaceLogRepository extends EntityRepository
{
    public function findLatestWorkspaceByUser(User $user, $size = 5)
    {
        $dql = "
            SELECT l, MAX(l.date) AS md
            FROM Claroline\CoreBundle\Entity\Workspace\WorkspaceLog l
            JOIN l.workspace w
            JOIN l.user u
            WHERE l.type = 'workspace_access'
            AND u.id = :userId
            GROUP BY w.id
            ORDER BY md DESC
        ";
        $query = $this->_em->createQuery($dql);
        $query->setMaxResults($size);
        $query->setParameter('userId', $user->getId());

        return $query->getResult();
    }
}