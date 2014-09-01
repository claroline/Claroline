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

use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Pager\PagerFactory;
use Claroline\CoreBundle\Persistence\ObjectManager;
use Claroline\SurveyBundle\Entity\Answer\MultipleChoiceQuestionAnswer;
use Claroline\SurveyBundle\Entity\Answer\OpenEndedQuestionAnswer;
use Claroline\SurveyBundle\Entity\Answer\QuestionAnswer;
use Claroline\SurveyBundle\Entity\Answer\SurveyAnswer;
use Claroline\SurveyBundle\Entity\Choice;
use Claroline\SurveyBundle\Entity\Question;
use Claroline\SurveyBundle\Entity\QuestionModel;
use Claroline\SurveyBundle\Entity\MultipleChoiceQuestion;
use Claroline\SurveyBundle\Entity\Survey;
use Claroline\SurveyBundle\Entity\SurveyQuestionRelation;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.manager.survey_manager")
 */
class SurveyManager
{
    private $om;
    private $choiceRepo;
    private $multipleChoiceQuestionAnswerRepo;
    private $multipleChoiceQuestionRepo;
    private $openEndedQuestionAnswerRepo;
    private $pagerFactory;
    private $surveyAnswerRepo;
    private $surveyQuestionRelationRepo;
    private $questionAnswerRepo;
    private $questionModelRepo;
    private $questionRepo;
    
