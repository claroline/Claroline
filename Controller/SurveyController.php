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
use Symfony\Component\Form\FormFactory;
//use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;
//use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\SecurityContextInterface;

class SurveyController extends Controller
{
    private $formFactory;
    private $request;
    private $router;
    private $security;
//    private $session;
    private $surveyManager;

    /**
     * @DI\InjectParams({
     *     "formFactory"   = @DI\Inject("form.factory"),
     *     "requestStack"  = @DI\Inject("request_stack"),
     *     "router"        = @DI\Inject("router"),
     *     "security"      = @DI\Inject("security.context"),
     *     "surveyManager" = @DI\Inject("claroline.manager.survey_manager")
     * })
     */
    public function __construct(
        FormFactory $formFactory,
        RequestStack $requestStack,
        UrlGeneratorInterface $router,
        SecurityContextInterface $security,
//        SessionInterface $session,
        SurveyManager $surveyManager
    )
    {
        $this->formFactory = $formFactory;
        $this->request = $requestStack;
        $this->router = $router;
        $this->security = $security;
//        $this->session = $session;
        $this->surveyManager = $surveyManager;
    }

    /**
     * @EXT\Route(
     *     "/{survey}",
     *     name="claro_survey_index"
     * )
     * @EXT\Template
     *
     * @param Survey $survey
     * @return array
     */
    public function indexAction(Survey $survey)
    {
        $this->checkRight($survey, 'OPEN');
        $canEdit = $this->hasRight($survey, 'EDIT');
//        $handler = $this->getHandler($survey);

//        if (!$survey->isPublished()) {
//
//            if ($canEdit) {
//
//                return $this->redirectTo('question_creation_form', $survey);
//            } elseif ($canEdit) {
//
//                return $this->redirectTo('question_edition_form', $survey);
//            }
//        } elseif (!$survey->isClosed()) {
//
//        } else {
//
//        }

        return array('survey' => $survey);
    }

