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

use Claroline\SurveyBundle\Entity\Question;
use Doctrine\ORM\EntityRepository;

class MultipleChoiceQuestionRepository extends EntityRepository
{
    public function findMultipleChoiceQuestionByQuestion(
        Question $question,
        $executeQuery = true
    ) {
        $dql = '
            SELECT q
            FROM Claroline\SurveyBundle\Entity\MultipleChoiceQuestion q
            WHERE q.question = :question
            ORDER BY q.id ASC
        ';
        $query = $this->_em->createQuery($dql);
        $query->setParameter('question', $question);

        return $executeQuery ? $query->getOneOrNullResult() : $query;
    }
}
