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
use Claroline\SurveyBundle\Entity\Survey;
use Doctrine\ORM\EntityRepository;

class SurveyQuestionRelationRepository extends EntityRepository
{
    public function findRelationsBySurvey(Survey $survey, $executeQuery = true)
    {
        $dql = "
            SELECT sqr
            FROM Claroline\SurveyBundle\Entity\SurveyQuestionRelation sqr
            WHERE sqr.survey = :survey
            ORDER BY sqr.questionOrder ASC
        ";
        $query = $this->_em->createQuery($dql);
        $query->setParameter('survey', $survey);

        return $executeQuery ? $query->getResult() : $query;
    }

    public function findRelationBySurveyAndQuestion(
        Survey $survey,
        Question $question,
        $executeQuery = true
    ) {
        $dql = "
            SELECT sqr
            FROM Claroline\SurveyBundle\Entity\SurveyQuestionRelation sqr
            WHERE sqr.survey = :survey
            AND sqr.question = :question
        ";
        $query = $this->_em->createQuery($dql);
        $query->setParameter('survey', $survey);
        $query->setParameter('question', $question);

        return $executeQuery ? $query->getOneOrNullResult() : $query;
    }

    public function findSurveyLastQuestionOrder(
        Survey $survey,
        $executeQuery = true
    ) {
        $dql = "
            SELECT MAX(sqr.questionOrder) AS order_max
            FROM Claroline\SurveyBundle\Entity\SurveyQuestionRelation sqr
            WHERE sqr.survey = :survey
        ";
        $query = $this->_em->createQuery($dql);
        $query->setParameter('survey', $survey);

        return $executeQuery ? $query->getSingleResult() : $query;
    }

    public function updateQuestionOrderBySurvey(
        Survey $survey,
        $questionOrder,
        $executeQuery = true
    ) {
        $dql = "
            UPDATE Claroline\SurveyBundle\Entity\SurveyQuestionRelation sqr
            SET sqr.questionOrder = sqr.questionOrder + 1
            WHERE sqr.survey = :survey
            AND sqr.questionOrder >= :questionOrder
        ";
        $query = $this->_em->createQuery($dql);
        $query->setParameter('survey', $survey);
        $query->setParameter('questionOrder', $questionOrder);

        return $executeQuery ? $query->execute() : $query;
    }
}
