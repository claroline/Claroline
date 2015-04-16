<?php

namespace HeVinci\CompetencyBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr;

class ObjectiveRepository extends EntityRepository
{
    /**
     * Returns an array representation of all the objectives, including
     * the number of competencies associated with each objective.
     *
     * @return array
     */
    public function findWithCompetencyCount()
    {
        return $this->createQueryBuilder('o')
            ->select('o.id', 'o.name', 'COUNT(oc) AS competencyCount')
            ->leftJoin('o.objectiveCompetencies', 'oc')
            ->groupBy('o.id')
            ->getQuery()
            ->getArrayResult();
    }

    /**
     * Returns the query object for counting all the users which have
     * at least one learning objective.
     *
     * @return \Doctrine\ORM\Query
     */
    public function getUsersWithObjectiveCountQuery()
    {
        return $this->_em->createQueryBuilder()
            ->select('COUNT(u.id)')
            ->from('Claroline\CoreBundle\Entity\User', 'u')
            ->where((new Expr())->in('u.id', $this->getInverseSideIdsDql('users')))
            ->getQuery();
    }

    /**
     * Returns the query object for fetching all the users which have
     * at least one learning objective.
     *
     * @return \Doctrine\ORM\Query
     */
    public function getUsersWithObjectiveQuery()
    {
        return $this->_em->createQueryBuilder()
            ->select('u.id', 'u.firstName', 'u.lastName')
            ->from('Claroline\CoreBundle\Entity\User', 'u')
            ->where((new Expr())->in('u.id', $this->getInverseSideIdsDql('users')))
            ->getQuery();
    }

    private function getInverseSideIdsDql($targetField)
    {
        return $this->createQueryBuilder('o')
            ->select('ot.id')
            ->join("o.{$targetField}", 'ot')
            ->getDQL();
    }
}
