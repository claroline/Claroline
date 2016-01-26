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
        $queryBuilder = $this->createQueryBuilder('w')
            ->select('w')
            ->andWhere('w.portfolio = :portfolio')
            ->andWhere($this->getWigetInstanceString($type))
            ->setParameter('portfolio', $portfolio)
        ;

        return $executeQuery ? $queryBuilder->getQuery()->getOneOrNullResult(): $queryBuilder->getQuery();
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
        $queryBuilder = $this->createQueryBuilder('w')
            ->select('MAX(w.row) as maxRow')
            ->andWhere('w.portfolio = :portfolio')
            ->andWhere('w.col = :col')
            ->setParameter('portfolio', $portfolio)
            ->setParameter('col', $col)
        ;

        return $executeQuery ? $queryBuilder->getQuery()->getOneOrNullResult(): $queryBuilder->getQuery();
    }

    /**
     * @param string $type
     * @param bool   $executeQuery
     *
     * @return \Icap\PortfolioBundle\Entity\Widget\AbstractWidget|\Doctrine\ORM\Query
     */
    public function findByType($type, $executeQuery = true)
    {
        $queryBuilder = $this->createQueryBuilder('w')
            ->andWhere($this->getWigetInstanceString($type))
        ;

        return $executeQuery ? $queryBuilder->getQuery()->getResult(): $queryBuilder->getQuery();
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
        $queryBuilder = $this->createQueryBuilder('w')
            ->andWhere($this->getWigetInstanceString($type))
            ->andWhere('w.id = :id')
            ->andWhere('w.user = :user')
            ->setParameters([
                'id' => $id,
                'user' => $user
            ])
        ;

        return $executeQuery ? $queryBuilder->getQuery()->getSingleResult(): $queryBuilder->getQuery();
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
        $queryBuilder = $this->createQueryBuilder('w')
            ->andWhere($this->getWigetInstanceString($type))
            ->andWhere('w.user = :user')
            ->setParameter('user', $user)
        ;

        return $executeQuery ? $queryBuilder->getQuery()->getResult(): $queryBuilder->getQuery();
    }

    private function getWigetInstanceString($widgetType)
    {
        $instanceOf = 'w INSTANCE OF ';
        if (strtoupper($widgetType) == 'BADGES') {
            $instanceOf .= 'IcapBadgeBundle:Portfolio\BadgesWidget';
        } else {
            $instanceOf .= sprintf("IcapPortfolioBundle:Widget\%sWidget", ucfirst($widgetType));
        }

        return $instanceOf;
    }
}