    /**
     * @DI\InjectParams({
     *     "om"           = @DI\Inject("claroline.persistence.object_manager"),
     *     "pagerFactory" = @DI\Inject("claroline.pager.pager_factory")
     * })
     */
    public function __construct(ObjectManager $om, PagerFactory $pagerFactory)
    {
        $this->om = $om;
        $this->pagerFactory = $pagerFactory;
        $this->choiceRepo =
            $om->getRepository('ClarolineSurveyBundle:Choice');
        $this->multipleChoiceQuestionAnswerRepo =
            $om->getRepository('ClarolineSurveyBundle:Answer\MultipleChoiceQuestionAnswer');
        $this->multipleChoiceQuestionRepo =
            $om->getRepository('ClarolineSurveyBundle:MultipleChoiceQuestion');
        $this->openEndedQuestionAnswerRepo =
            $om->getRepository('ClarolineSurveyBundle:Answer\OpenEndedQuestionAnswer');
        $this->surveyAnswerRepo =
            $om->getRepository('ClarolineSurveyBundle:Answer\SurveyAnswer');
        $this->surveyQuestionRelationRepo =
            $om->getRepository('ClarolineSurveyBundle:SurveyQuestionRelation');
        $this->questionAnswerRepo =
            $om->getRepository('ClarolineSurveyBundle:Answer\QuestionAnswer');
        $this->questionModelRepo =
            $om->getRepository('ClarolineSurveyBundle:QuestionModel');
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

    public function persistChoice(Choice $choice)
    {
        $this->om->persist($choice);
        $this->om->flush();
    }

    public function createMultipleChoiceQuestion(
        Question $question,
        $horizontal,
        array $choices
    )
    {
        $multipleChoiceQuestion = new MultipleChoiceQuestion();
        $multipleChoiceQuestion->setQuestion($question);
        $multipleChoiceQuestion->setHorizontal($horizontal);
        $this->om->persist($multipleChoiceQuestion);

        foreach ($choices as $choice) {

            if (!empty($choice)) {
                $newChoice = new Choice();
                $newChoice->setChoiceQuestion($multipleChoiceQuestion);
                $newChoice->setContent($choice);
                $this->om->persist($newChoice);
            }
        }
        $this->om->flush();

        return $multipleChoiceQuestion;
    }

    public function updateQuestionChoices(
        MultipleChoiceQuestion $multipleChoiceQuestion,
        $horizontal,
        array $newChoices
    )
    {
        $multipleChoiceQuestion->setHorizontal($horizontal);
        $this->om->persist($multipleChoiceQuestion);

        $oldChoices = $multipleChoiceQuestion->getChoices();

        foreach ($oldChoices as $oldChoice) {
            $this->om->remove($oldChoice);
        }

        foreach ($newChoices as $newChoice) {

            if (!empty($newChoice)) {
                $choice = new Choice();
                $choice->setChoiceQuestion($multipleChoiceQuestion);
                $choice->setContent($newChoice);
                $this->om->persist($choice);
            }
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

    public function persistSurveyQuestionRelation(SurveyQuestionRelation $relation)
    {
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
        $page = 1,
        $max = 20,
        $executeQuery = true
    )
    {
        if (count($exclusions) === 0) {

            return $this->getQuestionsByWorkspace(
                $workspace,
                $orderedBy,
                $order,
                $page,
                $max,
                $executeQuery
            );
        } else {

            return $this->getQuestionsByWorkspaceWithExclusions(
                $workspace,
                $exclusions,
                $orderedBy,
                $order,
                $page,
                $max,
                $executeQuery
            );
        }
    }

    public function updateSurveyStatus(Survey $survey)
    {
        $startDate = $survey->getStartDate();
        $endDate = $survey->getEndDate();
        $flush = false;

        if (!is_null($startDate) || !is_null($endDate)) {
            $now = new \DateTime();

            if ((!is_null($startDate) && !is_null($endDate) &&
                $now > $startDate && $now < $endDate) &&
                ($survey->isClosed() || !$survey->isPublished())) {

                $survey->setPublished(true);
                $survey->setClosed(false);
                $this->om->persist($survey);
                $flush = true;
            } else {

                if (!$survey->isPublished() &&
                    !$survey->isClosed() &&
                    !is_null($startDate) &&
                    $now > $startDate) {

                    $survey->setPublished(true);
                    $this->om->persist($survey);
                    $flush = true;
                }

                if (!$survey->isClosed() &&
                    !is_null($endDate) &&
                    $now > $endDate) {

                    $survey->setClosed(true);
                    $this->om->persist($survey);
                    $flush = true;
                }
            }

            if ($flush) {
                $this->om->flush();
            }
        }
    }

    public function persistSurveyAnswer(SurveyAnswer $surveyAnswer)
    {
        $this->om->persist($surveyAnswer);
        $this->om->flush();
    }

    public function persistQuestionAnswer(QuestionAnswer $questionAnswer)
    {
        $this->om->persist($questionAnswer);
        $this->om->flush();
    }

    public function persistOpenEndedQuestionAnswer(
        OpenEndedQuestionAnswer $openEndedAnswer
    )
    {
        $this->om->persist($openEndedAnswer);
        $this->om->flush();
    }

    public function persistMultipleChoiceQuestionAnswer(
        MultipleChoiceQuestionAnswer $choiceAnswer
    )
    {
        $this->om->persist($choiceAnswer);
        $this->om->flush();
    }

    public function createQuestionModel(Question $question)
    {
        $questionType = $question->getType();

        $model = new QuestionModel();
        $model->setTitle($question->getTitle());
        $model->setQuestionType($questionType);
        $model->setWorkspace($question->getWorkspace());
        $details = array();

        switch ($questionType) {

            case 'multiple_choice_single':
            case 'multiple_choice_multiple':
                $choiceQuestion =
                    $this->getMultipleChoiceQuestionByQuestion($question);
                $details['questionType'] = $questionType;

                if ($question->isCommentAllowed()) {
                    $details['withComment'] = 'comment';
                    $details['commentLabel'] = $question->getCommentLabel();
                } else {
                    $details['withComment'] = 'no-comment';
                }
                
                $horizontal = !is_null($choiceQuestion) &&
                    $choiceQuestion->getHorizontal();
                $details['choiceDisplay'] = $horizontal ?
                    'horizontal' :
                    'vertical';
                $details['choices'] = array();

                if (!is_null($choiceQuestion)) {
                    $choices = $this->getChoicesByQuestion($question);

                    foreach ($choices as $choice) {
                        $choiceDetails = array();
                        $choiceDetails['other'] = $choice->isOther() ?
                            'other' :
                            'not-other';
                        $choiceDetails['content'] = $choice->getContent();
                        $details['choices'][] = $choiceDetails;
                    }
                }
                break;
            case 'open-ended':
            default:
                break;
        }
        $model->setDetails($details);

        $this->om->persist($model);
        $this->om->flush();
    }

    public function deleteQuestionModel(QuestionModel $model)
    {
        $this->om->remove($model);
        $this->om->flush();
    }

    public function updateQuestionOrder(
        Survey $survey,
        SurveyQuestionRelation $relation,
        $questionOrder
    )
    {
        $this->updateQuestionOrderBySurvey($survey, $questionOrder);
        $relation->setQuestionOrder($questionOrder);
        $this->om->persist($relation);
        $this->om->flush();
    }


    /****************************************
     * Access to QuestionRepository methods *
     ****************************************/

    public function getQuestionById($questionId, $executeQuery = true)
    {
        return $this->questionRepo->findQuestionById($questionId, $executeQuery);
    }

    public function getQuestionsByWorkspace(
        Workspace $workspace,
        $orderedBy = 'title',
        $order = 'ASC',
        $page = 1,
        $max = 20,
        $executeQuery = true
    )
    {
        $questions = $this->questionRepo->findQuestionsByWorkspace(
            $workspace,
            $orderedBy,
            $order,
            $executeQuery
        );

        return $this->pagerFactory->createPagerFromArray($questions, $page, $max);
    }

    public function getQuestionsByWorkspaceWithExclusions(
        Workspace $workspace,
        array $exclusions,
        $orderedBy = 'title',
        $order = 'ASC',
        $page = 1,
        $max = 20,
        $executeQuery = true
    )
    {
        $questions = $this->questionRepo->findQuestionsByWorkspaceWithExclusions(
            $workspace,
            $exclusions,
            $orderedBy,
            $order,
            $executeQuery
        );

        return $this->pagerFactory->createPagerFromArray($questions, $page, $max);
    }

    /**************************************
     * Access to ChoiceRepository methods *
     **************************************/

    public function getChoiceById($choiceId)
    {
        return $this->choiceRepo->findOneById($choiceId);
    }

    public function getChoicesByQuestion(
        Question $question,
        $executeQuery = true
    )
    {
        return $this->choiceRepo->findChoicesByQuestion(
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

    public function updateQuestionOrderBySurvey(
        Survey $survey,
        $questionOrder,
        $executeQuery = true
    )
    {
        return $this->surveyQuestionRelationRepo->updateQuestionOrderBySurvey(
            $survey,
            $questionOrder,
            $executeQuery
        );
    }


    /********************************************
     * Access to SurveyAnswerRepository methods *
     ********************************************/

    public function getSurveyAnswerBySurveyAndUser(
        Survey $survey,
        User $user,
        $executeQuery = true
    )
    {
        return $this->surveyAnswerRepo->findSurveyAnswerBySurveyAndUser(
            $survey,
            $user,
            $executeQuery
        );
    }


    /**********************************************
     * Access to QuestionAnswerRepository methods *
     **********************************************/

    public function getQuestionAnswerBySurveyAnswerAndQuestion(
        SurveyAnswer $surveyAnswer,
        Question $question,
        $executeQuery = true
    )
    {
        return $this->questionAnswerRepo->findQuestionAnswerBySurveyAnswerAndQuestion(
            $surveyAnswer,
            $question,
            $executeQuery
        );
    }

    public function countQuestionAnswersBySurveyAndQuestion(
        Survey $survey,
        Question $question,
        $executeQuery = true
    )
    {
        return $this->questionAnswerRepo->countAnswersBySurveyAndQuestion(
            $survey,
            $question,
            $executeQuery
        );
    }

    public function getCommentsFromQuestionBySurveyAndQuestion(
        Survey $survey,
        Question $question,
        $page = 1,
        $max = 20,
        $executeQuery = true
    )
    {
        $comments = $this->questionAnswerRepo->findCommentsBySurveyAndQuestion(
            $survey,
            $question,
            $executeQuery
        );

        return $this->pagerFactory->createPagerFromArray($comments, $page, $max);
    }


    /*******************************************************
     * Access to OpenEndedQuestionAnswerRepository methods *
     *******************************************************/

    public function getOpenEndedAnswerByQuestionAnswer(
        QuestionAnswer $questionAnswer,
        $executeQuery = true
    )
    {
        return $this->openEndedQuestionAnswerRepo->findOpenEndedAnswerByQuestionAnswer(
            $questionAnswer,
            $executeQuery
        );
    }

    public function getOpenEndedAnswerByUserAndSurveyAndQuestion(
        User $user,
        Survey $survey,
        Question $question,
        $executeQuery = true
    )
    {
        return $this->openEndedQuestionAnswerRepo->findAnswerByUserAndSurveyAndQuestion(
            $user,
            $survey,
            $question,
            $executeQuery
        );
    }
    
    public function getOpenEndedAnswersBySurveyAndQuestion(
        Survey $survey,
        Question $question,
        $page = 1,
        $max = 20,
        $executeQuery = true
    )
    {
        $answers = $this->openEndedQuestionAnswerRepo->findAnswersBySurveyAndQuestion(
            $survey,
            $question,
            $executeQuery
        );

        return $this->pagerFactory->createPagerFromArray($answers, $page, $max);
    }


    /************************************************************
     * Access to MultipleChoiceQuestionAnswerRepository methods *
     ************************************************************/

    public function deleteMultipleChoiceAnswersByQuestionAnswer(
        QuestionAnswer $questionAnswer,
        $executeQuery = true
    )
    {
        return $this->multipleChoiceQuestionAnswerRepo
            ->deleteMultipleChoiceAnswersByQuestionAnswer(
                $questionAnswer,
                $executeQuery
            );
    }

    public function getMultipleChoiceAnswersByUserAndSurveyAndQuestion(
        User $user,
        Survey $survey,
        Question $question,
        $executeQuery = true
    )
    {
        return $this->multipleChoiceQuestionAnswerRepo
            ->findAnswersByUserAndSurveyAndQuestion(
                $user,
                $survey,
                $question,
                $executeQuery
            );
    }

    public function countMultipleChoiceAnswersBySurveyAndChoice(
        Survey $survey,
        Choice $choice,
        $executeQuery = true
    )
    {
        return $this->multipleChoiceQuestionAnswerRepo->countAnswersBySurveyAndChoice(
            $survey,
            $choice,
            $executeQuery
        );

    }

    public function getMultipleChoiceAnswersByChoice(
        Choice $choice,
        $page,
        $max,
        $executeQuery = true
    )
    {
        $answers = $this->multipleChoiceQuestionAnswerRepo->findAnswersByChoice(
            $choice,
            $executeQuery
        );

        return $this->pagerFactory->createPagerFromArray($answers, $page, $max);
    }


    /*********************************************
     * Access to QuestionModelRepository methods *
     *********************************************/

    public function getQuestionModelsByWorkspace(
        Workspace $workspace,
        $orderedBy = 'title',
        $order = 'ASC',
        $executeQuery = true
    )
    {
        return $this->questionModelRepo->findModelsByWorkspace(
            $workspace,
            $orderedBy,
            $order,
            $executeQuery
        );
    }
}
