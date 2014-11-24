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
        $queryBuilder = $this->createQueryBuilder('p')
            ->andWhere('p.user = :userId')
            ->setParameter('userId', $user->getId())
        ;

        return $executeQuery ? $queryBuilder->getQuery()->getResult(): $queryBuilder->getQuery();
    }

    /**
     * @param User $user
     * @param bool $executeQuery
     *
     * @return array|\Doctrine\ORM\Query
     */
    public function findByUserWithWidgets(User $user, $executeQuery = true)
    {
        $queryBuilder = $this->createQueryBuilder('p')
            ->select('p', 'widgets')
            ->join('p.widgets','widgets')
            ->andWhere('p.user = :userId')
            ->setParameter('userId', $user->getId())
        ;

        return $executeQuery ? $queryBuilder->getQuery()->getResult(): $queryBuilder->getQuery();
    }

    /**
     * @param User $user
     * @param bool $executeQuery
     *
     * @return array|\Doctrine\ORM\Query
     */
    public function findGuidedPortfolios(User $user, $executeQuery = true)
    {
        $queryBuilder = $this->createQueryBuilder('p')
            ->select('p', 'widgets')
            ->join('p.widgets','widgets')
            ->join('p.portfolioGuides', 'guides')
            ->andWhere('guides.user = :guide')
            ->andWhere('widgets INSTANCE OF IcapPortfolioBundle:Widget\TitleWidget')
            ->setParameter('guide', $user)
        ;

        return $executeQuery ? $queryBuilder->getQuery()->getResult(): $queryBuilder->getQuery();
    }

    /**
     * @param User $user
     * @param bool $executeQuery
     *
     * @return array|\Doctrine\ORM\Query
     */
    public function findAvailableToGuideByUser(User $user, $executeQuery = true)
    {
        $queryBuilder = $this->createQueryBuilder('p')
            ->select('p', 'widgets')
            ->join('p.widgets','widgets')
            ->leftJoin('p.portfolioGuides', 'guides')
            ->andWhere('p.user = :user')
            ->orWhere('guides.user = :guide')
            ->andWhere('widgets INSTANCE OF IcapPortfolioBundle:Widget\TitleWidget')
            ->setParameter('user', $user)
            ->setParameter('guide', $user)
        ;

        return $executeQuery ? $queryBuilder->getQuery()->getResult(): $queryBuilder->getQuery();
    }
}