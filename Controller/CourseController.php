<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CursusBundle\Controller;

use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Manager\ToolManager;
use Claroline\CursusBundle\Entity\Course;
use Claroline\CursusBundle\Entity\CourseSession;
use Claroline\CursusBundle\Entity\Cursus;
use Claroline\CursusBundle\Entity\CursusDisplayedWord;
use Claroline\CursusBundle\Form\CourseSessionType;
use Claroline\CursusBundle\Form\CourseType;
use Claroline\CursusBundle\Manager\CursusManager;
use JMS\DiExtraBundle\Annotation as DI;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Bundle\FrameworkBundle\Translation\Translator;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\SecurityContextInterface;

class CourseController extends Controller
{
    private $cursusManager;
    private $formFactory;
    private $request;
    private $securityContext;
    private $toolManager;
    private $translator;

    /**
     * @DI\InjectParams({
     *     "cursusManager"   = @DI\Inject("claroline.manager.cursus_manager"),
     *     "formFactory"     = @DI\Inject("form.factory"),
     *     "requestStack"    = @DI\Inject("request_stack"),
     *     "securityContext" = @DI\Inject("security.context"),
     *     "toolManager"     = @DI\Inject("claroline.manager.tool_manager"),
     *     "translator"      = @DI\Inject("translator")
     * })
     */
    public function __construct(
        CursusManager $cursusManager,
        FormFactory $formFactory,
        RequestStack $requestStack,
        SecurityContextInterface $securityContext,
        ToolManager $toolManager,
        Translator $translator
    )
    {
        $this->cursusManager = $cursusManager;
        $this->formFactory = $formFactory;
        $this->request = $requestStack->getCurrentRequest();
        $this->securityContext = $securityContext;
        $this->toolManager = $toolManager;
        $this->translator = $translator;
    }


    /**
     * @EXT\Route(
     *     "/tool/course/index/page/{page}/max/{max}/ordered/by/{orderedBy}/order/{order}/search/{search}",
     *     name="claro_cursus_tool_course_index",
     *     defaults={"page"=1, "search"="", "max"=50, "orderedBy"="title","order"="ASC"},
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     * @EXT\Template()
     */
    public function cursusToolCourseIndexAction(
        $search = '',
        $page = 1,
        $max = 50,
        $orderedBy = 'title',
        $order = 'ASC'
    )
    {
        $this->checkToolAccess();
        $displayedWords = array();

        foreach (CursusDisplayedWord::$defaultKey as $key) {
            $displayedWords[$key] = $this->cursusManager->getDisplayedWord($key);
        }
        $courses = $search === '' ?
            $this->cursusManager->getAllCourses($orderedBy, $order, $page, $max) :
            $this->cursusManager->getSearchedCourses($search, $orderedBy, $order, $page, $max);

        return array(
            'defaultWords' => CursusDisplayedWord::$defaultKey,
            'displayedWords' => $displayedWords,
            'type' => 'course',
            'courses' => $courses,
            'search' => $search,
            'page' => $page,
            'max' => $max,
            'orderedBy' => $orderedBy,
            'order' => $order
        );
    }

    /**
     * @EXT\Route(
     *     "cursus/course/create/form",
     *     name="claro_cursus_course_create_form",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     * @EXT\Template("ClarolineCursusBundle:Course:courseCreateModalForm.html.twig")
     */
    public function courseCreateFormAction(User $authenticatedUser)
    {
        $this->checkToolAccess();
        $form = $this->formFactory->create(new CourseType($authenticatedUser));

        return array('form' => $form->createView());
    }

    /**
     * @EXT\Route(
     *     "cursus/course/create",
     *     name="claro_cursus_course_create",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     * @EXT\Template("ClarolineCursusBundle:Course:courseCreateModalForm.html.twig")
     */
    public function courseCreateAction(User $authenticatedUser)
    {
        $this->checkToolAccess();
        $course = new Course();
        $form = $this->formFactory->create(new CourseType($authenticatedUser), $course);
        $form->handleRequest($this->request);

        if ($form->isValid()) {
            $this->cursusManager->persistCourse($course);

            $message = $this->translator->trans(
                'course_creation_confirm_msg' ,
                array(),
                'cursus'
            );
            $session = $this->request->getSession();
            $session->getFlashBag()->add('success', $message);

            return new JsonResponse('success', 200);
        } else {

            return array('form' => $form->createView());
        }
    }

    /**
     * @EXT\Route(
     *     "cursus/course/{course}/edit/form",
     *     name="claro_cursus_course_edit_form",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     * @EXT\Template("ClarolineCursusBundle:Course:courseEditModalForm.html.twig")
     *
     * @param Course $course
     */
    public function courseEditFormAction(Course $course, User $authenticatedUser)
    {
        $this->checkToolAccess();
        $form = $this->formFactory->create(
            new CourseType($authenticatedUser),
            $course
        );

        return array(
            'form' => $form->createView(),
            'course' => $course
        );
    }

