<?php
/**
 * Created by : Eric VINCENT
 * Date: 06/16.
 */

namespace Innova\CollecticielBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Innova\CollecticielBundle\Entity\GradingCriteria;
use Innova\CollecticielBundle\Entity\Notation;
use Innova\CollecticielBundle\Entity\ChoiceCriteria;

class ChoiceCriteriaRepository extends EntityRepository
{
    /**
     *  Pour savoir le type d'accusé de réception pour l'utilisateur indiqué et le dropzone indiqué.
     *
     * @param $gradingCriteria
     * @param $notation
     */
    public function getChoiceTextForCriteriaAndNotation(GradingCriteria $gradingCriteria, Notation $notation)
    {
        $qb = $this->createQueryBuilder('choicecriteria')
            ->select('choicecriteria')
            ->andWhere('choicecriteria.gradingCriteria = :gradingCriteria')
            ->andWhere('choicecriteria.notation = :notation')
            ->setParameter('gradingCriteria', $gradingCriteria)
            ->setParameter('notation', $notation);

        return $qb->getQuery()->getResult();
    }
}
