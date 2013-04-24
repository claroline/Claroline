<?php

namespace Claroline\CoreBundle\Repository;

use Claroline\CoreBundle\Entity\User;
use Doctrine\ORM\EntityRepository;

class WorkspaceTagRepository extends EntityRepository
{
    public function findNonEmptyTagsByUser(User $user)
    {
        $dql = "
            SELECT DISTINCT t
            FROM Claroline\CoreBundle\Entity\Workspace\RelWorkspaceTag rwt
            INNER JOIN Claroline\CoreBundle\Entity\Workspace\WorkspaceTag t WITH t = rwt.tag
            WHERE t.user = :user
        ";
        $query = $this->_em->createQuery($dql);
        $query->setParameter('user', $user);

        return $query->getResult();
    }

    public function findNonEmptyAdminTags()
    {
        $dql = "
            SELECT DISTINCT t
            FROM Claroline\CoreBundle\Entity\Workspace\RelWorkspaceTag rwt
            INNER JOIN Claroline\CoreBundle\Entity\Workspace\WorkspaceTag t WITH t = rwt.tag
            WHERE t.user IS NULL
        ";
        $query = $this->_em->createQuery($dql);

        return $query->getResult();
    }
}