    /**
     * @EXT\Route(
     *     "cursus/course/{course}/edit",
     *     name="claro_cursus_course_edit",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     * @EXT\Template("ClarolineCursusBundle:Course:courseEditModalForm.html.twig")
     *
     * @param Course $course
     */
    public function courseEditAction(Course $course, User $authenticatedUser)
    {
        $this->checkToolAccess();
        $form = $this->formFactory->create(
            new CourseType($authenticatedUser),
            $course
        );
        $form->handleRequest($this->request);

        if ($form->isValid()) {
            $this->cursusManager->persistCourse($course);

            $message = $this->translator->trans(
                'course_edition_confirm_msg' ,
                array(),
                'cursus'
            );
            $session = $this->request->getSession();
            $session->getFlashBag()->add('success', $message);

            return new JsonResponse('success', 200);
        } else {

            return array(
                'form' => $form->createView(),
                'course' => $course
            );
        }
    }

    /**
     * @EXT\Route(
     *     "cursus/course/{course}/delete",
     *     name="claro_cursus_course_delete",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     *
     * @param Course $course
     */
    public function courseDeleteAction(Course $course)
    {
        $this->checkToolAccess();
        $this->cursusManager->deleteCourse($course);

        $message = $this->translator->trans(
            'course_deletion_confirm_msg' ,
            array(),
            'cursus'
        );
        $session = $this->request->getSession();
        $session->getFlashBag()->add('success', $message);

        return new JsonResponse('success', 200);
    }

    /**
     * @EXT\Route(
     *     "/cursus/course/{course}/description/display",
     *     name="claro_cursus_course_display_description",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     * @EXT\Template("ClarolineCursusBundle:Course:courseDescriptionDisplayModal.html.twig")
     *
     * @param Course $course
     */
    public function courseDescriptionDisplayAction(Course $course)
    {
        $this->checkToolAccess();

        return array('description' => $course->getDescription());
    }

    /**
     * @EXT\Route(
     *     "cursus/course/{course}/management",
     *     name="claro_cursus_course_management",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     * @EXT\Template()
     *
     * @param Cursus $cursus
     *
     */
    public function courseManagementAction(Course $course)
    {
        $this->checkToolAccess();
        $displayedWords = array();

        foreach (CursusDisplayedWord::$defaultKey as $key) {
            $displayedWords[$key] = $this->cursusManager->getDisplayedWord($key);
        }
        $sessions = $this->cursusManager->getSessionsByCourse($course);
        $sessionsTab = array();

        foreach ($sessions as $session) {
            $status = $session->getSessionStatus();

            if (!isset($sessionsTab[$status])) {
                $sessionsTab[$status] = array();
            }
            $sessionsTab[$status][] = $session;
        }

        return array(
            'defaultWords' => CursusDisplayedWord::$defaultKey,
            'displayedWords' => $displayedWords,
            'type' => 'course',
            'course' => $course,
            'sessionsTab' => $sessionsTab
        );
    }

    /**
     * @EXT\Route(
     *     "cursus/course/{course}/session/create/form",
     *     name="claro_cursus_course_session_create_form",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     * @EXT\Template("ClarolineCursusBundle:Course:courseSessionCreateModalForm.html.twig")
     */
    public function courseSessionCreateFormAction(Course $course)
    {
        $this->checkToolAccess();
        $form = $this->formFactory->create(new CourseSessionType());

        return array('form' => $form->createView(), 'course' => $course);
    }

    /**
     * @EXT\Route(
     *     "cursus/course/{course}/session/create",
     *     name="claro_cursus_course_session_create",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     * @EXT\Template("ClarolineCursusBundle:Course:courseSessionCreateModalForm.html.twig")
     */
    public function courseSessionCreateAction(Course $course)
    {
        $this->checkToolAccess();
        $session = new CourseSession();
        $form = $this->formFactory->create(new CourseSessionType(), $session);
        $form->handleRequest($this->request);

        if ($form->isValid()) {
            $creationDate = new \DateTime();
            $session->setCreationDate($creationDate);
            $session->setCourse($course);
            $this->cursusManager->persistCourseSession($session);

            return new JsonResponse('success', 200);
        } else {

            return array('form' => $form->createView(), 'course' => $course);
        }
    }

    private function checkToolAccess()
    {
        $cursusTool = $this->toolManager->getAdminToolByName('claroline_cursus_tool');

        if (is_null($cursusTool) ||
            !$this->securityContext->isGranted('OPEN', $cursusTool)) {

            throw new AccessDeniedException();
        }
    }
}
