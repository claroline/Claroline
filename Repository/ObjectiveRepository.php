<?php

namespace HeVinci\CompetencyBundle\Repository;

use Doctrine\ORM\EntityRepository;

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
}
