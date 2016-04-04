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

use Claroline\CoreBundle\Entity\User;
use Claroline\SurveyBundle\Entity\Choice;
use Claroline\SurveyBundle\Entity\Question;
use Claroline\SurveyBundle\Entity\Survey;
use Claroline\SurveyBundle\Entity\Answer\QuestionAnswer;
use Doctrine\ORM\EntityRepository;

class MultipleChoiceQuestionAnswerRepository extends EntityRepository
{
    public function deleteMultipleChoiceAnswersByQuestionAnswer(
        QuestionAnswer $questionAnswer,
        $executeQuery = true
    )
    {
        $dql = "
            DELETE Claroline\SurveyBundle\Entity\Answer\MultipleChoiceQuestionAnswer mcqa
            WHERE mcqa.questionAnswer = :questionAnswer
        ";
        $query = $this->_em->createQuery($dql);
        $query->setParameter('questionAnswer', $questionAnswer);

        return $executeQuery ? $query->execute() : $query;
    }

    public function findAnswersByUserAndSurveyAndQuestion(
        User $user,
        Survey $survey,
        Question $question,
        $executeQuery = true
    )
    {
        $dql = "
            SELECT mcqa
            FROM Claroline\SurveyBundle\Entity\Answer\MultipleChoiceQuestionAnswer mcqa
            JOIN mcqa.questionAnswer qa
            JOIN qa.surveyAnswer sa
            WHERE sa.user = :user
            AND sa.survey = :survey
            AND qa.question = :question
        ";
        $query = $this->_em->createQuery($dql);
        $query->setParameter('user', $user);
        $query->setParameter('survey', $survey);
        $query->setParameter('question', $question);

        return $executeQuery ? $query->getResult() : $query;
    }

    public function countAnswersBySurveyAndChoice(
        Survey $survey,
        Choice $choice,
        $executeQuery = true
    )
    {
        $dql = "
            SELECT COUNT(mcqa) AS nb_answers
            FROM Claroline\SurveyBundle\Entity\Answer\MultipleChoiceQuestionAnswer mcqa
            JOIN mcqa.questionAnswer qa
            JOIN qa.surveyAnswer sa
            WHERE sa.survey = :survey
            AND mcqa.choice = :choice
        ";
        $query = $this->_em->createQuery($dql);
        $query->setParameter('survey', $survey);
        $query->setParameter('choice', $choice);

        return $executeQuery ? $query->getOneOrNullResult() : $query;
    }

    public function findAnswersByChoice(
        Choice $choice,
        $executeQuery = true
    )
    {
        $dql = "
            SELECT mcqa
            FROM Claroline\SurveyBundle\Entity\Answer\MultipleChoiceQuestionAnswer mcqa
            WHERE mcqa.choice = :choice
        ";
        $query = $this->_em->createQuery($dql);
        $query->setParameter('choice', $choice);

        return $executeQuery ? $query->getResult() : $query;
    }

    public function findMultipleChoiceAnswersByQuestionAnswer(
        QuestionAnswer $questionAnswer,
        $executeQuery = true
    )
    {
        $dql = "
            SELECT mcqa
            FROM Claroline\SurveyBundle\Entity\Answer\MultipleChoiceQuestionAnswer mcqa
            WHERE mcqa.questionAnswer = :questionAnswer
        ";
        $query = $this->_em->createQuery($dql);
        $query->setParameter('questionAnswer', $questionAnswer);

        return $executeQuery ? $query->getResult() : $query;
    }
}
