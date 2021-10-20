<?php
/**
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * Author: Panagiotis TSAVDARIS
 *
 * Date: 4/22/15
 */

namespace Icap\SocialmediaBundle\Repository;

use Doctrine\ORM\EntityRepository;

class LikeActionRepository extends EntityRepository
{
    public function countLikes(array $criteria)
    {
        $qb = $this->createQueryBuilder('likeAction');
        $qb->select('COUNT(likeAction.id)');
        foreach ($criteria as $key => $value) {
            $qb->andWhere('likeAction.'.$key.' = :'.$key);
            $qb->setParameter($key, $value);
        }

        return $qb->getQuery()->getSingleScalarResult();
    }
}
