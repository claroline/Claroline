<?php
/**
 * Created by : Eric VINCENT
 * Date: 06/16.
 */

namespace Innova\CollecticielBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Innova\CollecticielBundle\Entity\ChoiceNotation;
use Innova\CollecticielBundle\Entity\GradingNotation;
use Innova\CollecticielBundle\Entity\Notation;

class ChoiceNotationRepository extends EntityRepository
{
    /**
     *  Pour savoir le type d'accusé de réception pour l'utilisateur indiqué et le dropzone indiqué.
     *
     * @param $gradingNotation
     * @param $notation
     */
    public function getChoiceTextForCriteriaAndNotation(GradingNotation $gradingNotation, Notation $notation)
    {
        $qb = $this->createQueryBuilder('choicenotation')
            ->select('choicenotation')
            ->andWhere('choicenotation.gradingNotation = :gradingNotation')
            ->andWhere('choicenotation.notation = :notation')
            ->setParameter('gradingNotation', $gradingNotation)
            ->setParameter('notation', $notation);

        return $qb->getQuery()->getResult();
    }
}
