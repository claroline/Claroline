<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\SurveyBundle\Manager;

use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Persistence\ObjectManager;
use Claroline\SurveyBundle\Entity\Choice;
use Claroline\SurveyBundle\Entity\Question;
use Claroline\SurveyBundle\Entity\MultipleChoiceQuestion;
use Claroline\SurveyBundle\Entity\Survey;
use Claroline\SurveyBundle\Entity\SurveyQuestionRelation;
//use Claroline\SurveyBundle\QuestionTypeHandler\AbstractQuestionTypeHandler;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.manager.survey_manager")
 */
class SurveyManager
{
    private $om;
    private $choiceRepo;
    private $multipleChoiceQuestionRepo;
    private $surveyQuestionRelationRepo;
    private $questionRepo;
    
    /**
     * @DI\InjectParams({
     *     "om" = @DI\Inject("claroline.persistence.object_manager")
     * })
     */
    public function __construct(ObjectManager $om)
    {
        $this->om = $om;
        $this->choiceRepo =
            $om->getRepository('ClarolineSurveyBundle:Choice');
        $this->multipleChoiceQuestionRepo =
            $om->getRepository('ClarolineSurveyBundle:MultipleChoiceQuestion');
        $this->surveyQuestionRelationRepo =
            $om->getRepository('ClarolineSurveyBundle:SurveyQuestionRelation');
        $this->questionRepo =
            $om->getRepository('ClarolineSurveyBundle:Question');
    }

    public function persistSurvey(Survey $survey)
    {
        $this->om->persist($survey);
        $this->om->flush();
    }

    public function persistQuestion(Question $question)
    {
        $this->om->persist($question);
        $this->om->flush();
    }

    public function deleteQuestion(Question $question)
    {
        $this->om->remove($question);
        $this->om->flush();
    }

    public function createMultipleChoiceQuestion(
        Question $question,
        array $choices,
        $multipleResponse
    )
    {
        $multipleChoiceQuestion = new MultipleChoiceQuestion();
        $multipleChoiceQuestion->setQuestion($question);
        $multipleChoiceQuestion->setAllowMultipleResponse($multipleResponse);
        $this->om->persist($multipleChoiceQuestion);

        foreach ($choices as $choice) {
            $newChoice = new Choice();
            $newChoice->setChoiceQuestion($multipleChoiceQuestion);
            $newChoice->setContent($choice);
            $this->om->persist($newChoice);
        }
        $this->om->flush();
    }

    public function updateQuestionChoices(
        MultipleChoiceQuestion $multipleChoiceQuestion,
        array $newChoices,
        $multipleResponse
    )
    {
        $multipleChoiceQuestion->setAllowMultipleResponse($multipleResponse);
        $this->om->persist($multipleChoiceQuestion);

        $oldChoices = $multipleChoiceQuestion->getChoices();

        foreach ($oldChoices as $oldChoice) {
            $this->om->remove($oldChoice);
        }

        foreach ($newChoices as $newChoice) {
            $choice = new Choice();
            $choice->setChoiceQuestion($multipleChoiceQuestion);
            $choice->setContent($newChoice);
            $this->om->persist($choice);
        }
        $this->om->flush();
    }

    public function createSurveyQuestionRelation(
        Survey $survey,
        Question $question
    )
    {
        $relation = new SurveyQuestionRelation();
        $relation->setSurvey($survey);
        $relation->setQuestion($question);
        $orderMaxTab = $this->getSurveyLastQuestionOrder($survey);
        $orderMax = $orderMaxTab['order_max'];

        if (is_null($orderMax)) {
            $orderMax = 0;
        }
        $orderMax++;
        $relation->setQuestionOrder($orderMax);

        $this->om->persist($relation);
        $this->om->flush();
    }

    public function deleteSurveyQuestionRelation(
        Survey $survey,
        Question $question
    )
    {
        $relation = $this->getRelationBySurveyAndQuestion($survey, $question);

        if (!is_null($relation)) {
            $this->om->remove($relation);
            $this->om->flush();
        }
    }

    public function getAvailableQuestions(
        Workspace $workspace,
        array $exclusions,
        $orderedBy = 'title',
        $order = 'ASC',
        $executeQuery = true
    )
    {
        if (count($exclusions) === 0) {

            return $this->getQuestionsByWorkspace(
                $workspace,
                $orderedBy,
                $order,
                $executeQuery
            );
        } else {

            return $this->getQuestionsByWorkspaceWithExclusions(
                $workspace,
                $exclusions,
                $orderedBy,
                $order,
                $executeQuery
            );
        }
    }

    /****************************************
     * Access to QuestionRepository methods *
     ****************************************/

    public function getQuestionsByWorkspace(
        Workspace $workspace,
        $orderedBy = 'title',
        $order = 'ASC',
        $executeQuery = true
    )
    {
        return $this->questionRepo->findQuestionsByWorkspace(
            $workspace,
            $orderedBy,
            $order,
            $executeQuery
        );
    }

    public function getQuestionsByWorkspaceWithExclusions(
        Workspace $workspace,
        array $exclusions,
        $orderedBy = 'title',
        $order = 'ASC',
        $executeQuery = true
    )
    {
        return $this->questionRepo->findQuestionsByWorkspaceWithExclusions(
            $workspace,
            $exclusions,
            $orderedBy,
            $order,
            $executeQuery
        );
    }

    /**************************************
     * Access to ChoiceRepository methods *
     **************************************/

    public function getChoiceByQuestion(
        MultipleChoiceQuestion $question,
        $executeQuery = true
    )
    {
        return $this->choiceRepo->findChoiceByQuestion(
            $question,
            $executeQuery
        );
    }


    /******************************************************
     * Access to MultipleChoiceQuestionRepository methods *
     ******************************************************/

    public function getMultipleChoiceQuestionByQuestion(
        Question $question,
        $executeQuery = true
    )
    {
        return $this->multipleChoiceQuestionRepo
            ->findMultipleChoiceQuestionByQuestion($question, $executeQuery);
    }


    /******************************************************
     * Access to SurveyQuestionRelationRepository methods *
     ******************************************************/

    public function getRelationBySurveyAndQuestion(
        Survey $survey,
        Question $question,
        $executeQuery = true
    )
    {
        return $this->surveyQuestionRelationRepo->findRelationBySurveyAndQuestion(
            $survey,
            $question,
            $executeQuery
        );
    }

    public function getSurveyLastQuestionOrder(
        Survey $survey,
        $executeQuery = true
    )
    {
        return $this->surveyQuestionRelationRepo
            ->findSurveyLastQuestionOrder($survey, $executeQuery);
    }
}
