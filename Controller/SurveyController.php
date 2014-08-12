<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\SurveyBundle\Controller;

use Claroline\CoreBundle\Library\Resource\ResourceCollection;
use Claroline\SurveyBundle\Entity\Question;
use Claroline\SurveyBundle\Entity\Survey;
use Claroline\SurveyBundle\Form\QuestionType;
use Claroline\SurveyBundle\Form\SurveyEditionType;
use Claroline\SurveyBundle\Manager\SurveyManager;
use JMS\DiExtraBundle\Annotation as DI;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Bundle\TwigBundle\TwigEngine;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\SecurityContextInterface;

class SurveyController extends Controller
{
    private $formFactory;
    private $request;
    private $router;
    private $security;
    private $surveyManager;
    private $templating;

    /**
     * @DI\InjectParams({
     *     "formFactory"   = @DI\Inject("form.factory"),
     *     "requestStack"  = @DI\Inject("request_stack"),
     *     "router"        = @DI\Inject("router"),
     *     "security"      = @DI\Inject("security.context"),
     *     "surveyManager" = @DI\Inject("claroline.manager.survey_manager"),
     *     "templating"    = @DI\Inject("templating")
     * })
     */
    public function __construct(
        FormFactory $formFactory,
        RequestStack $requestStack,
        UrlGeneratorInterface $router,
        SecurityContextInterface $security,
        SurveyManager $surveyManager,
        TwigEngine $templating
    )
    {
        $this->formFactory = $formFactory;
        $this->request = $requestStack;
        $this->router = $router;
        $this->security = $security;
        $this->surveyManager = $surveyManager;
        $this->templating = $templating;
    }

    /**
     * @EXT\Route(
     *     "/{survey}",
     *     name="claro_survey_index"
     * )
     * @EXT\Template()
     *
     * @param Survey $survey
     * @return array
     */
    public function indexAction(Survey $survey)
    {
        $this->checkSurveyRight($survey, 'OPEN');
        $canEdit = $this->hasSurveyRight($survey, 'EDIT');

        if ($canEdit) {

            return new Response(
                $this->templating->render(
                    "ClarolineSurveyBundle:Survey:surveyEditionMainMenu.html.twig",
                    array('survey' => $survey)
                )
            );
        }

        return array('survey' => $survey);
    }

