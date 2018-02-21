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

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Pager\PagerFactory;
use Claroline\SurveyBundle\Entity\Answer\MultipleChoiceQuestionAnswer;
use Claroline\SurveyBundle\Entity\Answer\OpenEndedQuestionAnswer;
use Claroline\SurveyBundle\Entity\Answer\QuestionAnswer;
use Claroline\SurveyBundle\Entity\Answer\SimpleTextQuestionAnswer;
use Claroline\SurveyBundle\Entity\Answer\SurveyAnswer;
use Claroline\SurveyBundle\Entity\Choice;
use Claroline\SurveyBundle\Entity\MultipleChoiceQuestion;
use Claroline\SurveyBundle\Entity\Question;
use Claroline\SurveyBundle\Entity\QuestionModel;
use Claroline\SurveyBundle\Entity\Survey;
use Claroline\SurveyBundle\Entity\SurveyQuestionRelation;
use Claroline\SurveyBundle\Event\Log\LogSurveyAnswerDelete;
use Doctrine\Common\Collections\ArrayCollection;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * @DI\Service("claroline.manager.survey_manager")
 */
class SurveyManager
{
    private $eventDispatcher;
    private $om;
    private $pagerFactory;

    private $choiceRepo;
    private $multipleChoiceQuestionAnswerRepo;
    private $multipleChoiceQuestionRepo;
    private $openEndedQuestionAnswerRepo;
    private $simpleTextQuestionAnswerRepo;
    private $surveyAnswerRepo;
    private $surveyQuestionRelationRepo;
    private $questionAnswerRepo;
    private $questionModelRepo;
    private $questionRepo;