    /**
     * @EXT\Route(
     *     "/survey/{survey}/edit/form",
     *     name="claro_survey_edit_form"
     * )
     * @EXT\Template()
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function surveyEditFormAction(Survey $survey)
    {
        $this->checkRight($survey, 'EDIT');
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
     *     "/survey/{survey}/edit",
     *     name="claro_survey_edit"
     * )
     * @EXT\Template(
     *     "ClarolineSurveyBundle:Survey:surveyEditForm.html.twig"
     * )
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function surveyEditAction(Survey $survey)
    {
        $this->checkRight($survey, 'EDIT');
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
     *     "/survey/{survey}/questions/management/ordered/by/{orderedBy}/order/{order}",
     *     name="claro_survey_questions_management",
     *     defaults={"ordered"="title","order"="ASC"},
     * )
     * @EXT\Template()
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function questionsManagementAction(Survey $survey, $orderedBy, $order)
    {
        $this->checkRight($survey, 'EDIT');
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
     *     "/survey/{survey}/question/create/form",
     *     name="claro_survey_question_create_form"
     * )
     * @EXT\Template()
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function questionCreateFormAction(Survey $survey)
    {
        $this->checkRight($survey, 'EDIT');
        $form = $this->formFactory->create(
            new QuestionType(),
            new Question()
        );

        return array(
            'form' => $form->createView(),
            'survey' => $survey
        );
    }

    /**
     * @EXT\Route(
     *     "/survey/{survey}/question/create",
     *     name="claro_survey_question_create"
     * )
     * @EXT\Template(
     *     "ClarolineSurveyBundle:Survey:questionCreateForm.html.twig"
     * )
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function questionCreateAction(Survey $survey)
    {
        $this->checkRight($survey, 'EDIT');
        $question  = new Question();
        $form = $this->formFactory->create(
            new QuestionType(),
            $question
        );
        $form->handleRequest($this->request->getCurrentRequest());

        if ($form->isValid()) {
            $question->setWorkspace($survey->getResourceNode()->getWorkspace());
            $this->surveyManager->persistQuestion($question);

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

        return array(
            'form' => $form->createView(),
            'survey' => $survey
        );
    }

//    /**
//     * @EXT\Route(
//     *     "/{survey}/question",
//     *     name="claro_survey_question_creation_form"
//     * )
//     * @EXT\Method("GET")
//     * @EXT\Template("ClarolineSurveyBundle:Survey:questionForm.html.twig")
//     *
//     * @param Survey $survey
//     * @return array
//     */
//    public function questionCreationFormAction(Survey $survey)
//    {
//        $this->checkRight($survey, 'EDIT');
//        $form = $this->getHandler($survey)->getCreationForm($survey);
//
//        return array(
//            'survey' => $survey,
//            'form' => $form,
//            'action' => 'create'
//        );
//    }

//    /**
//     * @EXT\Route(
//     *     "/{survey}/question",
//     *     name="claro_survey_create_question"
//     * )
//     * @EXT\Method("POST")
//     *
//     * @param Survey $survey
//     * @param \Symfony\Component\HttpFoundation\Request $request
//     * @return Response
//     */
//    public function createQuestionAction(Survey $survey, Request $request)
//    {
//        $this->checkRight($survey, 'EDIT');
//        $result = $this->getHandler($survey)->createQuestion($survey, $request);
//
//        if ($result instanceof FormView) {
//
//            return $this->render(
//                'ClarolineSurveyBundle:Survey:questionForm.html.twig',
//                array('survey' => $survey, 'form' => $result, 'action' => 'create')
//            );
//        }
//
//        $this->session->getFlashBag()->add('success', 'Question created');
//
//        return $this->redirectTo('question_edition_form', $survey);
//    }

//    /**
//     * @EXT\Route(
//     *     "/{survey}/question/edit",
//     *     name="claro_survey_question_edition_form"
//     * )
//     * @EXT\Method("GET")
//     * @EXT\Template("ClarolineSurveyBundle:Survey:questionForm.html.twig")
//     *
//     * @param Survey $survey
//     * @return array
//     */
//    public function questionEditionFormAction(Survey $survey)
//    {
//        $this->checkRight($survey, 'EDIT');
//        $handler = $this->getHandler($survey);
//        $form = $handler->getEditionForm($handler->getQuestion($survey));
//
//        return array(
//            'survey' => $survey,
//            'form' => $form,
//            'action' => 'edit'
//        );
//    }

//    /**
//     * @EXT\Route(
//     *     "/{survey}/question/edit",
//     *     name="claro_survey_edit_question"
//     * )
//     * @EXT\Method("POST")
//     * @EXT\Template("ClarolineSurveyBundle:Survey:questionForm.html.twig")
//     *
//     * @param Survey $survey
//     * @param \Symfony\Component\HttpFoundation\Request $request
//     * @return array
//     */
//    public function editQuestionAction(Survey $survey, Request $request)
//    {
//        $this->checkRight($survey, 'EDIT');
//        $result = $this->getHandler($survey)->editQuestion($survey, $request);
//
//        if ($result instanceof FormView) {
//
//            return $this->render(
//                'ClarolineSurveyBundle:Survey:questionForm.html.twig',
//                array('survey' => $survey, 'form' => $result, 'action' => 'edit')
//            );
//        }
//
//        $this->session->getFlashBag()->add('success', 'Changes saved');
//
//        return $this->redirectTo('question_edition_form', $survey);
//    }

    private function checkRight(Survey $survey, $right)
    {
        $collection = new ResourceCollection(array($survey->getResourceNode()));

        if (!$this->security->isGranted($right, $collection)) {
            
            throw new AccessDeniedException($collection->getErrorsForDisplay());
        }
    }

    private function hasRight(Survey $survey, $right)
    {
        $collection = new ResourceCollection(array($survey->getResourceNode()));

        return $this->security->isGranted($right, $collection);
    }

//    private function getHandler(Survey $survey)
//    {
//        return $this->manager->getQuestionTypeHandlerFor(
//            $survey->getQuestionType()
//        );
//    }

//    private function redirectTo($route, Survey $survey)
//    {
//        $url = $this->router->generate(
//            "claro_survey_{$route}",
//            array('survey' => $survey->getId())
//        );
//
//        return new RedirectResponse($url);
//    }
}
