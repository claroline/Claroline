<?php

namespace HeVinci\CompetencyBundle\Repository;

use Doctrine\ORM\EntityRepository;
use HeVinci\CompetencyBundle\Entity\Scale;

class ScaleRepository extends EntityRepository
{
    /**
     * Returns an array representation of registered scales, including
     * information about linked frameworks and abilities
     *
     * @return array
     */
    public function findWithStatus()
    {
        $scales = $this->createQueryBuilder('s')
            ->select('s.id', 's.name', 'COUNT(c) AS competencies')
            ->leftJoin('s.competencies', 'c')
            ->groupBy('s')
            ->getQuery()
            ->getArrayResult();

        $abilityCounts = $this->createQueryBuilder('s')
            ->select('s.id', 'COUNT (ca) AS abilities')
            ->leftJoin('s.levels', 'l')
            ->leftJoin('l.competencyAbilities', 'ca')
            ->groupBy('s')
            ->getQuery()
            ->getArrayResult();

        return array_map(function ($element) use ($abilityCounts) {
            foreach ($abilityCounts as $counts) {
                if ($counts['id'] === $element['id']) {
                    $element['abilities'] = $counts['abilities'];

                    return $element;
                }
            }
        }, $scales);
    }

    /**
     * Returns the number of competency frameworks linked to a scale.
     *
     * @param Scale $scale
     * @return integer
     */
    public function findCompetencyCount(Scale $scale)
    {
        return $this->createQueryBuilder('s')
            ->select('COUNT(c) AS competencies')
            ->leftJoin('s.competencies', 'c')
            ->where('s = :scale')
            ->groupBy('s')
            ->getQuery()
            ->setParameter(':scale', $scale)
            ->getSingleScalarResult();
    }

    /**
     * Returns the number of abilities linked to the levels of a scale.
     *
     * @param Scale $scale
     * @return integer
     */
    public function findAbilityCount(Scale $scale)
    {
        return $this->createQueryBuilder('s')
            ->select('COUNT (ca) AS abilities')
            ->leftJoin('s.levels', 'l')
            ->leftJoin('l.competencyAbilities', 'ca')
            ->where('s = :scale')
            ->groupBy('s')
            ->getQuery()
            ->setParameter(':scale', $scale)
            ->getSingleScalarResult();
    }
}
