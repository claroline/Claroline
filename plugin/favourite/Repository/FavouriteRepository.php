<?php

namespace HeVinci\FavouriteBundle\Repository;

use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Doctrine\ORM\EntityRepository;

class FavouriteRepository extends EntityRepository
{
    public function findFavouritesByUserAndWorkspace(User $user, Workspace $workspace)
    {
        $dql = '
            SELECT f
            FROM HeVinci\FavouriteBundle\Entity\Favourite f
            JOIN f.user u
            JOIN f.resourceNode r
            JOIN r.workspace w
            WHERE u = :user
            AND w = :workspace
        ';
        $query = $this->_em->createQuery($dql);
        $query->setParameter('user', $user);
        $query->setParameter('workspace', $workspace);

        return $query->getResult();
    }
}
