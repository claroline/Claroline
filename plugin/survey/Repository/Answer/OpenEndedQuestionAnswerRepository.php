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
use Claroline\SurveyBundle\Entity\Answer\QuestionAnswer;
use Claroline\SurveyBundle\Entity\Question;
use Claroline\SurveyBundle\Entity\Survey;
use Doctrine\ORM\EntityRepository;

class OpenEndedQuestionAnswerRepository extends EntityRepository
{
    public function findOpenEndedAnswerByQuestionAnswer(
        QuestionAnswer $questionAnswer,
        $executeQuery = true
    ) {
        $dql = "
            SELECT oeqa
            FROM Claroline\SurveyBundle\Entity\Answer\OpenEndedQuestionAnswer oeqa
            WHERE oeqa.questionAnswer = :questionAnswer
        ";
        $query = $this->_em->createQuery($dql);
        $query->setParameter('questionAnswer', $questionAnswer);

        return $executeQuery ? $query->getOneOrNullResult() : $query;
    }

    public function findAnswerByUserAndSurveyAndQuestion(
        User $user,
        Survey $survey,
        Question $question,
        $executeQuery = true
    ) {
        $dql = "
            SELECT oeqa
            FROM Claroline\SurveyBundle\Entity\Answer\OpenEndedQuestionAnswer oeqa
            JOIN oeqa.questionAnswer qa
            JOIN qa.surveyAnswer sa
            WHERE sa.user = :user
            AND sa.survey = :survey
            AND qa.question = :question
        ";
        $query = $this->_em->createQuery($dql);
        $query->setParameter('user', $user);
        $query->setParameter('survey', $survey);
        $query->setParameter('question', $question);

        return $executeQuery ? $query->getOneOrNullResult() : $query;
    }

    public function findAnswersBySurveyAndQuestion(
        Survey $survey,
        Question $question,
        $executeQuery = true
    ) {
        $dql = "
            SELECT oeqa
            FROM Claroline\SurveyBundle\Entity\Answer\OpenEndedQuestionAnswer oeqa
            JOIN oeqa.questionAnswer qa
            JOIN qa.surveyAnswer sa
            WHERE sa.survey = :survey
            AND qa.question = :question
        ";
        $query = $this->_em->createQuery($dql);
        $query->setParameter('survey', $survey);
        $query->setParameter('question', $question);

        return $executeQuery ? $query->getResult() : $query;
    }
}
