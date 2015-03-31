<?php

namespace HeVinci\CompetencyBundle\Repository;

use Doctrine\ORM\EntityRepository;

class ObjectiveRepository extends EntityRepository
{
    public function findWithObjectives()
    {
        return $this->createQueryBuilder('o', 'c', 'l')
            ->join('o.objectiveCompetencies', 'oc')
            ->join('oc.competency', 'c')
            ->join('oc.level', 'l')
            ->getQuery()
            ->getResult();
    }
}
