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
use Claroline\SurveyBundle\Entity\Answer\MultipleChoiceQuestionAnswer;
use Claroline\SurveyBundle\Entity\Answer\OpenEndedQuestionAnswer;
use Claroline\SurveyBundle\Entity\Answer\QuestionAnswer;
use Claroline\SurveyBundle\Entity\Answer\SurveyAnswer;
use Claroline\SurveyBundle\Entity\Choice;
use Claroline\SurveyBundle\Entity\Question;
use Claroline\SurveyBundle\Entity\QuestionModel;
use Claroline\SurveyBundle\Entity\Survey;
use Claroline\SurveyBundle\Entity\SurveyQuestionRelation;
use Claroline\SurveyBundle\Event\Log\LogSurveyAnswer;
use Claroline\SurveyBundle\Form\QuestionTitleType;
use Claroline\SurveyBundle\Form\QuestionType;
use Claroline\SurveyBundle\Form\SurveyEditionType;
use Claroline\SurveyBundle\Manager\SurveyManager;
use JMS\DiExtraBundle\Annotation as DI;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Bundle\TwigBundle\TwigEngine;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Translation\TranslatorInterface;

class SurveyController extends Controller
{
    private $eventDispatcher;
    private $formFactory;
    private $request;
    private $router;
    private $authorization;
    private $surveyManager;
    private $templating;
    private $tokenStorage;
    private $translator;

