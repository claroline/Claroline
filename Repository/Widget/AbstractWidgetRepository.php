<?php

namespace Icap\PortfolioBundle\Repository\Widget;

use Claroline\CoreBundle\Entity\User;
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
     * @param integer   $col
     * @param bool      $executeQuery
     *
     * @return Query|integer
     */
    public function findMaxRow(Portfolio $portfolio, $col, $executeQuery = true)
    {
        $query = $this->createQueryBuilder('w')
            ->select('MAX(w.row) as maxRow')
            ->andWhere('w.portfolio = :portfolio')
            ->andWhere('w.col = :col')
            ->setParameter('portfolio', $portfolio)
            ->setParameter('col', $col)
        ;

        return $executeQuery ? $query->getQuery()->getOneOrNullResult(): $query->getQuery();
    }

    /**
     * @param string $type
     * @param bool   $executeQuery
     *
     * @return \Icap\PortfolioBundle\Entity\Widget\AbstractWidget|\Doctrine\ORM\Query
     */
    public function findByType($type, $executeQuery = true)
    {
        $query = $this->createQueryBuilder('w')
            ->andWhere('w INSTANCE OF ' . sprintf("IcapPortfolioBundle:Widget\%sWidget", ucfirst($type)))
        ;

        return $executeQuery ? $query->getQuery()->getResult(): $query->getQuery();
    }

    /**
     * @param string $type
     * @param integer $id
     * @param User $user
     * @param bool $executeQuery
     *
     * @return \Doctrine\ORM\QueryBuilder|mixed
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findOneByWidgetType($type, $id, User $user, $executeQuery = true)
    {
        $query = $this->createQueryBuilder('w')
            ->andWhere('w INSTANCE OF ' . sprintf("IcapPortfolioBundle:Widget\%sWidget", ucfirst($type)))
            ->andWhere('w.id = :id')
            ->andWhere('w.user = :user')
            ->setParameters([
                'id' => $id,
                'user' => $user
            ])
        ;

        return $executeQuery ? $query->getQuery()->getSingleResult(): $query->getQuery();
    }

    /**
     * @param string $type
     * @param User   $user
     * @param bool   $executeQuery
     *
     * @return \Icap\PortfolioBundle\Entity\Widget\AbstractWidget[]|Query
     */
    public function findByWidgetTypeAndUser($type, User $user, $executeQuery = true)
    {
        $query = $this->createQueryBuilder('w')
            ->andWhere('w INSTANCE OF ' . sprintf("IcapPortfolioBundle:Widget\%sWidget", ucfirst($type)))
            ->andWhere('w.user = :user')
            ->setParameter('user', $user)
        ;

        return $executeQuery ? $query->getQuery()->getResult(): $query->getQuery();
    }
}