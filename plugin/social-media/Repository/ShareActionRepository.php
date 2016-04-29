<?php
/**
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * Author: Panagiotis TSAVDARIS
 * 
 * Date: 4/30/15
 */

namespace Icap\SocialmediaBundle\Repository;

use Doctrine\ORM\EntityRepository;

class ShareActionRepository extends EntityRepository
{
    public function countShares(array $criteria)
    {
        $qb = $this->createQueryBuilder('shareAction');
        $qb->select('COUNT(shareAction.id)');
        foreach ($criteria as $key => $value) {
            $qb->andWhere('shareAction.'.$key.' = :'.$key);
            $qb->setParameter($key, $value);
        }

        return $qb->getQuery()->getSingleScalarResult();
    }
}
