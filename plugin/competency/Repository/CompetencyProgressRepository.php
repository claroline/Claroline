<?php

namespace HeVinci\CompetencyBundle\Repository;

use Claroline\CoreBundle\Entity\User;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr;

class CompetencyProgressRepository extends EntityRepository
{
    /**
     * Returns every competency progress entity related to a
     * given user and whose id is in a given set.
     *
     * @param User $user
     * @param array $competencyIds
     * @return array
     */
    public function findByUserAndCompetencyIds(User $user, array $competencyIds)
    {
        if (count($competencyIds) === 0) {
            return [];
        }

        return $this->createQueryBuilder('p')
            ->select('p', 'c')
            ->join('p.competency', 'c')
            ->where('p.user = :user')
            ->andWhere((new Expr())->in('p.competency', $competencyIds))
            ->setParameter(':user', $user)
            ->getQuery()
            ->getResult();
    }
}
