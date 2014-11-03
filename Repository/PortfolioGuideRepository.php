<?php

namespace Icap\PortfolioBundle\Repository;

use Claroline\CoreBundle\Entity\User;
use Doctrine\ORM\EntityRepository;
use Icap\PortfolioBundle\Entity\Portfolio;

class PortfolioGuideRepository extends EntityRepository
{
    /**
     * @param Portfolio $portfolio
     * @param User      $user
     * @param bool      $executeQuery
     *
     * @return array|\Doctrine\ORM\Query
     */
    public function findByPortfolioAndUser(Portfolio $portfolio, User $user, $executeQuery = true)
    {
        $queryBuilder = $this->createQueryBuilder('pg')
            ->andWhere('pg.portfolio = :portfolio')
            ->andWhere('pg.user = :user')
            ->setParameter('portfolio', $portfolio)
            ->setParameter('user', $user)
        ;

        return $executeQuery ? $queryBuilder->getQuery()->getOneOrNullResult(): $queryBuilder->getQuery();
    }
}