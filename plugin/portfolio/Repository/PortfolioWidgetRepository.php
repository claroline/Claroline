<?php

namespace Icap\PortfolioBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Icap\PortfolioBundle\Entity\Portfolio;

class PortfolioWidgetRepository extends EntityRepository
{
    /**
     * @param Portfolio $portfolio
     * @param bool      $executeQuery
     *
     * @return array|\Doctrine\ORM\Query
     */
    public function findOrderedByRowAndCol(Portfolio $portfolio, $executeQuery = true)
    {
        $queryBuilder = $this->createQueryBuilder('pw')
            ->andWhere('pw.portfolio = :portfolio')
            ->setParameter('portfolio', $portfolio)
            ->orderBy('pw.row', 'ASC')
            ->addOrderBy('pw.col', 'ASC')
        ;

        return $executeQuery ? $queryBuilder->getQuery()->getResult() : $queryBuilder->getQuery();
    }
}
