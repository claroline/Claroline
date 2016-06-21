<?php
/**
 * Created by : Vincent SAISSET
 * Date: 05/09/13
 * Time: 14:56.
 */

namespace Innova\CollecticielBundle\Repository;

use Doctrine\ORM\EntityRepository;

class GradeRepository extends EntityRepository
{
    public function findByCriteriaAndCorrection($criteria, $correction)
    {
        $criteriaIds = array();
        foreach ($criteria as $criterion) {
            $criteriaIds[] = $criterion->getId();
        }

        $grades = $this
            ->createQueryBuilder('grade')
            ->select('grade')
            ->join('grade.criterion', 'criterion')
            ->join('grade.correction', 'correction')
            ->andWhere('criterion.id IN (:criterionId)')
            ->andWhere('correction.id = :correctionId')
            ->setParameter('criterionId', $criteriaIds)
            ->setParameter('correctionId', $correction->getId())
            ->getQuery()->getResult();

        return $grades;
    }
}
