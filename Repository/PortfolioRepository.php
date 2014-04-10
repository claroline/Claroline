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
        $query = $this->createQueryBuilder('portfolio')
            ->andWhere('portfolio.user = :userId')
            ->setParameter('userId', $user->getId())
        ;

        return $executeQuery ? $query->getQuery()->getResult(): $query->getQuery();
    }
}