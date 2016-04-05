<?php

namespace Icap\PortfolioBundle\Repository;

use Claroline\CoreBundle\Entity\User;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;

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

    /**
     * @param bool $executeQuery
     *
     * @return int|QueryBuilder
     */
    public function countAll($executeQuery = true)
    {
        $queryBuilder = $this->createQueryBuilder('p')
            ->select('COUNT(p)');


        return $executeQuery ? $queryBuilder->getQuery()->getSingleScalarResult() : $queryBuilder;
    }

    /**
     * @return int
     */
    public function countAllDeleted()
    {
        $queryBuilder = $this->countAll(false);
        return $queryBuilder
            ->where('p.deletedAt IS NOT NULL')
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * @return array
     */
    public function countAllByVisibilityStatus()
    {
        return $this->createQueryBuilder('p')
            ->select('COUNT(p) as number, p.visibility')
            ->groupBy('p.visibility')
            ->getQuery()
            ->getArrayResult()
        ;
    }
}