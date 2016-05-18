<?php

namespace Icap\BadgeBundle\Repository;

use Claroline\CoreBundle\Entity\User;
use Doctrine\ORM\EntityRepository;

class PortfolioWidgetRepository extends EntityRepository
{
    /**
     * @param string $type
     * @param int    $id
     * @param User   $user
     * @param bool   $executeQuery
     *
     * @return \Doctrine\ORM\QueryBuilder|mixed
     *
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findOneByWidgetType($type, $id, User $user, $executeQuery = true)
    {
        $query = $this->createQueryBuilder('w')
            ->andWhere('w INSTANCE OF IcapBadgeBundle:Portfolio\BadgesWidget')
            ->andWhere('w.id = :id')
            ->andWhere('w.user = :user')
            ->setParameters([
                'id' => $id,
                'user' => $user,
            ])
        ;

        return $executeQuery ? $query->getQuery()->getSingleResult() : $query->getQuery();
    }
}
