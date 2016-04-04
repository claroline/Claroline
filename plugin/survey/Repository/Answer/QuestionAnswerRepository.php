<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\SurveyBundle\Repository\Answer;

use Claroline\SurveyBundle\Entity\Answer\SurveyAnswer;
use Claroline\SurveyBundle\Entity\Question;
use Claroline\SurveyBundle\Entity\Survey;
use Doctrine\ORM\EntityRepository;

class QuestionAnswerRepository extends EntityRepository
{
    public function findQuestionAnswerBySurveyAnswerAndQuestion(
        SurveyAnswer $surveyAnswer,
        Question $question,
        $executeQuery = true
    )
    {
        $dql = "
            SELECT qa
            FROM Claroline\SurveyBundle\Entity\Answer\QuestionAnswer qa
            WHERE qa.surveyAnswer = :surveyAnswer
            AND qa.question = :question
        ";
        $query = $this->_em->createQuery($dql);
        $query->setParameter('surveyAnswer', $surveyAnswer);
        $query->setParameter('question', $question);

        return $executeQuery ? $query->getOneOrNullResult() : $query;
    }

    public function countAnswersBySurveyAndQuestion(
        Survey $survey,
        Question $question,
        $executeQuery = true
    )
    {
        $dql = "
            SELECT COUNT(qa) AS nb_answers
            FROM Claroline\SurveyBundle\Entity\Answer\QuestionAnswer qa
            JOIN qa.surveyAnswer sa
            WHERE sa.survey = :survey
            AND qa.question = :question
        ";
        $query = $this->_em->createQuery($dql);
        $query->setParameter('survey', $survey);
        $query->setParameter('question', $question);

        return $executeQuery ? $query->getOneOrNullResult() : $query;
    }

    public function findCommentsBySurveyAndQuestion(
        Survey $survey,
        Question $question,
        $executeQuery = true
    )
    {
        $dql = "
            SELECT qa.comment AS comment
            FROM Claroline\SurveyBundle\Entity\Answer\QuestionAnswer qa
            JOIN qa.surveyAnswer sa
            WHERE sa.survey = :survey
            AND qa.question = :question
            AND qa.comment IS NOT NULL
        ";
        $query = $this->_em->createQuery($dql);
        $query->setParameter('survey', $survey);
        $query->setParameter('question', $question);

        return $executeQuery ? $query->getResult() : $query;
    }
}
