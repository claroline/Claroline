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
    public function findByUserWithWidgetsAndComments(User $user, $executeQuery = true)
    {
        $queryBuilder = $this->createQueryBuilder('p')
            ->select('p')
            ->leftJoin('p.comments','comments')
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
            ->join('p.portfolioGuides', 'guides')
            ->andWhere('guides.user = :guide')
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
            ->leftJoin('p.portfolioGuides', 'guides')
            ->andWhere('p.user = :user')
            ->orWhere('guides.user = :guide')
            ->setParameter('user', $user)
            ->setParameter('guide', $user)
        ;

        return $executeQuery ? $queryBuilder->getQuery()->getResult(): $queryBuilder->getQuery();
    }
}