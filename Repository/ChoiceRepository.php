<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\SurveyBundle\Repository;

use Claroline\SurveyBundle\Entity\MultipleChoiceQuestion;
use Doctrine\ORM\EntityRepository;

class ChoiceRepository extends EntityRepository
{
    public function findChoicesByQuestion(
        MultipleChoiceQuestion $question,
        $executeQuery = true
    )
    {
        $dql = '
            SELECT c
            FROM Claroline\SurveyBundle\Entity\Choice c
            WHERE c.choiceQuestion = :question
            ORDER BY c.id ASC
        ';
        $query = $this->_em->createQuery($dql);
        $query->setParameter('question', $question);

        return $executeQuery ? $query->getResult() : $query;
    }
}