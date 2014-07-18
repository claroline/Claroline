<?php

namespace Icap\PortfolioBundle\Repository\Widget;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;
use Icap\PortfolioBundle\Entity\Portfolio;

class AbstractWidgetRepository extends EntityRepository
{
    /**
     * @param string                                                                              $type
     * @param \Icap\PortfolioBundle\Entity\Portfolio|\Icap\PortfolioBundle\Repository\Widget\User $portfolio
     * @param bool                                                                                $executeQuery
     *
     * @return \Icap\PortfolioBundle\Entity\Widget\AbstractWidget|\Doctrine\ORM\Query
     */
    public function findOneByTypeAndPortfolio($type, Portfolio $portfolio, $executeQuery = true)
    {
        $query = $this->createQueryBuilder('w')
            ->select('w')
            ->andWhere('w.portfolio = :portfolio')
            ->andWhere('w INSTANCE OF ' . sprintf("IcapPortfolioBundle:Widget\%sWidget", ucfirst($type)))
            ->setParameter('portfolio', $portfolio)
        ;

        return $executeQuery ? $query->getQuery()->getOneOrNullResult(): $query->getQuery();
    }

    /**
     * @param Portfolio $portfolio
     * @param integer   $column
     * @param bool      $executeQuery
     *
     * @return Query|integer
     */
    public function findMaxRow(Portfolio $portfolio, $column, $executeQuery = true)
    {
        $query = $this->createQueryBuilder('w')
            ->select('MAX(w.row) as maxRow')
            ->andWhere('w.portfolio = :portfolio')
            ->andWhere('w.column = :column')
            ->setParameter('portfolio', $portfolio)
            ->setParameter('column', $column)
        ;

        return $executeQuery ? $query->getQuery()->getOneOrNullResult(): $query->getQuery();
    }
}