    /**
     * @DI\InjectParams({
     *     "eventDispatcher" = @DI\Inject("event_dispatcher"),
     *     "om"              = @DI\Inject("claroline.persistence.object_manager"),
     *     "pagerFactory"    = @DI\Inject("claroline.pager.pager_factory")
     * })
     */
    public function __construct(EventDispatcherInterface $eventDispatcher, ObjectManager $om, PagerFactory $pagerFactory)
    {
        $this->eventDispatcher = $eventDispatcher;
        $this->om = $om;
        $this->pagerFactory = $pagerFactory;

        $this->choiceRepo = $om->getRepository('ClarolineSurveyBundle:Choice');
        $this->multipleChoiceQuestionAnswerRepo = $om->getRepository('ClarolineSurveyBundle:Answer\MultipleChoiceQuestionAnswer');
        $this->multipleChoiceQuestionRepo = $om->getRepository('ClarolineSurveyBundle:MultipleChoiceQuestion');
        $this->openEndedQuestionAnswerRepo = $om->getRepository('ClarolineSurveyBundle:Answer\OpenEndedQuestionAnswer');
        $this->simpleTextQuestionAnswerRepo = $om->getRepository('ClarolineSurveyBundle:Answer\SimpleTextQuestionAnswer');
        $this->surveyAnswerRepo = $om->getRepository('ClarolineSurveyBundle:Answer\SurveyAnswer');
        $this->surveyQuestionRelationRepo = $om->getRepository('ClarolineSurveyBundle:SurveyQuestionRelation');
        $this->questionAnswerRepo = $om->getRepository('ClarolineSurveyBundle:Answer\QuestionAnswer');
        $this->questionModelRepo = $om->getRepository('ClarolineSurveyBundle:QuestionModel');
        $this->questionRepo = $om->getRepository('ClarolineSurveyBundle:Question');
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
    ) {
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
    ) {
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
    ) {
        $relation = new SurveyQuestionRelation();
        $relation->setSurvey($survey);
        $relation->setQuestion($question);
        $orderMaxTab = $this->getSurveyLastQuestionOrder($survey);
        $orderMax = $orderMaxTab['order_max'];

        if (is_null($orderMax)) {
            $orderMax = 0;
        }
        ++$orderMax;
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
    ) {
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
    ) {
        if (0 === count($exclusions)) {
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
    ) {
        $this->om->persist($openEndedAnswer);
        $this->om->flush();
    }

    public function persistSimpleTextQuestionAnswer(
        SimpleTextQuestionAnswer $simpleTextAnswer
    ) {
        $this->om->persist($simpleTextAnswer);
        $this->om->flush();
    }

    public function persistMultipleChoiceQuestionAnswer(
        MultipleChoiceQuestionAnswer $choiceAnswer
    ) {
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
        $details = [];

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
                $details['choices'] = [];

                if (!is_null($choiceQuestion)) {
                    $choices = $this->getChoicesByQuestion($question);

                    foreach ($choices as $choice) {
                        $choiceDetails = [];
                        $choiceDetails['other'] = $choice->isOther() ?
                            'other' :
                            'not-other';
                        $choiceDetails['content'] = $choice->getContent();
                        $details['choices'][] = $choiceDetails;
                    }
                }
                break;
            case 'open_ended':
            case 'open_ended_bare':
            case 'simple_text':
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
    ) {
        $this->updateQuestionOrderBySurvey($survey, $questionOrder);
        $relation->setQuestionOrder($questionOrder);
        $this->om->persist($relation);
        $this->om->flush();
    }

    public function deleteSurveyAnswers(array $surveyAnswers)
    {
        $this->om->startFlushSuite();

        foreach ($surveyAnswers as $surveyAnswer) {
            $this->om->remove($surveyAnswer);
            $event = new LogSurveyAnswerDelete($surveyAnswer);
            $this->eventDispatcher->dispatch('log', $event);
        }
        $this->om->endFlushSuite();
    }

    public function deleteAllSurveyAnswers(Survey $survey)
    {
        $surveyAnswers = $this->getSurveyAnswersBySurvey($survey);
        $this->deleteSurveyAnswers($surveyAnswers);
    }

    public function checkQuestionAnswersByQuestions(array $questions)
    {
        $datas = [];
        $questionAnswers = $this->getQuestionAnswersByQuestions($questions);

        foreach ($questionAnswers as $questionAnswer) {
            $question = $questionAnswer->getQuestion();
            $datas[$question->getId()] = true;
        }

        return $datas;
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
    ) {
        $questions = $this->questionRepo->findQuestionsByWorkspace(
            $workspace,
            $orderedBy,
            $order,
            $executeQuery
        );

        return $executeQuery ?
            $this->pagerFactory->createPagerFromArray($questions, $page, $max) :
            $this->pagerFactory->createPager($questions, $page, $max);
    }

    public function getQuestionsByWorkspaceWithExclusions(
        Workspace $workspace,
        array $exclusions,
        $orderedBy = 'title',
        $order = 'ASC',
        $page = 1,
        $max = 20,
        $executeQuery = true
    ) {
        $questions = $this->questionRepo->findQuestionsByWorkspaceWithExclusions(
            $workspace,
            $exclusions,
            $orderedBy,
            $order,
            $executeQuery
        );

        return $executeQuery ?
            $this->pagerFactory->createPagerFromArray($questions, $page, $max) :
            $this->pagerFactory->createPager($questions, $page, $max);
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
    ) {
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
    ) {
        return $this->multipleChoiceQuestionRepo
            ->findMultipleChoiceQuestionByQuestion($question, $executeQuery);
    }

    /******************************************************
     * Access to SurveyQuestionRelationRepository methods *
     ******************************************************/

    public function getQuestionRelationsBySurvey(
        Survey $survey,
        $executeQuery = true
    ) {
        return $this->surveyQuestionRelationRepo
            ->findRelationsBySurvey($survey, $executeQuery);
    }

    public function getRelationBySurveyAndQuestion(
        Survey $survey,
        Question $question,
        $executeQuery = true
    ) {
        return $this->surveyQuestionRelationRepo->findRelationBySurveyAndQuestion(
            $survey,
            $question,
            $executeQuery
        );
    }

    public function getSurveyLastQuestionOrder(
        Survey $survey,
        $executeQuery = true
    ) {
        return $this->surveyQuestionRelationRepo
            ->findSurveyLastQuestionOrder($survey, $executeQuery);
    }

    public function updateQuestionOrderBySurvey(
        Survey $survey,
        $questionOrder,
        $executeQuery = true
    ) {
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
    ) {
        return $this->surveyAnswerRepo->findSurveyAnswerBySurveyAndUser(
            $survey,
            $user,
            $executeQuery
        );
    }

    public function getSurveyAnswersBySurvey(Survey $survey, $executeQuery = true)
    {
        return $this->surveyAnswerRepo->findSurveyAnswersBySurvey($survey, $executeQuery);
    }

    public function getSurveyAnswersBySurveyWithPager(Survey $survey, $page = 1, $max = 20)
    {
        $answers = $this->surveyAnswerRepo->findSurveyAnswersBySurvey($survey);

        return $this->pagerFactory->createPagerFromArray($answers, $page, $max);
    }

    /**********************************************
     * Access to QuestionAnswerRepository methods *
     **********************************************/

    public function getQuestionAnswerBySurveyAnswerAndQuestion(
        SurveyAnswer $surveyAnswer,
        Question $question,
        $executeQuery = true
    ) {
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
    ) {
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
    ) {
        $comments = $this->questionAnswerRepo->findCommentsBySurveyAndQuestion(
            $survey,
            $question,
            $executeQuery
        );

        return $executeQuery ?
            $this->pagerFactory->createPagerFromArray($comments, $page, $max) :
            $this->pagerFactory->createPager($comments, $page, $max);
    }

    public function getQuestionAnswersByQuestions(array $questions, $executeQuery = true)
    {
        return count($questions) > 0 ?
            $this->questionAnswerRepo->findQuestionAnswersByQuestions($questions, $executeQuery) :
            [];
    }

    /*******************************************************
     * Access to OpenEndedQuestionAnswerRepository methods *
     *******************************************************/

    public function getOpenEndedAnswerByQuestionAnswer(
        QuestionAnswer $questionAnswer,
        $executeQuery = true
    ) {
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
    ) {
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
    ) {
        $answers = $this->openEndedQuestionAnswerRepo->findAnswersBySurveyAndQuestion(
            $survey,
            $question,
            $executeQuery
        );

        return $executeQuery ?
            $this->pagerFactory->createPagerFromArray($answers, $page, $max) :
            $this->pagerFactory->createPager($answers, $page, $max);
    }

    public function getOpenEndedAnswersBySurveyAndQuestionWithoutPager(
        Survey $survey,
        Question $question,
        $executeQuery = true
    ) {
        return $this->openEndedQuestionAnswerRepo->findAnswersBySurveyAndQuestion(
            $survey,
            $question,
            $executeQuery
        );
    }

    /*
     * Access to SimpleTextQuestionAnswerRepository methods *
     */

    public function getSimpleTextAnswerByQuestionAnswer(
        QuestionAnswer $questionAnswer,
        $executeQuery = true
    ) {
        return $this->simpleTextQuestionAnswerRepo->findSimpleTextAnswerByQuestionAnswer(
            $questionAnswer,
            $executeQuery
        );
    }

    public function getSimpleTextAnswerByUserAndSurveyAndQuestion(
        User $user,
        Survey $survey,
        Question $question,
        $executeQuery = true
    ) {
        return $this->simpleTextQuestionAnswerRepo->findAnswerByUserAndSurveyAndQuestion(
            $user,
            $survey,
            $question,
            $executeQuery
        );
    }

    public function getSimpleTextAnswersBySurveyAndQuestion(
        Survey $survey,
        Question $question,
        $page = 1,
        $max = 20,
        $executeQuery = true
    ) {
        $answers = $this->simpleTextQuestionAnswerRepo->findAnswersBySurveyAndQuestion(
            $survey,
            $question,
            $executeQuery
        );

        return $executeQuery ?
            $this->pagerFactory->createPagerFromArray($answers, $page, $max) :
            $this->pagerFactory->createPager($answers, $page, $max);
    }

    public function getSimpleTextAnswersBySurveyAndQuestionWithoutPager(
        Survey $survey,
        Question $question,
        $executeQuery = true
    ) {
        return $this->simpleTextQuestionAnswerRepo->findAnswersBySurveyAndQuestion(
            $survey,
            $question,
            $executeQuery
        );
    }

    /************************************************************
     * Access to MultipleChoiceQuestionAnswerRepository methods *
     ************************************************************/

    public function deleteMultipleChoiceAnswersByQuestionAnswer(
        QuestionAnswer $questionAnswer,
        $executeQuery = true
    ) {
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
    ) {
        return $this->multipleChoiceQuestionAnswerRepo
            ->findAnswersByUserAndSurveyAndQuestion(
                $user,
                $survey,
                $question,
                $executeQuery
            );
    }

    public function getMultipleChoiceAnswersBySurveyAndQuestionWithoutPager(
        Survey $survey,
        Question $question,
        $executeQuery = true
    ) {
        return $this->multipleChoiceQuestionAnswerRepo->findAnswersBySurveyAndQuestion(
            $survey,
            $question,
            $executeQuery
        );
    }

    public function countMultipleChoiceAnswersBySurveyAndChoice(
        Survey $survey,
        Choice $choice,
        $executeQuery = true
    ) {
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
    ) {
        $answers = $this->multipleChoiceQuestionAnswerRepo->findAnswersByChoice(
            $choice,
            $executeQuery
        );

        return $executeQuery ?
            $this->pagerFactory->createPagerFromArray($answers, $page, $max) :
            $this->pagerFactory->createPager($answers, $page, $max);
    }

    public function getMultipleChoiceAnswersByQuestionAnswer(
        QuestionAnswer $questionAnswer,
        $executeQuery = true
    ) {
        return $this->multipleChoiceQuestionAnswerRepo->findMultipleChoiceAnswersByQuestionAnswer(
            $questionAnswer,
            $executeQuery
        );
    }

    /*********************************************
     * Access to QuestionModelRepository methods *
     *********************************************/

    public function getQuestionModelsByWorkspace(
        Workspace $workspace,
        $orderedBy = 'title',
        $order = 'ASC',
        $executeQuery = true
    ) {
        return $this->questionModelRepo->findModelsByWorkspace(
            $workspace,
            $orderedBy,
            $order,
            $executeQuery
        );
    }

    public function exportSurvey(Workspace $workspace, array &$files, Survey $survey)
    {
        $questionRelations = $survey->getQuestionRelations();
        $questions = [];
        foreach ($questionRelations as $questionRelation) {
            $question = $questionRelation->getQuestion();
            $question_type = $question->getType();

            $multiple_choices = [];
            if ('multiple_choice_single' === $question_type || 'multiple_choice_multiple' === $question_type) {
                $multipleChoiceQuestion = $this->getMultipleChoiceQuestionByQuestion($question);

                $multiple_choices['horizontal'] = $multipleChoiceQuestion->getHorizontal();

                $choices = $multipleChoiceQuestion->getChoices();
                foreach ($choices as $choice) {
                    $multiple_choices['choices'][] = [
                        'contentPath' => $this->makeFile($choice->getContent(), $files),
                        'other' => $choice->isOther(),
                    ];
                }
            }

            $questions[] = [
                'title' => $question->getTitle(),
                'questionPath' => $this->makeFile($question->getQuestion(), $files),
                'type' => $question->getType(),
                'multiple_choices' => $multiple_choices,
                'commentAllowed' => $question->isCommentAllowed(),
                'commentLabelPath' => $this->makeFile($question->getCommentLabel(), $files),
                'richText' => $question->isRichText(),
                'questionOrder' => $questionRelation->getQuestionOrder(),
                'mandatory' => $questionRelation->getMandatory(),
            ];
        }

        return [
            'descriptionPath' => $this->makeFile($survey->getDescription(), $files),
            'questions' => $questions,
            'published' => $survey->isPublished(),
            'closed' => $survey->isClosed(),
            'hasPublicResult' => $survey->getHasPublicResult(),
            'allowAnswerEdition' => $survey->getAllowAnswerEdition(),
            'startDate' => $survey->getStartDate() ? $survey->getStartDate()->format('Y-m-d H:i:s') : null,
            'endDate' => $survey->getEndDate() ? $survey->getEndDate()->format('Y-m-d H:i:s') : null,
        ];
    }

    public function importSurvey(array $data, $rootPath, $loggedUser, $workspace)
    {
        $survey = new Survey();
        if (isset($data['data'])) {
            $surveyData = $data['data'];

            $survey->setDescription($this->getFromFile($surveyData['descriptionPath'], $rootPath));
            $survey->setPublished($surveyData['published']);
            $survey->setClosed($surveyData['closed']);
            $survey->setHasPublicResult($surveyData['hasPublicResult']);
            $survey->setAllowAnswerEdition($surveyData['allowAnswerEdition']);
            if (null !== $surveyData['startDate']) {
                $survey->setStartDate(new \DateTime($surveyData['startDate']));
            }
            if (null !== $surveyData['endDate']) {
                $survey->setEndDate(new \DateTime($surveyData['endDate']));
            }

            $questionRelations = new ArrayCollection();
            foreach ($surveyData['questions'] as $questionData) {
                $question = new Question();
                $question->setTitle($questionData['title']);
                $question->setQuestion($this->getFromFile($questionData['questionPath'], $rootPath));
                $question->setType($questionData['type']);
                $question->setWorkspace($workspace);
                $question->setCommentAllowed($questionData['commentAllowed']);
                $question->setCommentLabel($this->getFromFile($questionData['commentLabelPath'], $rootPath));
                $question->setRichText($questionData['richText']);

                $this->om->persist($question);

                if ('multiple_choice_single' === $questionData['type'] || 'multiple_choice_multiple' === $questionData['type']) {
                    $multipleQuestion = new MultipleChoiceQuestion();
                    $multipleQuestion->setHorizontal($questionData['multiple_choices']['horizontal']);
                    $multipleQuestion->setQuestion($question);
                    $this->om->persist($multipleQuestion);

                    $choices = new ArrayCollection();
                    foreach ($questionData['multiple_choices']['choices'] as $choiceData) {
                        $choice = new Choice();
                        $choice->setContent($this->getFromFile($choiceData['contentPath'], $rootPath));
                        $choice->setOther($choiceData['other']);
                        $choice->setChoiceQuestion($multipleQuestion);
                        $this->om->persist($choice);

                        $choices->add($choice);
                    }
                }

                $relation = new SurveyQuestionRelation();
                $relation->setSurvey($survey);
                $relation->setQuestion($question);
                $relation->setQuestionOrder($questionData['questionOrder']);
                $relation->setMandatory($questionData['mandatory']);
                $this->om->persist($relation);

                $questionRelations->add($relation);
            }
            $survey->setQuestionRelations($questionRelations);
        }

        return $survey;
    }

    private function makeFile($content, &$files)
    {
        $uid = uniqid().'.txt';
        $tmpPath = sys_get_temp_dir().DIRECTORY_SEPARATOR.$uid;
        file_put_contents($tmpPath, $content);
        $files[$uid] = $tmpPath;

        return $uid;
    }

    private function getFromFile($filePath, $rootPath)
    {
        return file_get_contents($rootPath.DIRECTORY_SEPARATOR.$filePath);
    }
}
