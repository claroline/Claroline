<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\SurveyBundle\QuestionTypeHandler;

use Claroline\CoreBundle\Entity\User;
use Claroline\SurveyBundle\Entity\AbstractQuestion;
use Claroline\SurveyBundle\Entity\Survey;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\Request;

abstract class AbstractQuestionTypeHandler
{
    /**
     * Returns the question type supported by the handler.
     *
     * @return string
     */
    abstract public function getSupportedType();

    /**
     * Returns the question repository.
     *
     * @return EntityRepository
     */
    abstract public function getRepository();

    /**
     * Returns the question creation form.
     *
     * @return FormView
     */
    abstract public function getCreationForm();

    /**
     * Returns a question edition form.
     *
     * @param AbstractQuestion $question
     * @return FormView
     */
    abstract public function getEditionForm(AbstractQuestion $question);

    /**
     * Returns a question answer form.
     *
     * @param AbstractQuestion $question
     * @return FormView
     */
    abstract public function getAnswerForm(AbstractQuestion $question);

    /**
     * Returns a view of the answers to a question.
     *
     * @param AbstractQuestion $question
     * @return string
     */
    abstract public function getAnswersView(AbstractQuestion $question);

    /**
     * Records a user answer to a question.
     *
     * @param \Claroline\SurveyBundle\Entity\Survey $survey
     * @param Request $request
     * @param User $user
     */
    abstract public function registerAnswer(Survey $survey, Request $request, User $user);

    /**
     * Does the real work of creating a question. Must return true in case of
     * success, a form view populated with errors otherwise.
     *
     * @param Survey $survey
     * @param Request $request
     * @return boolean|FormView
     */
    abstract protected function doCreateQuestion(Survey $survey, Request $request);

    /**
     * Does the real work of editing an existing question. Must return true in
     * case of success, a form view populated with errors otherwise.
     *
     * @param Survey $survey
     * @param Request $request
     * @return boolean|FormView
     */
    abstract protected function doEditQuestion(Survey $survey, Request $request);

    /**
     * Checks if a survey has an associated question.
     *
     * @param Survey $survey
     * @return bool
     */
    public function hasQuestion(Survey $survey)
    {
        return $this->getQuestion($survey, false) !== null;
    }

    /**
     * Returns the question associated to a survey. Can throw an exception
     * if that's not the case.
     *
     * @param Survey $survey
     * @param bool $mustExist
     * @return AbstractQuestion
     * @throws \LogicError
     */
    public function getQuestion(Survey $survey, $mustExist = true)
    {
        $question = $this->getRepository()->findOneBySurvey($survey);

        if ($mustExist && !$question) {
            throw new \LogicError('Survey has no associated question');
        }

        return $question;
    }

    /**
     * Creates a question. Will return true in case of success, a form view
     * populated with errors otherwise.
     *
     * @param Survey $survey
     * @param Request $request
     * @return bool|FormView
     * @throws \LogicException if the survey already has an associated question
     */
    public function createQuestion(Survey $survey, Request $request)
    {
        if ($this->hasQuestion($survey)) {
            throw new \LogicException('Survey already has an associated question');
        }

        return $this->doCreateQuestion($survey, $request);
    }

    /**
     * Edits an existing question. Will return true in case of success, a
     * form view populated with errors otherwise.
     *
     * @param Survey $survey
     * @param Request $request
     * @return bool|FormView
     * @throws \LogicException if the survey has no associated question
     */
    public function editQuestion(Survey $survey, Request $request)
    {
        if (!$this->hasQuestion($survey)) {
            throw new \LogicException('Survey has no associated question');
        }

        return $this->doEditQuestion($survey, $request);
    }
}
