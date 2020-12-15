<?php
/**
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * Author: Panagiotis TSAVDARIS
 * 
 * Date: 4/23/15
 */

namespace Icap\SocialmediaBundle\Repository;

use Claroline\CoreBundle\Entity\User;
use Doctrine\ORM\EntityRepository;

class WallItemRepository extends EntityRepository
{
    public function findItemsForPagination($userId, $isOwner)
    {
        $qb = $this->createQueryBuilder('wallItem');
        $qb->select('wallItem');
        $qb->andWhere('wallItem.user = :user');
        $qb->setParameter('user', $userId);
        if (!$isOwner) {
            $qb->andWhere('wallItem.visible != :visible');
            $qb->setParameter('visible', false);
        }
        $qb->orderBy('wallItem.creationDate', 'DESC');

        return $qb;
    }
}
