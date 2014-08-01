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
use Claroline\SurveyBundle\Entity\Survey;
use Claroline\SurveyBundle\Manager\SurveyManager;
use JMS\DiExtraBundle\Annotation as DI;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;

class SurveyController extends Controller
{
    private $security;
    private $manager;
    private $router;
    private $session;

    /**
     * @DI\InjectParams({
     *     "security"   = @DI\Inject("security.context"),
     *     "manager"    = @DI\Inject("claroline.manager.survey_manager"),
     *     "router"     = @DI\Inject("router"),
     *     "session"    = @DI\Inject("session")
     * })
     */
    public function __construct(
        SecurityContextInterface $security,
        SurveyManager $manager,
        UrlGeneratorInterface $router,
        SessionInterface $session
    )
    {
        $this->security = $security;
        $this->manager = $manager;
        $this->router = $router;
        $this->session = $session;
    }

    /**
     * @EXT\Route("/{survey}", name="claro_survey_index")
     * @EXT\Template
     *
     * @param Survey $survey
     * @return array
     */
    public function indexAction(Survey $survey)
    {
        $this->checkRight($survey, 'OPEN');
        $canEdit = $this->hasRight($survey, 'EDIT');
        $handler = $this->getHandler($survey);

        if (!$survey->isPublished()) {
            if ($canEdit && !$handler->hasQuestion($survey)) {
                return $this->redirectTo('question_creation_form', $survey);
            } elseif ($canEdit) {
                return $this->redirectTo('question_edition_form', $survey);
            }
        } elseif (!$survey->isClosed()) {

        } else {

        }
        return array('_resource' => $survey, 'content' => $content);
    }

    /**
     * @EXT\Route("/{survey}/question", name="claro_survey_question_creation_form")
     * @EXT\Method("GET")
     * @EXT\Template("ClarolineSurveyBundle:Survey:questionForm.html.twig")
     *
     * @param Survey $survey
     * @return array
     */
    public function questionCreationFormAction(Survey $survey)
    {
        $this->checkRight($survey, 'EDIT');
        $form = $this->getHandler($survey)->getCreationForm($survey);

        return array('survey' => $survey, 'form' => $form, 'action' => 'create');
    }

    /**
     * @EXT\Route("/{survey}/question", name="claro_survey_create_question")
     * @EXT\Method("POST")
     *
     * @param Survey $survey
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return Response
     */
    public function createQuestionAction(Survey $survey, Request $request)
    {
        $this->checkRight($survey, 'EDIT');
        $result = $this->getHandler($survey)->createQuestion($survey, $request);

        if ($result instanceof FormView) {
            return $this->render(
                'ClarolineSurveyBundle:Survey:questionForm.html.twig',
                array('survey' => $survey, 'form' => $result, 'action' => 'create')
            );
        }

        $this->session->getFlashBag()->add('success', 'Question created');

        return $this->redirectTo('question_edition_form', $survey);
    }

    /**
     * @EXT\Route("/{survey}/question/edit", name="claro_survey_question_edition_form")
     * @EXT\Method("GET")
     * @EXT\Template("ClarolineSurveyBundle:Survey:questionForm.html.twig")
     *
     * @param Survey $survey
     * @return array
     */
    public function questionEditionFormAction(Survey $survey)
    {
        $this->checkRight($survey, 'EDIT');
        $handler = $this->getHandler($survey);
        $form = $handler->getEditionForm($handler->getQuestion($survey));

        return array('survey' => $survey, 'form' => $form, 'action' => 'edit');
    }

    /**
     * @EXT\Route("/{survey}/question/edit", name="claro_survey_edit_question")
     * @EXT\Method("POST")
     * @EXT\Template("ClarolineSurveyBundle:Survey:questionForm.html.twig")
     *
     * @param Survey $survey
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return array
     */
    public function editQuestionAction(Survey $survey, Request $request)
    {
        $this->checkRight($survey, 'EDIT');
        $result = $this->getHandler($survey)->editQuestion($survey, $request);

        if ($result instanceof FormView) {
            return $this->render(
                'ClarolineSurveyBundle:Survey:questionForm.html.twig',
                array('survey' => $survey, 'form' => $result, 'action' => 'edit')
            );
        }

        $this->session->getFlashBag()->add('success', 'Changes saved');

        return $this->redirectTo('question_edition_form', $survey);
    }

    private function checkRight(Survey $survey, $right)
    {
        $collection = new ResourceCollection(array($survey->getResourceNode()));

        if (!$this->get('security.context')->isGranted($right, $collection)) {
            throw new AccessDeniedHttpException($collection->getErrorsForDisplay());
        }
    }

    private function hasRight(Survey $survey, $right)
    {
        $collection = new ResourceCollection(array($survey->getResourceNode()));

        return $this->get('security.context')->isGranted($right, $collection);
    }

    private function getHandler(Survey $survey)
    {
        return $this->manager->getQuestionTypeHandlerFor(
            $survey->getQuestionType()
        );
    }

    private function redirectTo($route, Survey $survey)
    {
        $url = $this->router->generate(
            "claro_survey_{$route}",
            array('survey' => $survey->getId())
        );

        return new RedirectResponse($url);
    }
}
