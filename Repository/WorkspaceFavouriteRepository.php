<?php

namespace Claroline\CoreBundle\Repository;

use Claroline\CoreBundle\Entity\User;
use Doctrine\ORM\EntityRepository;

class WorkspaceFavouriteRepository extends EntityRepository
{
    public function findFavouriteWorkspacesByUser(User $user)
    {
        $dql = "
            SELECT f
            FROM Claroline\CoreBundle\Entity\Workspace\WorkspaceFavourite f
            WHERE f.user = :user
        ";

        $query = $this->_em->createQuery($dql);
        $query->setParameter('user', $user);

        return $query->getResult();
    }
}
