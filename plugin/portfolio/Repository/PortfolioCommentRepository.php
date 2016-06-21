<?php

namespace Icap\PortfolioBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Icap\PortfolioBundle\Entity\Portfolio;

class PortfolioCommentRepository extends EntityRepository
{
    /**
     * @param Portfolio $portfolio
     * @param int       $nbResult
     * @param bool      $executeQuery
     *
     * @return array|\Doctrine\ORM\Query
     */
    public function findSome(Portfolio $portfolio, $nbResult = 10, $executeQuery = true)
    {
        $queryBuilder = $this->createQueryBuilder('pc')
            ->andWhere('pc.portfolio = :portfolio')
            ->setParameter('portfolio', $portfolio)
            ->setMaxResults($nbResult)
        ;

        return $executeQuery ? $queryBuilder->getQuery()->getResult() : $queryBuilder->getQuery();
    }
}
