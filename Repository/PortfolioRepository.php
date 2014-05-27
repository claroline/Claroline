<?php

namespace Icap\PortfolioBundle\Repository;

use Claroline\CoreBundle\Entity\User;
use Doctrine\ORM\EntityRepository;

class PortfolioRepository extends EntityRepository
{
    /**
     * @param User $user
     * @param bool $executeQuery
     *
     * @return array|\Doctrine\ORM\Query
     */
    public function findByUser(User $user, $executeQuery = true)
    {
        $query = $this->createQueryBuilder('p')
            ->andWhere('p.user = :userId')
            ->setParameter('userId', $user->getId())
        ;

        return $executeQuery ? $query->getQuery()->getResult(): $query->getQuery();
    }

    /**
     * @param User $user
     * @param bool $executeQuery
     *
     * @return array|\Doctrine\ORM\Query
     */
    public function findByUserWithWidgets(User $user, $executeQuery = true)
    {
        $query = $this->createQueryBuilder('p')
            ->select('p', 'widgets')
            ->join('p.widgets','widgets')
            ->andWhere('p.user = :userId')
            ->andWhere('widgets INSTANCE OF IcapPortfolioBundle:Widget\TitleWidget')
            ->setParameter('userId', $user->getId())
        ;

        return $executeQuery ? $query->getQuery()->getResult(): $query->getQuery();
    }
}