<?php

namespace HeVinci\CompetencyBundle\Repository;

use Doctrine\ORM\EntityRepository;
use HeVinci\CompetencyBundle\Entity\Competency;

class LevelRepository extends EntityRepository
{
    /**
     * Returns the builder needed to find the levels associated
     * to a competency (or its ancestor).
     *
     * Note: this method is used in the ability form type.
     *
     * @param Competency $competency
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getFindByCompetencyBuilder(Competency $competency)
    {
        return $this->createQueryBuilder('l')
            ->join('l.scale', 's')
            ->join('s.competencies', 'c')
            ->where('c.root = :compRoot')
            ->setParameter(':compRoot', $competency->getRoot());
    }
}