    /**
     * @DI\InjectParams({
     *     "eventDispatcher" = @DI\Inject("event_dispatcher"),
     *     "formFactory"     = @DI\Inject("form.factory"),
     *     "httpKernel"      = @DI\Inject("http_kernel"),
     *     "requestStack"    = @DI\Inject("request_stack"),
     *     "router"          = @DI\Inject("router"),
     *     "authorization"   = @DI\Inject("security.authorization_checker"),
     *     "surveyManager"   = @DI\Inject("claroline.manager.survey_manager"),
     *     "templating"      = @DI\Inject("templating"),
     *     "tokenStorage"    = @DI\Inject("security.token_storage"),
     *     "translator"      = @DI\Inject("translator")
     * })
     */
    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        FormFactory $formFactory,
        HttpKernelInterface $httpKernel,
        RequestStack $requestStack,
        UrlGeneratorInterface $router,
        AuthorizationCheckerInterface $authorization,
        SurveyManager $surveyManager,
        TwigEngine $templating,
        TokenStorageInterface $tokenStorage,
        TranslatorInterface $translator
    )
    {
        $this->eventDispatcher = $eventDispatcher;
        $this->formFactory = $formFactory;
        $this->httpKernel = $httpKernel;
        $this->request = $requestStack;
        $this->router = $router;
        $this->authorization = $authorization;
        $this->surveyManager = $surveyManager;
        $this->templating = $templating;
        $this->tokenStorage = $tokenStorage;
        $this->translator = $translator;
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
        $user = $this->tokenStorage->getToken()->getUser();
        $isAnon = ($user === 'anon.');
        $canEdit = $this->hasSurveyRight($survey, 'EDIT');
        $this->surveyManager->updateSurveyStatus($survey);
        $status = $this->computeStatus($survey);

        if ($isAnon) {
            $hasAnswered = false;
        } else {
            $surveyAnswer = $this->surveyManager
                ->getSurveyAnswerBySurveyAndUser($survey, $user);
            $hasAnswered = !is_null($surveyAnswer);

            if ($canEdit) {
                $params = array();
                $params['_controller'] = 'ClarolineSurveyBundle:Survey:surveyManagement';
                $params['survey'] = $survey;
                $subRequest = $this->request
                    ->getCurrentRequest()
                    ->duplicate(array(), null, $params);
                $response = $this->httpKernel
                    ->handle($subRequest, HttpKernelInterface::SUB_REQUEST);

                return $response;
            }
        }
        $currentDate = new \DateTime();

        return array(
            'survey' => $survey,
            'status' => $status,
            'currentDate' => $currentDate,
            'hasAnswered' => $hasAnswered,
            'isAnon' => $isAnon
        );
    }

    /**
     * @EXT\Route(
     *     "/survey/{survey}/parameters",
     *     name="claro_survey_parameters"
     * )
     * @EXT\Template()
     *
     * @param Survey $survey
     * @return array
     */
    public function surveyEditionMainMenuAction(Survey $survey)
    {
        $this->checkSurveyRight($survey, 'EDIT');
        $this->surveyManager->updateSurveyStatus($survey);
        $status = $this->computeStatus($survey);

        return array(
            'survey' => $survey,
            'status' => $status
        );
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
     * @EXT\Template( "ClarolineSurveyBundle:Survey:surveyParametersEditForm.html.twig")
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
                    'claro_survey_parameters',
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
        $questionRelations = $this->surveyManager
            ->getQuestionRelationsBySurvey($survey);
        $status = $this->computeStatus($survey);
        $questionResult = null;

        foreach ($questionRelations as $relation) {
            $question = $relation->getQuestion();

            if ($question->getType() !== 'title') {
                $questionResult = $question;
                break;
            }
        }

        return array(
            'survey' => $survey,
            'questionRelations' => $questionRelations,
            'status' => $status,
            'questionResult' => $questionResult
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
        $questionRelations = $this->surveyManager
            ->getQuestionRelationsBySurvey($survey);

        foreach ($questionRelations as $relation) {
            $question = $relation->getQuestion();

            if ($question->getType() !== 'title') {
                $questionViews[] =
                    $this->typedQuestionDisplayAction($survey, $question)->getContent();
            } else {
                $questionViews[] = $this->templating->render(
                    "ClarolineSurveyBundle:Survey:titleQuestionDisplay.html.twig",
                    array('question' => $question)
                );
            }
        }

        return array(
            'survey' => $survey,
            'questionRelations' => $questionRelations,
            'questionViews' => $questionViews
        );
    }

    /**
     * @EXT\Route(
     *     "/survey/{survey}/publish",
     *     name="claro_survey_publish"
     * )
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function surveyPublishAction(Survey $survey)
    {
        $this->checkSurveyRight($survey, 'EDIT');

        if (!$survey->isPublished() || $survey->isClosed()) {
            $survey->setClosed(false);
            $survey->setPublished(true);
            $this->surveyManager->persistSurvey($survey);
        }

        return new RedirectResponse(
            $this->router->generate(
                'claro_survey_parameters',
                array('survey' => $survey->getId())
            )
        );
    }

    /**
     * @EXT\Route(
     *     "/survey/{survey}/close",
     *     name="claro_survey_close"
     * )
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function surveyCloseAction(Survey $survey)
    {
        $this->checkSurveyRight($survey, 'EDIT');

        if (!$survey->isClosed()) {
            $survey->setClosed(true);
            $this->surveyManager->persistSurvey($survey);
        }

        return new RedirectResponse(
            $this->router->generate(
                'claro_survey_parameters',
                array('survey' => $survey->getId())
            )
        );
    }

    /**
     * @EXT\Route(
     *     "/survey/{survey}/questions/management/ordered/by/{orderedBy}/order/{order}/page/{page}/max/{max}",
     *     name="claro_survey_questions_management",
     *     defaults={"ordered"="title","order"="ASC","page"=1,"max"=20}
     * )
     * @EXT\Template()
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function questionsManagementAction(
        Survey $survey,
        $orderedBy,
        $order,
        $page = 1,
        $max = 20
    )
    {
        $this->checkSurveyRight($survey, 'EDIT');
        $questions = $this->surveyManager->getQuestionsByWorkspace(
            $survey->getResourceNode()->getWorkspace(),
            $orderedBy,
            $order,
            $page,
            $max
        );

        return array(
            'survey' => $survey,
            'questions' => $questions,
            'orderedBy' => $orderedBy,
            'order' => $order,
            'max' => $max
        );
    }

    /**
     * @EXT\Route(
     *     "/survey/{survey}/models/management/ordered/by/{orderedBy}/order/{order}",
     *     name="claro_survey_models_management",
     *     defaults={"ordered"="title","order"="ASC"}
     * )
     * @EXT\Template()
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function modelsManagementAction(Survey $survey, $orderedBy, $order)
    {
        $this->checkSurveyRight($survey, 'EDIT');
        $models = $this->surveyManager->getQuestionModelsByWorkspace(
            $survey->getResourceNode()->getWorkspace(),
            $orderedBy,
            $order
        );

        return array(
            'survey' => $survey,
            'models' => $models,
            'orderedBy' => $orderedBy,
            'order' => $order
        );
    }

    /**
     * @EXT\Route(
     *     "/survey/{survey}/model/{model}/delete",
     *     name="claro_survey_model_delete",
     *     options={"expose"=true}
     * )
     * @EXT\Template("ClarolineSurveyBundle:Survey:modelsManagement.html.twig")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function modelDeleteAction(QuestionModel $model, Survey $survey)
    {
        $canEdit = $this->hasSurveyRight($survey, 'EDIT');
        $workspaceIdA = $survey->getResourceNode()->getWorkspace()->getId();
        $workspaceIdB = $model->getWorkspace()->getId();

        if (!$canEdit || ($workspaceIdA !== $workspaceIdB)) {

            throw new AccessDeniedException();
        }
        $this->surveyManager->deleteQuestionModel($model);
        $models = $this->surveyManager->getQuestionModelsByWorkspace(
            $survey->getResourceNode()->getWorkspace()
        );

        return new RedirectResponse(
            $this->router->generate(
                'claro_survey_models_management',
                array(
                    'survey' => $survey->getId(),
                    'models' => $models,
                    'orderedBy' => 'title',
                    'order' => 'ASC'
                )
            )
        );
    }

    /**
     * @EXT\Route(
     *     "/survey/{survey}/questions/list/ordered/by/{orderedBy}/order/{order}/page/{page}/max/{max}",
     *     name="claro_survey_questions_list",
     *     defaults={"ordered"="title","order"="ASC","page"=1,"max"=20},
     *     options={"expose"=true}
     * )
     * @EXT\Template()
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function questionsListAction(
        Survey $survey,
        $orderedBy = 'title',
        $order = 'ASC',
        $page = 1,
        $max = 20
    )
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
            $orderedBy,
            $order,
            $page,
            $max
        );

        return array(
            'survey' => $survey,
            'questions' => $questions,
            'orderedBy' => $orderedBy,
            'order' => $order,
            'max' => $max
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
        $models = $this->surveyManager->getQuestionModelsByWorkspace(
            $survey->getResourceNode()->getWorkspace()
        );

        return array(
            'form' => $form->createView(),
            'survey' => $survey,
            'source' => $source,
            'models' => $models
        );
    }

    /**
     * @EXT\Route(
     *     "/survey/{survey}/question/create/source/{source}",
     *     name="claro_survey_question_create",
     *     defaults={"source"="question"}
     * )
     * @EXT\Template("ClarolineSurveyBundle:Survey:questionCreateForm.html.twig")
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

                case 'multiple_choice_single':
                case 'multiple_choice_multiple':
                    $postDatas = $this->request->getCurrentRequest()->request->all();

                    $this->updateMultipleChoiceQuestion($question, $postDatas);

                    if (isset($postDatas['model'])) {
                        $this->surveyManager->createQuestionModel($question);
                    }
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
        $models = $this->surveyManager->getQuestionModelsByWorkspace(
            $survey->getResourceNode()->getWorkspace()
        );

        return array(
            'form' => $form->createView(),
            'survey' => $survey,
            'source' => $source,
            'models' => $models
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
        $models = $this->surveyManager->getQuestionModelsByWorkspace(
            $survey->getResourceNode()->getWorkspace()
        );

        return array(
            'form' => $form->createView(),
            'question' => $question,
            'survey' => $survey,
            'source' => $source,
            'models' => $models
        );
    }

    /**
     * @EXT\Route(
     *     "/survey/{survey}/question/{question}/edit/source/{source}",
     *     name="claro_survey_question_edit",
     *     defaults={"source"="question"}
     * )
     * @EXT\Template("ClarolineSurveyBundle:Survey:questionEditForm.html.twig")
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

                case 'multiple_choice_single':
                case 'multiple_choice_multiple':
                    $postDatas = $this->request->getCurrentRequest()->request->all();
                    $this->updateMultipleChoiceQuestion($question, $postDatas);

                    if (isset($postDatas['model'])) {
                        $this->surveyManager->createQuestionModel($question);
                    }
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
        $models = $this->surveyManager->getQuestionModelsByWorkspace(
            $survey->getResourceNode()->getWorkspace()
        );

        return array(
            'form' => $form->createView(),
            'question' => $question,
            'survey' => $survey,
            'source' => $source,
            'models' => $models
        );
    }

    /**
     * @EXT\Route(
     *     "/survey/{survey}/question/{question}/delete",
     *     name="claro_survey_question_delete",
     *     options={"expose"=true}
     * )
     * @EXT\Template("ClarolineSurveyBundle:Survey:questionsManagement.html.twig")
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
     *     "/survey/{survey}/question/title/create/form",
     *     name="claro_survey_question_title_create_form"
     * )
     * @EXT\Template()
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function questionTitleCreateFormAction(Survey $survey)
    {
        $this->checkSurveyRight($survey, 'EDIT');
        $form = $this->formFactory->create(
            new QuestionTitleType(),
            new Question()
        );

        return array(
            'form' => $form->createView(),
            'survey' => $survey
        );
    }

    /**
     * @EXT\Route(
     *     "/survey/{survey}/question/title/create",
     *     name="claro_survey_question_title_create"
     * )
     * @EXT\Template("ClarolineSurveyBundle:Survey:questionTitleCreateForm.html.twig")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function questionTitleCreateAction(Survey $survey)
    {
        $this->checkSurveyRight($survey, 'EDIT');
        $question  = new Question();
        $question->setTitle('TITLE');
        $form = $this->formFactory->create(
            new QuestionTitleType(),
            $question
        );
        $form->handleRequest($this->request->getCurrentRequest());

        if ($form->isValid()) {
            $question->setType('title');
            $question->setWorkspace($survey->getResourceNode()->getWorkspace());
            $question->setCommentAllowed(false);
            $this->surveyManager->persistQuestion($question);
            $this->surveyManager
                ->createSurveyQuestionRelation($survey, $question);

            return new RedirectResponse(
                $this->router->generate(
                    'claro_survey_management',
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
     *     "/survey/{survey}/question/{question}/title/edit/form",
     *     name="claro_survey_question_title_edit_form"
     * )
     * @EXT\Template()
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function questionTitleEditFormAction(Question $question, Survey $survey)
    {
        $this->checkQuestionRight($survey, $question, 'EDIT');
        $form = $this->formFactory->create(
            new QuestionTitleType(),
            $question
        );

        return array(
            'form' => $form->createView(),
            'question' => $question,
            'survey' => $survey
        );
    }

    /**
     * @EXT\Route(
     *     "/survey/{survey}/question/{question}/title/edit",
     *     name="claro_survey_question_title_edit"
     * )
     * @EXT\Template("ClarolineSurveyBundle:Survey:questionTitleEditForm.html.twig")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function questionTitleEditAction(Question $question, Survey $survey)
    {
        $this->checkQuestionRight($survey, $question, 'EDIT');
        $form = $this->formFactory->create(
            new QuestionTitleType(),
            $question
        );
        $form->handleRequest($this->request->getCurrentRequest());

        if ($form->isValid()) {
            $this->surveyManager->persistQuestion($question);

            return new RedirectResponse(
                $this->router->generate(
                    'claro_survey_management',
                    array('survey' => $survey->getId())
                )
            );
        }

        return array(
            'form' => $form->createView(),
            'question' => $question,
            'survey' => $survey
        );
    }

    /**
     * @EXT\Route(
     *     "/survey/{survey}/question/{question}/title/delete",
     *     name="claro_survey_question_title_delete"
     * )
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function questionTitleDeleteAction(Question $question, Survey $survey)
    {
        $this->checkQuestionRight($survey, $question, 'EDIT');

        if ($question->getType() === 'title') {
            $this->surveyManager->deleteQuestion($question);
        }

        return new RedirectResponse(
            $this->router->generate(
                'claro_survey_management',
                array('survey' => $survey->getId())
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
        $this->checkQuestionRight($survey, $question, 'OPEN');
        $questionType = $question->getType();

        switch ($questionType) {

            case 'multiple_choice_single' :
            case 'multiple_choice_multiple' :

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

            case 'multiple_choice_single':
            case 'multiple_choice_multiple':

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

            case 'multiple_choice_single':
            case 'multiple_choice_multiple':

                return $this->multipleChoiceQuestionForm(
                    $survey,
                    $question
                );
            case 'open_ended':
            default:
                break;
        }
    }

    /**
     * @EXT\Route(
     *     "/survey/question/relation/{relation}/switch",
     *     name="claro_survey_question_relation_mandatory_switch",
     *     options={"expose"=true}
     * )
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function surveyQuestionRelationMandatorySwitchAction(
        SurveyQuestionRelation $relation
    )
    {
        $survey = $relation->getSurvey();
        $this->checkSurveyRight($survey, 'EDIT');

        $relation->switchMandatory();
        $this->surveyManager->persistSurveyQuestionRelation($relation);
        $data = $relation->getMandatory() ? 'mandatory' : 'not_mandatory';

        return new Response($data, 200);
    }

    /**
     * @EXT\Route(
     *     "/survey/{survey}/answer/form",
     *     name="claro_survey_answer_form"
     * )
     * @EXT\Template()
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function surveyAnswerFormAction(Survey $survey)
    {
        $this->checkSurveyRight($survey, 'OPEN');
        $status = $this->computeStatus($survey);
        $user = $this->tokenStorage->getToken()->getUser();
        $isAnon = ($user === 'anon.');
        $questionViews = array();
        $errors = array();

        $surveyAnswer = $isAnon ?
            null :
            $this->surveyManager->getSurveyAnswerBySurveyAndUser($survey, $user);
        $answersDatas = array();
        $canEdit = $status === 'published' &&
            (is_null($surveyAnswer) || $survey->getAllowAnswerEdition());

        if (!is_null($surveyAnswer)) {
            $questionsAnswers = $surveyAnswer->getQuestionsAnswers();

            foreach ($questionsAnswers as $questionAnswer) {
                $question = $questionAnswer->getQuestion();
                $questionId = $question->getId();
                $answersDatas[$questionId] = array();

                if (!is_null($questionAnswer->getComment())) {
                    $answersDatas[$questionId]['comment'] = $questionAnswer->getComment();
                }

                if ($question->getType() === 'open_ended') {
                    $openEndedAnswer = $this->surveyManager
                        ->getOpenEndedAnswerByUserAndSurveyAndQuestion(
                            $user,
                            $survey,
                            $question
                        );

                    if (!is_null($openEndedAnswer)) {
                        $answersDatas[$questionId]['answer'] =
                            $openEndedAnswer->getContent();
                    }
                } elseif ($question->getType() === 'multiple_choice_single' ||
                        $question->getType() === 'multiple_choice_multiple') {

                    $choiceAnswers = $this->surveyManager
                        ->getMultipleChoiceAnswersByUserAndSurveyAndQuestion(
                            $user,
                            $survey,
                            $question
                        );

                    foreach ($choiceAnswers as $choiceAnswer) {
                        $choiceId = $choiceAnswer->getChoice()->getId();
                        $answersDatas[$questionId][$choiceId] = $choiceId;

                        if ($choiceAnswer->getChoice()->isOther()) {
                            $answersDatas[$questionId]['other'] =
                                $choiceAnswer->getContent();
                        }
                    }
                }
            }
        }
        $questionRelations = $this->surveyManager
            ->getQuestionRelationsBySurvey($survey);

        foreach ($questionRelations as $relation) {
            $question = $relation->getQuestion();

            if ($question->getType() !== 'title') {
                $questionAnswer = isset($answersDatas[$question->getId()]) ?
                    $answersDatas[$question->getId()] :
                    array();
                $questionViews[$relation->getId()] = $this->displayTypedQuestion(
                    $survey,
                    $question,
                    $questionAnswer,
                    $canEdit
                )->getContent();
            } else {
                $questionViews[$relation->getId()] = $this->templating->render(
                    "ClarolineSurveyBundle:Survey:titleQuestionDisplay.html.twig",
                    array('question' => $question)
                );
            }
        }

        return array(
            'survey' => $survey,
            'questionRelations' => $questionRelations,
            'questionViews' => $questionViews,
            'canEdit' => $canEdit,
            'errors' => $errors,
            'isAnon' => $isAnon
        );
    }

    /**
     * @EXT\Route(
     *     "/survey/{survey}/answer",
     *     name="claro_survey_answer"
     * )
     * @EXT\Template("ClarolineSurveyBundle:Survey:surveyAnswerForm.html.twig")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function surveyAnswerAction(Survey $survey)
    {
        $this->checkSurveyRight($survey, 'OPEN');
        $status = $this->computeStatus($survey);
        $user = $this->tokenStorage->getToken()->getUser();
        $isAnon = ($user === 'anon.');

        if ($status === 'published') {
            $postDatas = $this->request->getCurrentRequest()->request->all();
            $errors = $this->validateSurveyAnswer($survey, $postDatas);

            if (count($errors) > 0) {
                $surveyAnswer = $isAnon ?
                    null :
                    $this->surveyManager->getSurveyAnswerBySurveyAndUser($survey, $user);
                $canEdit = is_null($surveyAnswer) ||
                    $survey->getAllowAnswerEdition();
                $answersDatas = array();

                foreach ($postDatas as $questionId => $questionDatas) {

                    if (isset($questionDatas['comment'])) {
                        $answersDatas[$questionId]['comment'] = $questionDatas['comment'];
                    }

                    if (isset($questionDatas['answer']) &&
                        !empty($questionDatas['answer'])) {

                        $answersDatas[$questionId]['answer'] = $questionDatas['answer'];
                    }

                    if (isset($questionDatas['choice']) &&
                        !empty($questionDatas['choice'])) {

                        $choiceId = $questionDatas['choice'];
                        $answersDatas[$questionId][$choiceId] = $choiceId;
                    }

                    if (isset($questionDatas['other'])) {
                        $answersDatas[$questionId]['other'] = $questionDatas['other'];
                    }

                    foreach ($questionDatas as $key => $value) {

                        if (is_int($key)) {
                            $answersDatas[$questionId][$key] = $value;
                        }
                    }
                }
                $questionRelations = $this->surveyManager
                    ->getQuestionRelationsBySurvey($survey);

                foreach ($questionRelations as $relation) {
                    $question = $relation->getQuestion();

                    if ($question->getType() !== 'title') {
                        $answerData = isset($answersDatas[$question->getId()]) ?
                            $answersDatas[$question->getId()] :
                            array();

                        $questionViews[$relation->getId()] = $this->displayTypedQuestion(
                            $survey,
                            $question,
                            $answerData,
                            $canEdit
                        )->getContent();
                    } else {
                        $questionViews[$relation->getId()] = $this->templating->render(
                            "ClarolineSurveyBundle:Survey:titleQuestionDisplay.html.twig",
                            array('question' => $question)
                        );
                    }
                }

                return array(
                    'survey' => $survey,
                    'questionRelations' => $questionRelations,
                    'questionViews' => $questionViews,
                    'canEdit' => $canEdit,
                    'errors' => $errors,
                    'isAnon' => $isAnon
                );
            }

            $surveyAnswer = $isAnon ?
                null :
                $this->surveyManager->getSurveyAnswerBySurveyAndUser($survey, $user);
            $isNewAnswer = true;

            if (is_null($surveyAnswer)) {
                $surveyAnswer = new SurveyAnswer();
                $surveyAnswer->setSurvey($survey);

                if (!$isAnon) {
                    $surveyAnswer->setUser($user);
                }
                $surveyAnswer->setNbAnswers(1);
                $surveyAnswer->setAnswerDate(new \DateTime());
                $this->surveyManager->persistSurveyAnswer($surveyAnswer);
            } else {
                $isNewAnswer = false;
                $surveyAnswer->incrementNbAnswers();
                $this->surveyManager->persistSurveyAnswer($surveyAnswer);
            }

            foreach ($postDatas as $questionId => $questionResponse) {
                $question = $this->surveyManager->getQuestionById($questionId);

                if (!is_null($question)) {
                    $questionType = $question->getType();

                    if ($isNewAnswer) {
                        $questionAnswer = new QuestionAnswer();
                        $questionAnswer->setSurveyAnswer($surveyAnswer);
                        $questionAnswer->setQuestion($question);

                        if (isset($questionResponse['comment']) &&
                            !empty($questionResponse['comment'])) {

                            $questionAnswer->setComment($questionResponse['comment']);
                        }
                        $this->surveyManager->persistQuestionAnswer($questionAnswer);

                        if ($questionType === 'open_ended' &&
                            isset($questionResponse['answer']) &&
                            !empty($questionResponse['answer'])) {

                            $openEndedAnswer = new OpenEndedQuestionAnswer();
                            $openEndedAnswer->setQuestionAnswer($questionAnswer);
                            $openEndedAnswer->setContent($questionResponse['answer']);
                            $this->surveyManager
                                ->persistOpenEndedQuestionAnswer($openEndedAnswer);

                        } elseif ($questionType === 'multiple_choice_single' ||
                                $questionType === 'multiple_choice_multiple') {

                            $multipleChoiceQuestion = $this->surveyManager
                                ->getMultipleChoiceQuestionByQuestion($question);

                            if (!is_null($multipleChoiceQuestion)) {

                                if ($questionType === 'multiple_choice_multiple') {

                                    foreach($questionResponse as $choiceId => $response) {

                                        if ($choiceId !== 'comment' && $choiceId !== 'other') {
                                            $choice = $this->surveyManager->getChoiceById($choiceId);
                                            $choiceAnswer = new MultipleChoiceQuestionAnswer();
                                            $choiceAnswer->setQuestionAnswer($questionAnswer);
                                            $choiceAnswer->setChoice($choice);

                                            if ($choice->isOther() && isset($questionResponse['other'])) {
                                                $choiceAnswer->setContent($questionResponse['other']);
                                            }
                                            $this->surveyManager
                                                ->persistMultipleChoiceQuestionAnswer($choiceAnswer);
                                        }
                                    }
                                } elseif ($questionType === 'multiple_choice_single' &&
                                    isset($questionResponse['choice']) &&
                                    !empty($questionResponse['choice'])) {

                                    $choiceId = (int)$questionResponse['choice'];
                                    $choice = $this->surveyManager->getChoiceById($choiceId);
                                    $choiceAnswer = new MultipleChoiceQuestionAnswer();
                                    $choiceAnswer->setQuestionAnswer($questionAnswer);
                                    $choiceAnswer->setChoice($choice);

                                    if ($choice->isOther() && isset($questionResponse['other'])) {
                                        $choiceAnswer->setContent($questionResponse['other']);
                                    }
                                    $this->surveyManager
                                        ->persistMultipleChoiceQuestionAnswer($choiceAnswer);
                                }
                            }
                        }

                    } elseif ($survey->getAllowAnswerEdition()) {
                        $questionAnswer = $this->surveyManager
                            ->getQuestionAnswerBySurveyAnswerAndQuestion(
                                $surveyAnswer,
                                $question
                            );

                        if (is_null($questionAnswer)) {
                            $questionAnswer = new QuestionAnswer();
                            $questionAnswer->setSurveyAnswer($surveyAnswer);
                            $questionAnswer->setQuestion($question);
                            $this->surveyManager->persistQuestionAnswer($questionAnswer);
                        }

                        if (isset($questionResponse['comment']) &&
                            !empty($questionResponse['comment'])) {

                            $questionAnswer->setComment($questionResponse['comment']);
                            $this->surveyManager->persistQuestionAnswer($questionAnswer);
                        }

                        if ($questionType === 'open_ended' &&
                            isset($questionResponse['answer']) &&
                            !empty($questionResponse['answer'])) {

                            $openEndedAnswer = $this->surveyManager
                                ->getOpenEndedAnswerByQuestionAnswer($questionAnswer);

                            if (is_null($openEndedAnswer)) {
                                $openEndedAnswer = new OpenEndedQuestionAnswer();
                                $openEndedAnswer->setQuestionAnswer($questionAnswer);
                            }
                            $openEndedAnswer->setContent($questionResponse['answer']);
                            $this->surveyManager
                                ->persistOpenEndedQuestionAnswer($openEndedAnswer);

                        } elseif ($questionType === 'multiple_choice_single' ||
                                $questionType === 'multiple_choice_multiple') {

                            $multipleChoiceQuestion = $this->surveyManager
                                ->getMultipleChoiceQuestionByQuestion($question);

                            if (!is_null($multipleChoiceQuestion)) {

                                if ($questionType === 'multiple_choice_multiple') {

                                    $this->surveyManager
                                        ->deleteMultipleChoiceAnswersByQuestionAnswer($questionAnswer);

                                    foreach($questionResponse as $choiceId => $response) {

                                        if ($choiceId !== 'comment' && $choiceId !== 'other') {
                                            $choice = $this->surveyManager->getChoiceById($choiceId);
                                            $choiceAnswer = new MultipleChoiceQuestionAnswer();
                                            $choiceAnswer->setQuestionAnswer($questionAnswer);
                                            $choiceAnswer->setChoice($choice);

                                            if ($choice->isOther() && isset($questionResponse['other'])) {
                                                $choiceAnswer->setContent($questionResponse['other']);
                                            }
                                            $this->surveyManager
                                                ->persistMultipleChoiceQuestionAnswer($choiceAnswer);
                                        }
                                    }
                                } elseif ($questionType === 'multiple_choice_single' &&
                                    isset($questionResponse['choice']) &&
                                    !empty($questionResponse['choice'])) {

                                    $this->surveyManager
                                        ->deleteMultipleChoiceAnswersByQuestionAnswer($questionAnswer);

                                    $choiceId = (int)$questionResponse['choice'];
                                    $choice = $this->surveyManager->getChoiceById($choiceId);
                                    $choiceAnswer = new MultipleChoiceQuestionAnswer();
                                    $choiceAnswer->setQuestionAnswer($questionAnswer);
                                    $choiceAnswer->setChoice($choice);

                                    if ($choice->isOther() && isset($questionResponse['other'])) {
                                        $choiceAnswer->setContent($questionResponse['other']);
                                    }
                                    $this->surveyManager
                                        ->persistMultipleChoiceQuestionAnswer($choiceAnswer);
                                }
                            }
                        }
                    }
                }
            }

            $event = $isAnon ?
                new LogSurveyAnswer($survey) :
                new LogSurveyAnswer($survey, $user);
            $this->eventDispatcher->dispatch('log', $event);
        }

        return new RedirectResponse(
            $this->router->generate(
                'claro_survey_index',
                array('survey' => $survey->getId())
            )
        );
    }

    /**
     * @EXT\Route(
     *     "/survey/{survey}/results/show/question/{question}/page/{page}/max/{max}",
     *     name="claro_survey_results_show",
     *     defaults={"page"=1, "max"=20}
     * )
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     * @EXT\Template()
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function surveyResultsShowAction(
        Survey $survey,
        Question $question,
        $page = 1,
        $max = 20
    )
    {
        $canEdit = $this->hasSurveyRight($survey, 'EDIT');

        if (!$canEdit && !$survey->getHasPublicResult()) {

            throw new AccessDeniedException();
        }
        $questionRelations = $this->surveyManager
            ->getQuestionRelationsBySurvey($survey);
        $questions = array();

        foreach ($questionRelations as $relation) {
            $relationQuestion = $relation->getQuestion();

            if ($relationQuestion->getType() !== 'title') {
                $questions[] = $relation->getQuestion();
            }
        }

        $results = $this->showTypedQuestionResults($survey, $question, $page, $max)
            ->getContent();
        $comments = $this->surveyManager->getCommentsFromQuestionBySurveyAndQuestion(
            $survey,
            $question
        );

        return array(
            'survey' => $survey,
            'questions' => $questions,
            'currentQuestion' => $question,
            'results' => $results,
            'nbComments' => count($comments),
            'max' => $max
        );
    }

    /**
     * @EXT\Route(
     *     "/survey/{survey}/results/show/question/{question}/comments/page/{page}/max/{max}",
     *     name="claro_survey_results_show_comments",
     *     defaults={"page"=1, "max"=20},
     *     options={"expose"=true}
     * )
     * @EXT\Template()
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function showCommentsForQuestionAction(
        Survey $survey,
        Question $question,
        $page = 1,
        $max = 20
    )
    {
        $canEdit = $this->hasSurveyRight($survey, 'EDIT');

        if (!$canEdit && !$survey->getHasPublicResult()) {

            throw new AccessDeniedException();
        }
        $comments = $this->surveyManager->getCommentsFromQuestionBySurveyAndQuestion(
            $survey,
            $question,
            $page,
            $max
        );

        return array(
            'survey' => $survey,
            'question' => $question,
            'max' => $max,
            'comments' => $comments
        );
    }

    /**
     * @EXT\Route(
     *     "/survey/{survey}/results/show/question/{question}/choice/{choice}/other/page/{page}/max/{max}",
     *     name="claro_survey_results_show_other_answers",
     *     defaults={"page"=1, "max"=20},
     *     options={"expose"=true}
     * )
     * @EXT\Template()
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function showOtherAnswersForChoiceAction(
        Survey $survey,
        Question $question,
        Choice $choice,
        $page = 1,
        $max = 20
    )
    {
        $canEdit = $this->hasSurveyRight($survey, 'EDIT');

        if (!$canEdit && !$survey->getHasPublicResult()) {

            throw new AccessDeniedException();
        }
        $answers = $this->surveyManager->getMultipleChoiceAnswersByChoice(
            $choice,
            $page,
            $max
        );

        return array(
            'survey' => $survey,
            'question' => $question,
            'choice' => $choice,
            'otherMax' => $max,
            'answers' => $answers
        );
    }

    /**
     * @EXT\Route(
     *     "/survey/{survey}/model/{model}/details/retrieve",
     *     name="claro_survey_retrieve_model_details",
     *     options={"expose"=true}
     * )
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function retrieveModelDetailsAction(Survey $survey, QuestionModel $model)
    {
        $canEdit = $this->hasSurveyRight($survey, 'EDIT');
        $workspaceIdA = $survey->getResourceNode()->getWorkspace()->getId();
        $workspaceIdB = $model->getWorkspace()->getId();

        if (!$canEdit || ($workspaceIdA !== $workspaceIdB)) {

            throw new AccessDeniedException();
        }

        return new Response(json_encode($model->getDetails()), 200);
    }

    /**
     * @EXT\Route(
     *     "/survey/{survey}/question/order/update/relation/{relation}/with/{otherRelation}/mode/{mode}",
     *     name="claro_survey_update_question_order",
     *     options={"expose"=true}
     * )
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function updateQuestionOrderAction(
        Survey $survey,
        SurveyQuestionRelation $relation,
        SurveyQuestionRelation $otherRelation,
        $mode
    )
    {
        $this->checkSurveyRight($survey, 'EDIT');

        if ($relation->getSurvey()->getId() === $survey->getId() &&
            $otherRelation->getSurvey()->getId() === $survey->getId()) {
            $newOrder = $otherRelation->getQuestionOrder();

            if ($mode === 'next') {
                $this->surveyManager
                    ->updateQuestionOrder($survey, $relation, $newOrder);
            } else {
                $relation->setQuestionOrder($newOrder + 1);
                $this->surveyManager->persistSurveyQuestionRelation($relation);
            }

            return new Response('success', 204);
        } else {

            return new Response('Forbidden', 403);
        }
    }

    /**
     * @EXT\Route(
     *     "/survey/{survey}/results/export",
     *     name="claro_survey_results_export"
     * )
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function resultsExcelExportAction(Survey $survey)
    {
        $canEdit = $this->hasSurveyRight($survey, 'EDIT');

        if (!$canEdit && !$survey->getHasPublicResult()) {

            throw new AccessDeniedException();
        }
        $questionRelations = $this->surveyManager
            ->getQuestionRelationsBySurvey($survey);
        $questions = array();
        $results = array();
        $comments = array();

        foreach ($questionRelations as $relation) {
            $relationQuestion = $relation->getQuestion();

            if ($relationQuestion->getType() !== 'title') {
                $questions[] = $relation->getQuestion();
            }
        }

        foreach ($questions as $question) {
            $results[$question->getId()] =
                $this->showTypedQuestionResults($survey, $question, 1, 20, true)->getContent();
            $comments[$question->getId()] = $this->surveyManager->getCommentsFromQuestionBySurveyAndQuestion(
                $survey,
                $question
            );
        }
        $response = new Response(
            $this->templating->render(
                "ClarolineSurveyBundle:Survey:surveyResultsExport.html.twig",
                array(
                    'survey' => $survey,
                    'questions' => $questions,
                    'results' => $results,
                    'comments' => $comments
                )
            )
        );
        $fileName = $this->translator->trans('results', array(), 'platform');
        $response->headers->set('Content-Transfer-Encoding', 'octet-stream');
        $response->headers->set('Content-Type', 'application/force-download; charset=utf-8');
        $response->headers->set('Content-Disposition', 'attachment; filename=' . $fileName . '.xls');
        $response->headers->set('Content-Type', 'application/vnd.ms-excel; charset=utf-8');

        return $response;
    }

    /**
     * @EXT\Route(
     *     "/survey/{survey}/question/{question}/results/export",
     *     name="claro_survey_question_results_export"
     * )
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function questionResultsExcelExportAction(Survey $survey, Question $question)
    {
        $canEdit = $this->hasSurveyRight($survey, 'EDIT');

        if (!$canEdit && !$survey->getHasPublicResult()) {

            throw new AccessDeniedException();
        }
        $results = array();
        $comments = array();
        $results[$question->getId()] =
            $this->showTypedQuestionResults($survey, $question, 1, 20, true)->getContent();

        $response = new Response(
            $this->templating->render(
                "ClarolineSurveyBundle:Survey:surveyResultsExport.html.twig",
                array(
                    'survey' => $survey,
                    'questions' => array($question),
                    'results' => $results,
                    'comments' => $comments
                )
            )
        );
        $fileName = $this->translator->trans('results', array(), 'platform');
        $response->headers->set('Content-Transfer-Encoding', 'octet-stream');
        $response->headers->set('Content-Type', 'application/force-download');
        $response->headers->set('Content-Disposition', 'attachment; filename=' . $fileName . '.xls');
        $response->headers->set('Content-Type', 'application/vnd.ms-excel; charset=utf-8');

        return $response;
    }

    /**
     * @EXT\Route(
     *     "/survey/{survey}/answers/export",
     *     name="claro_survey_answers_export"
     * )
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function surveyAnswersExportAction(Survey $survey)
    {
        $this->checkSurveyRight($survey, 'EDIT');
        $questionRelations = $this->surveyManager->getQuestionRelationsBySurvey($survey);
        $surveyAnswers = $this->surveyManager->getSurveyAnswersBySurvey($survey);
        $mapping = array();
        $index = 0;
        $exportingArray = array();
        $line = array();

        foreach ($questionRelations as $relation) {
            $question = $relation->getQuestion();
            $questionId = $question->getId();
            
            // Create mapping between question id and position in the exported line
            $mapping[$questionId] = $index;
            $index ++;
            $line[] = $question->getQuestion();

            if ($question->isCommentAllowed()) {
                $commentLabel = $question->getCommentLabel();
                $label = empty($commentLabel) ?
                    $this->translator->trans('comment', array(), 'survey')
                    : $commentLabel;
                $line[] = $label;
                $index++;
            }
        }
        $exportingArray[] = $line;

        foreach ($surveyAnswers as $surveyAnswer) {
            $line = array();

            for ($i = 0; $i < $index; $i++) {
                $line[] = '';
            }
            $answers = $surveyAnswer->getQuestionsAnswers();

            foreach ($answers as $answer) {
                $question = $answer->getQuestion();
                $questionId = $question->getId();
                $questionType = $question->getType();
                $position = isset($mapping[$questionId]) ? $mapping[$questionId] : null;

                if (!is_null($position)) {

                    switch ($questionType) {
                        case 'open_ended':
                            $openEndedQuestion = $this->surveyManager
                                ->getOpenEndedAnswerByQuestionAnswer($answer);
                            $line[$position] = is_null($openEndedQuestion) ? '-' : $openEndedQuestion->getContent();
                            break;
                        case 'multiple_choice_single':
                        case 'multiple_choice_multiple':
                            $choicesAnswers = $this->surveyManager
                                ->getMultipleChoiceAnswersByQuestionAnswer($answer);
                            $choicesArray = array();

                            foreach ($choicesAnswers as $choiceAnswer) {
                                $choice = $choiceAnswer->getChoice();

                                if (!is_null($choice)) {
                                    $choicesArray[] = $choice->getContent();
                                }
                            }
                            $choicesText = implode('[,]', $choicesArray);
                            $line[$position] = $choicesText;
                            break;
                        default:
                            break;
                    }

                    if ($question->isCommentAllowed()) {
                        $comment = $answer->getComment();

                        if (!empty($comment)) {
                            $line[$position + 1] = $comment;
                        }
                    }
                }
            }
            $exportingArray[] = $line;
        }
        $response = new Response();
        $content = '';

        foreach ($exportingArray as $exportingLine) {
            $rawTxt = implode('[;]', $exportingLine);
            $cleanerTxt = html_entity_decode($rawTxt);
            $txt = strip_tags($cleanerTxt);
            $content .= $txt . PHP_EOL;
        }

        $filename = $this->translator->trans('answers', array(), 'survey') . '.txt';

        $response->headers->set('Content-Transfer-Encoding', 'octet-stream');
        $response->headers->set('Content-Type', 'application/force-download');
        $response->headers->set('Content-Disposition', 'attachment; filename=' . $filename);
        $response->headers->set('Content-Type', 'text/plain; charset=utf-8');
        $response->headers->set('Connection', 'close');
        $response->setContent($content);

        return $response;
    }

    private function showTypedQuestionResults(
        Survey $survey,
        Question $question,
        $page = 1,
        $max = 20,
        $forExport = false
    )
    {
        $questionType = $question->getType();

        switch ($questionType) {

            case 'multiple_choice_single' :
            case 'multiple_choice_multiple' :

                return $this->showMultipleChoiceQuestionResults(
                    $survey,
                    $question,
                    $max,
                    $forExport
                );
            case 'open_ended':

                return $this->showOpenEndedQuestionResults(
                    $survey,
                    $question,
                    $page,
                    $max,
                    $forExport
                );
            default:
                break;
        }

        return new Response();
    }

    private function showMultipleChoiceQuestionResults(
        Survey $survey,
        Question $question,
        $otherMax = 20,
        $forExport = false
    )
    {
        $choices = $this->surveyManager->getChoicesByQuestion($question);
        $choicesCount = array();
        $choicesRatio = array();
        $nbRespondents = 0;
        $nbChoiceAnswers = 0;
        $otherChoice = null;

        $respondents = $this->surveyManager
            ->countQuestionAnswersBySurveyAndQuestion($survey, $question);

        if (!is_null($respondents)) {
            $nbRespondents = $respondents['nb_answers'];

            foreach ($choices as $choice) {
                $count = $this->surveyManager
                    ->countMultipleChoiceAnswersBySurveyAndChoice($survey, $choice);

                if (!is_null($count)) {
                    $choicesCount[$choice->getId()] = $count['nb_answers'];
                    $nbChoiceAnswers += $choicesCount[$choice->getId()];

                    if ($choice->isOther() && $choicesCount[$choice->getId()] > 0) {
                        $otherChoice = $choice;
                    }
                }
            }

            foreach ($choicesCount as $choiceId => $nbAbswers) {
                $choicesRatio[$choiceId] = ($nbChoiceAnswers > 0) ?
                    round(
                        ($nbAbswers / $nbChoiceAnswers) * 100,
                        2
                    ) :
                    0;
            }
        }

        return new Response(
            $this->templating->render(
                "ClarolineSurveyBundle:Survey:showMultipleChoiceQuestionResults.html.twig",
                array(
                    'survey' => $survey,
                    'question' => $question,
                    'choices' => $choices,
                    'choicesCount' => $choicesCount,
                    'nbRespondents' => $nbRespondents,
                    'choicesRatio' => $choicesRatio,
                    'otherChoice' => $otherChoice,
                    'otherMax' => $otherMax,
                    'forExport' => $forExport
                )
            )
        );
    }

    private function showOpenEndedQuestionResults(
        Survey $survey,
        Question $question,
        $page = 1,
        $max = 20,
        $forExport = false
    )
    {
        $answers = $forExport ?
            $this->surveyManager->getOpenEndedAnswersBySurveyAndQuestionWithoutPager(
                $survey,
                $question
            ) :
            $this->surveyManager->getOpenEndedAnswersBySurveyAndQuestion(
                $survey,
                $question,
                $page,
                $max
            );

        return new Response(
            $this->templating->render(
                "ClarolineSurveyBundle:Survey:showOpenEndedQuestionResults.html.twig",
                array(
                    'survey' => $survey,
                    'question' => $question,
                    'answers' => $answers,
                    'max' => $max,
                    'forExport' => $forExport
                )
            )
        );
    }

    private function displayTypedQuestion(
        Survey $survey,
        Question $question,
        array $answers,
        $canEdit = true
    )
    {
        $this->checkQuestionRight($survey, $question, 'OPEN');
        $questionType = $question->getType();

        switch ($questionType) {

            case 'multiple_choice_single' :
            case 'multiple_choice_multiple' :

                return $this->displayMultipleChoiceQuestion(
                    $question,
                    $answers,
                    $canEdit
                );
            case 'open_ended':

                return $this->displayOpenEndedQuestion(
                    $question,
                    $answers,
                    $canEdit
                );
            default:
                break;
        }

        return new Response();
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
        $horizontal = false;

        if (!is_null($multipleChoiceQuestion)) {
            $choices = $multipleChoiceQuestion->getChoices();
            $horizontal = $multipleChoiceQuestion->getHorizontal();
        }

        return new Response(
            $this->templating->render(
                "ClarolineSurveyBundle:Survey:multipleChoiceQuestionForm.html.twig",
                array(
                    'survey' => $survey,
                    'horizontal' => $horizontal,
                    'choices' => $choices
                )
            )
        );
    }

    private function displayMultipleChoiceQuestion(
        Question $question,
        array $answers = null,
        $canEdit = true
    )
    {
        $multipleChoiceQuestion = $this->surveyManager
            ->getMultipleChoiceQuestionByQuestion($question);

        if (is_null($multipleChoiceQuestion)) {

            throw new \Exception('Cannot find multiple choice question');
        }

        $choices = $multipleChoiceQuestion->getChoices();
        $answersDatas = is_null($answers) ? array() : $answers;

        return new Response(
            $this->templating->render(
                "ClarolineSurveyBundle:Survey:displayMultipleChoiceQuestion.html.twig",
                array(
                    'question' => $question,
                    'choices' => $choices,
                    'answers' => $answersDatas,
                    'canEdit' => $canEdit,
                    'horizontal' => $multipleChoiceQuestion->getHorizontal()
                )
            )
        );
    }

    private function displayOpenEndedQuestion(
        Question $question,
        array $answers = null,
        $canEdit = true
    )
    {
        $answersDatas = is_null($answers) ? array() : $answers;

        return new Response(
            $this->templating->render(
                "ClarolineSurveyBundle:Survey:displayOpenEndedQuestion.html.twig",
                array(
                    'question' => $question,
                    'answers' => $answersDatas,
                    'canEdit' => $canEdit
                )
            )
        );
    }

    private function updateMultipleChoiceQuestion(
        Question $question,
        array $datas
    )
    {
        $horizontal = isset($datas['choice-display']) &&
            ($datas['choice-display'] === 'horizontal');
        $choices = isset($datas['choice']) ?
            $datas['choice'] :
            array();
        $hasChoiceOther = isset($datas['choice-other']['other']) &&
            $datas['choice-other']['other'] === 'other';

        $multipleChoiceQuestion = $this->surveyManager
            ->getMultipleChoiceQuestionByQuestion($question);

        if (is_null($multipleChoiceQuestion)) {
            $multipleChoiceQuestion = $this->surveyManager
                ->createMultipleChoiceQuestion(
                    $question,
                    $horizontal,
                    $choices
                );
        } else {
            $this->surveyManager->updateQuestionChoices(
                $multipleChoiceQuestion,
                $horizontal,
                $choices
            );
        }

        if ($hasChoiceOther &&
            isset($datas['choice-other']['content']) &&
            !empty($datas['choice-other']['content'])) {

            $otherChoice = new Choice();
            $otherChoice->setChoiceQuestion($multipleChoiceQuestion);
            $otherChoice->setOther(true);
            $otherChoice->setContent($datas['choice-other']['content']);
            $this->surveyManager->persistChoice($otherChoice);
        }
    }

    private function computeStatus(Survey $survey)
    {
        $status = 'unpublished';

        if ($survey->isPublished() && !$survey->isClosed()) {
            $status = 'published';
        } elseif ($survey->isClosed()) {
            $status = 'closed';
        }

        return $status;
    }

    private function validateSurveyAnswer(Survey $survey, array $datas)
    {
        $relations = $survey->getQuestionRelations();
        $errors = array();

        foreach ($relations as $relation) {

            if ($relation->getMandatory()) {
                $question = $relation->getQuestion();
                $questionId = $question->getId();
                $type = $question->getType();

                switch ($type) {

                    case 'open_ended':

                        if (!isset($datas[$questionId]) ||
                            !isset($datas[$questionId]['answer']) ||
                            empty($datas[$questionId]['answer'])) {

                            $errors[$questionId] = $questionId;
                        }
                        break;
                    case 'multiple_choice_single':

                        if (!isset($datas[$questionId]) ||
                            !isset($datas[$questionId]['choice']) ||
                            empty($datas[$questionId]['choice'])) {

                            $errors[$questionId] = $questionId;
                        }
                        break;
                    case 'multiple_choice_multiple':

                        if (!isset($datas[$questionId]) ||
                            count($datas[$questionId]) === 0 ||
                            (
                                count($datas[$questionId]) === 1 &&
                                isset($datas[$questionId]['comment'])
                            ) ||
                            (
                                count($datas[$questionId]) === 1 &&
                                isset($datas[$questionId]['other'])
                            ) ||
                            (
                                count($datas[$questionId]) === 2 &&
                                isset($datas[$questionId]['comment']) &&
                                isset($datas[$questionId]['other'])
                            )
                        ) {
                            $errors[$questionId] = $questionId;
                        }
                        break;
                    default:
                        break;
                }
            }
        }

        return $errors;
    }

    private function checkSurveyRight(Survey $survey, $right)
    {
        $collection = new ResourceCollection(array($survey->getResourceNode()));

        if (!$this->authorization->isGranted($right, $collection)) {

            throw new AccessDeniedException($collection->getErrorsForDisplay());
        }
    }

    private function hasSurveyRight(Survey $survey, $right)
    {
        $collection = new ResourceCollection(array($survey->getResourceNode()));

        return $this->authorization->isGranted($right, $collection);
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