    /**
     * @EXT\Route(
     *     "/survey/{survey}/parameters/edit/form",
     *     name="claro_survey_parameters_edit_form"
     * )
     * @EXT\Template()
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function surveyParametersEditFormAction(Survey $survey)
    {
        $this->checkSurveyRight($survey, 'EDIT');
        $form = $this->formFactory->create(
            new SurveyEditionType(),
            $survey
        );

        return array(
            'form' => $form->createView(),
            'survey' => $survey
        );
    }

    /**
     * @EXT\Route(
     *     "/survey/{survey}/parameters/edit",
     *     name="claro_survey_parameters_edit"
     * )
     * @EXT\Template(
     *     "ClarolineSurveyBundle:Survey:surveyParametersEditForm.html.twig"
     * )
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function surveyParametersEditAction(Survey $survey)
    {
        $this->checkSurveyRight($survey, 'EDIT');
        $form = $this->formFactory->create(
            new SurveyEditionType(),
            $survey
        );
        $form->handleRequest($this->request->getCurrentRequest());

        if ($form->isValid()) {
            $this->surveyManager->persistSurvey($survey);

            return new RedirectResponse(
                $this->router->generate(
                    'claro_survey_index',
                    array('survey' => $survey->getId())
                )
            );
        }

        return array(
            'form' => $form->createView(),
            'survey' => $survey
        );
    }

    /**
     * @EXT\Route(
     *     "/survey/{survey}/management",
     *     name="claro_survey_management"
     * )
     * @EXT\Template()
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function surveyManagementAction(Survey $survey)
    {
        $this->checkSurveyRight($survey, 'EDIT');
        $questionRelations = $survey->getQuestionRelations();

        return array(
            'survey' => $survey,
            'questionRelations' => $questionRelations
        );
    }

    /**
     * @EXT\Route(
     *     "/survey/{survey}/display",
     *     name="claro_survey_display",
     *     options={"expose"=true}
     * )
     * @EXT\Template()
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function surveyDisplayAction(Survey $survey)
    {
        $this->checkSurveyRight($survey, 'EDIT');
        $questionViews = array();

        foreach ($survey->getQuestionRelations() as $relation) {
            $question = $relation->getQuestion();
            $questionViews[] =
                $this->typedQuestionDisplayAction($survey, $question)->getContent();
        }

        return array(
            'survey' => $survey,
            'questionRelations' => $survey->getQuestionRelations(),
            'questionViews' => $questionViews
        );
    }

    /**
     * @EXT\Route(
     *     "/survey/{survey}/questions/management/ordered/by/{orderedBy}/order/{order}",
     *     name="claro_survey_questions_management",
     *     defaults={"ordered"="title","order"="ASC"}
     * )
     * @EXT\Template()
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function questionsManagementAction(Survey $survey, $orderedBy, $order)
    {
        $this->checkSurveyRight($survey, 'EDIT');
        $questions = $this->surveyManager->getQuestionsByWorkspace(
            $survey->getResourceNode()->getWorkspace(),
            $orderedBy,
            $order
        );

        return array(
            'survey' => $survey,
            'questions' => $questions,
            'orderedBy' => $orderedBy,
            'order' => $order
        );
    }

    /**
     * @EXT\Route(
     *     "/survey/{survey}/questions/list",
     *     name="claro_survey_questions_list",
     *     options={"expose"=true}
     * )
     * @EXT\Template()
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function questionsListAction(Survey $survey)
    {
        $this->checkSurveyRight($survey, 'EDIT');
        $relations = $survey->getQuestionRelations();
        $exclusions = array();

        foreach ($relations as $relation) {
            $exclusions[] = $relation->getQuestion()->getId();
        }

        $questions = $this->surveyManager->getAvailableQuestions(
            $survey->getResourceNode()->getWorkspace(),
            $exclusions,
            'title',
            'ASC'
        );

        return array(
            'survey' => $survey,
            'questions' => $questions
        );
    }

    /**
     * @EXT\Route(
     *     "/survey/{survey}/question/create/form/source/{source}",
     *     name="claro_survey_question_create_form",
     *     defaults={"source"="question"}
     * )
     * @EXT\Template()
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function questionCreateFormAction(Survey $survey, $source)
    {
        $this->checkSurveyRight($survey, 'EDIT');
        $form = $this->formFactory->create(
            new QuestionType(),
            new Question()
        );

        return array(
            'form' => $form->createView(),
            'survey' => $survey,
            'source' => $source
        );
    }

    /**
     * @EXT\Route(
     *     "/survey/{survey}/question/create/source/{source}",
     *     name="claro_survey_question_create",
     *     defaults={"source"="question"}
     * )
     * @EXT\Template(
     *     "ClarolineSurveyBundle:Survey:questionCreateForm.html.twig"
     * )
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function questionCreateAction(Survey $survey, $source)
    {
        $this->checkSurveyRight($survey, 'EDIT');
        $question  = new Question();
        $form = $this->formFactory->create(
            new QuestionType(),
            $question
        );
        $form->handleRequest($this->request->getCurrentRequest());

        if ($form->isValid()) {
            $question->setWorkspace($survey->getResourceNode()->getWorkspace());
            $this->surveyManager->persistQuestion($question);
            $questionType = $question->getType();

            switch ($questionType) {

                case 'multiple_choice':
                    $postDatas = $this->request->getCurrentRequest()->request->all();
                    $this->updateMultipleChoiceQuestion($question, $postDatas);
                    break;
                case 'open-ended':
                default:
                    break;
            }

            if ($source === 'survey') {
                $this->surveyManager
                    ->createSurveyQuestionRelation($survey, $question);

                return new RedirectResponse(
                    $this->router->generate(
                        'claro_survey_management',
                        array('survey' => $survey->getId())
                    )
                );
            } else {

                return new RedirectResponse(
                    $this->router->generate(
                        'claro_survey_questions_management',
                        array(
                            'survey' => $survey->getId(),
                            'orderedBy' => 'title',
                            'order' => 'ASC'
                        )
                    )
                );
            }
        }

        return array(
            'form' => $form->createView(),
            'survey' => $survey,
            'source' => $source
        );
    }

    /**
     * @EXT\Route(
     *     "/survey/{survey}/question/{question}/edit/form/source/{source}",
     *     name="claro_survey_question_edit_form",
     *     defaults={"source"="question"}
     * )
     * @EXT\Template()
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function questionEditFormAction(
        Question $question,
        Survey $survey,
        $source
    )
    {
        $this->checkQuestionRight($survey, $question, 'EDIT');
        $form = $this->formFactory->create(
            new QuestionType(),
            $question
        );

        return array(
            'form' => $form->createView(),
            'question' => $question,
            'survey' => $survey,
            'source' => $source
        );
    }

    /**
     * @EXT\Route(
     *     "/survey/{survey}/question/{question}/edit/source/{source}",
     *     name="claro_survey_question_edit",
     *     defaults={"source"="question"}
     * )
     * @EXT\Template(
     *     "ClarolineSurveyBundle:Survey:questionEditForm.html.twig"
     * )
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function questionEditAction(
        Question $question,
        Survey $survey,
        $source
    )
    {
        $this->checkQuestionRight($survey, $question, 'EDIT');
        $form = $this->formFactory->create(
            new QuestionType(),
            $question
        );
        $form->handleRequest($this->request->getCurrentRequest());

        if ($form->isValid()) {
            $question->setWorkspace($survey->getResourceNode()->getWorkspace());
            $this->surveyManager->persistQuestion($question);
            $questionType = $question->getType();

            switch ($questionType) {

                case 'multiple_choice':
                    $postDatas = $this->request->getCurrentRequest()->request->all();
                    $this->updateMultipleChoiceQuestion($question, $postDatas);
                    break;
                case 'open-ended':
                default:
                    break;
            }

            if ($source === 'survey') {

                return new RedirectResponse(
                    $this->router->generate(
                        'claro_survey_management',
                        array('survey' => $survey->getId())
                    )
                );
            } else {

                return new RedirectResponse(
                    $this->router->generate(
                        'claro_survey_questions_management',
                        array(
                            'survey' => $survey->getId(),
                            'orderedBy' => 'title',
                            'order' => 'ASC'
                        )
                    )
                );
                }
        }

        return array(
            'form' => $form->createView(),
            'question' => $question,
            'survey' => $survey,
            'source' => $source
        );
    }

    /**
     * @EXT\Route(
     *     "/survey/{survey}/question/{question}/delete",
     *     name="claro_survey_question_delete",
     *     options={"expose"=true}
     * )
     * @EXT\Template(
     *     "ClarolineSurveyBundle:Survey:questionsManagement.html.twig"
     * )
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function questionDeleteAction(Question $question, Survey $survey)
    {
        $this->checkQuestionRight($survey, $question, 'EDIT');
        $this->surveyManager->deleteQuestion($question);

        return new RedirectResponse(
            $this->router->generate(
                'claro_survey_questions_management',
                array(
                    'survey' => $survey->getId(),
                    'orderedBy' => 'title',
                    'order' => 'ASC'
                )
            )
        );
    }

    /**
     * @EXT\Route(
     *     "/survey/{survey}/add/question/{question}",
     *     name="claro_survey_add_question",
     *     options={"expose"=true}
     * )
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function surveyAddQuestionAction(Survey $survey, Question $question)
    {
        $this->checkQuestionRight($survey, $question, 'EDIT');
        $this->surveyManager->createSurveyQuestionRelation($survey, $question);

        return new RedirectResponse(
            $this->router->generate(
                'claro_survey_management',
                array('survey' => $survey->getId())
            )
        );
    }

    /**
     * @EXT\Route(
     *     "/survey/{survey}/remove/question/{question}",
     *     name="claro_survey_remove_question",
     *     options={"expose"=true}
     * )
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function surveyRemoveQuestionAction(Survey $survey, Question $question)
    {
        $this->checkQuestionRight($survey, $question, 'EDIT');
        $this->surveyManager->deleteSurveyQuestionRelation($survey, $question);

        return new RedirectResponse(
            $this->router->generate(
                'claro_survey_management',
                array('survey' => $survey->getId())
            )
        );
    }

    /**
     * @EXT\Route(
     *     "/survey/{survey}/typed/question/{question}/display",
     *     name="claro_survey_typed_question_display",
     *     options={"expose"=true}
     * )
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function typedQuestionDisplayAction(Survey $survey, Question $question)
    {
        $this->checkQuestionRight($survey, $question, 'EDIT');
        $questionType = $question->getType();

        switch ($questionType) {

            case 'multiple_choice' :

                return $this->displayMultipleChoiceQuestion($question);
            case 'open_ended':

                return $this->displayOpenEndedQuestion($question);
            default:
                break;
        }

        return new Response();
    }

    /**
     * @EXT\Route(
     *     "/survey/{survey}/type/{questionType}/create/form",
     *     name="claro_survey_typed_question_create_form",
     *     options={"expose"=true}
     * )
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function typedQuestionCreateFormAction(
        Survey $survey,
        $questionType
    )
    {
        $this->checkSurveyRight($survey, 'EDIT');

        switch ($questionType) {

            case 'multiple_choice':

                return $this->multipleChoiceQuestionForm($survey);
            case 'open_ended':
            default:
                break;
        }
    }

    /**
     * @EXT\Route(
     *     "/survey/{survey}/question/{question}/type/{questionType}/edit/form",
     *     name="claro_survey_typed_question_edit_form",
     *     options={"expose"=true}
     * )
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function typedQuestionEditFormAction(
        Question $question,
        Survey $survey,
        $questionType
    )
    {
        $this->checkQuestionRight($survey, $question, 'EDIT');

        switch ($questionType) {

            case 'multiple_choice':

                return $this->multipleChoiceQuestionForm(
                    $survey,
                    $question
                );
            case 'open_ended':
            default:
                break;
        }
    }

    private function multipleChoiceQuestionForm(
        Survey $survey,
        Question $question = null
    )
    {
        $multipleChoiceQuestion = is_null($question) ?
            null :
            $this->surveyManager->getMultipleChoiceQuestionByQuestion($question);
        $choices = array();
        $allowMultipleResponse = false;

        if (!is_null($multipleChoiceQuestion)) {
            $choices = $multipleChoiceQuestion->getChoices();
            $allowMultipleResponse =
                $multipleChoiceQuestion->getAllowMultipleResponse();
        }

        return new Response(
            $this->templating->render(
                "ClarolineSurveyBundle:Survey:multipleChoiceQuestionForm.html.twig",
                array(
                    'survey' => $survey,
                    'choices' => $choices,
                    'allowMultipleResponse' => $allowMultipleResponse
                )
            )
        );
    }

    private function displayMultipleChoiceQuestion(Question $question)
    {
        $multipleChoiceQuestion = $this->surveyManager
            ->getMultipleChoiceQuestionByQuestion($question);

        if (is_null($multipleChoiceQuestion)) {

            throw new \Exception('Cannot find multiple choice question');
        }

        $choices = $multipleChoiceQuestion->getChoices();
        $allowMultipleResponse =
            $multipleChoiceQuestion->getAllowMultipleResponse();

        return new Response(
            $this->templating->render(
                "ClarolineSurveyBundle:Survey:displayMultipleChoiceQuestion.html.twig",
                array(
                    'question' => $question,
                    'choices' => $choices,
                    'allowMultipleResponse' => $allowMultipleResponse
                )
            )
        );
    }

    private function displayOpenEndedQuestion(Question $question)
    {
        return new Response(
            $this->templating->render(
                "ClarolineSurveyBundle:Survey:displayOpenEndedQuestion.html.twig",
                array('question' => $question)
            )
        );
    }

    private function updateMultipleChoiceQuestion(
        Question $question,
        array $datas
    )
    {
        $multipleResponse = isset($datas['allow-multiple-response']) &&
            ($datas['allow-multiple-response'] === 'on');
        $choices = isset($datas['choice']) ?
            $datas['choice'] :
            array();

        $multipleChoiceQuestion = $this->surveyManager
            ->getMultipleChoiceQuestionByQuestion($question);

        if (is_null($multipleChoiceQuestion)) {
            $this->surveyManager->createMultipleChoiceQuestion(
                $question,
                $choices,
                $multipleResponse
            );
        } else {
            $this->surveyManager->updateQuestionChoices(
                $multipleChoiceQuestion,
                $choices,
                $multipleResponse
            );
        }
    }

    private function checkSurveyRight(Survey $survey, $right)
    {
        $collection = new ResourceCollection(array($survey->getResourceNode()));

        if (!$this->security->isGranted($right, $collection)) {
            
            throw new AccessDeniedException($collection->getErrorsForDisplay());
        }
    }

    private function hasSurveyRight(Survey $survey, $right)
    {
        $collection = new ResourceCollection(array($survey->getResourceNode()));

        return $this->security->isGranted($right, $collection);
    }

    private function checkQuestionRight(Survey $survey, Question $question, $right)
    {
        $this->checkSurveyRight($survey, $right);
        $surveyWorkspaceId = $survey->getResourceNode()->getWorkspace()->getId();
        $questionWorkspaceId = $question->getWorkspace()->getId();

        if ($surveyWorkspaceId !== $questionWorkspaceId) {

            throw new AccessDeniedException();
        }
    }
}
