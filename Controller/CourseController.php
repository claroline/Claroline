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
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Library\Workspace\Configuration;
use Claroline\CoreBundle\Manager\RoleManager;
use Claroline\CoreBundle\Manager\ToolManager;
use Claroline\CoreBundle\Manager\WorkspaceManager;
use Claroline\CursusBundle\Entity\Course;
use Claroline\CursusBundle\Entity\CourseSession;
use Claroline\CursusBundle\Entity\CourseSessionUser;
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
    private $roleManager;
    private $securityContext;
    private $templateDir;
    private $toolManager;
    private $translator;
    private $workspaceManager;

    /**
     * @DI\InjectParams({
     *     "cursusManager"    = @DI\Inject("claroline.manager.cursus_manager"),
     *     "formFactory"      = @DI\Inject("form.factory"),
     *     "requestStack"     = @DI\Inject("request_stack"),
     *     "roleManager"      = @DI\Inject("claroline.manager.role_manager"),
     *     "securityContext"  = @DI\Inject("security.context"),
     *     "templateDir"      = @DI\Inject("%claroline.param.templates_directory%"),
     *     "toolManager"      = @DI\Inject("claroline.manager.tool_manager"),
     *     "translator"       = @DI\Inject("translator"),
     *     "workspaceManager" = @DI\Inject("claroline.manager.workspace_manager")
     * })
     */
    public function __construct(
        CursusManager $cursusManager,
        FormFactory $formFactory,
        RequestStack $requestStack,
        RoleManager $roleManager,
        SecurityContextInterface $securityContext,
        $templateDir,
        ToolManager $toolManager,
        Translator $translator,
        WorkspaceManager $workspaceManager
    )
    {
        $this->cursusManager = $cursusManager;
        $this->formFactory = $formFactory;
        $this->request = $requestStack->getCurrentRequest();
        $this->roleManager = $roleManager;
        $this->securityContext = $securityContext;
        $this->templateDir = $templateDir;
        $this->toolManager = $toolManager;
        $this->translator = $translator;
        $this->workspaceManager = $workspaceManager;
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
    public function courseSessionCreateAction(Course $course, User $authenticatedUser)
    {
        $this->checkToolAccess();
        $session = new CourseSession();
        $form = $this->formFactory->create(new CourseSessionType(), $session);
        $form->handleRequest($this->request);

        if ($form->isValid()) {
            $creationDate = new \DateTime();
            $session->setCreationDate($creationDate);
            $session->setCourse($course);
            $session->setPublicRegistration($course->getPublicRegistration());
            $session->setPublicUnregistration($course->getPublicUnregistration());
            $session->setRegistrationValidation($course->getRegistrationValidation());
            $workspace = $this->generateWorkspace($course, $session, $authenticatedUser);
            $session->setWorkspace($workspace);
            $learnerRole = $this->generateRoleForSession(
                $workspace,
                $course->getLearnerRoleName(),
                0
            );
            $tutorRole = $this->generateRoleForSession(
                $workspace,
                $course->getTutorRoleName(),
                1
            );
            $session->setLearnerRole($learnerRole);
            $session->setTutorRole($tutorRole);
            $this->cursusManager->persistCourseSession($session);

            return new JsonResponse('success', 200);
        } else {

            return array('form' => $form->createView(), 'course' => $course);
        }
    }

    /**
     * @EXT\Route(
     *     "cursus/course/session/{session}/view/management",
     *     name="claro_cursus_course_session_view_management",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     * @EXT\Template()
     *
     * @param CourseSession $session
     */
    public function courseSessionViewManagementAction(CourseSession $session)
    {
        $this->checkToolAccess();
        $sessionUsers = $this->cursusManager->getSessionUsersBySession($session);
        $learners = array();
        $tutors = array();

        foreach ($sessionUsers as $sessionUser) {

            if ($sessionUser->getUserType() === 0) {
                $learners[] = $sessionUser;
            } elseif ($sessionUser->getUserType() === 1) {
                $tutors[] = $sessionUser;
            }
        }

        return array(
            'session' => $session,
            'learners' => $learners,
            'tutors' => $tutors
        );
    }

    /**
     * @EXT\Route(
     *     "cursus/course/session/{session}/registration/unregistered/users/{userType}/list/page/{page}/max/{max}/ordered/by/{orderedBy}/order/{order}/search/{search}",
     *     name="claro_cursus_course_session_registration_unregistered_users_list",
     *     defaults={"userType"=0, "page"=1, "search"="", "max"=50, "orderedBy"="firstName","order"="ASC"},
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     * @EXT\Template()
     *
     * Displays the list of users who are not registered to the session.
     *
     * @param CourseSession $session
     * @param integer $userType
     * @param string  $search
     * @param integer $page
     * @param integer $max
     * @param string  $orderedBy
     * @param string  $order
     */
    public function courseSessionRegistrationUnregisteredUsersListAction(
        CourseSession $session,
        $userType = 0,
        $search = '',
        $page = 1,
        $max = 50,
        $orderedBy = 'firstName',
        $order = 'ASC'
    )
    {
        $this->checkToolAccess();

        $users = $search === '' ?
            $this->cursusManager->getUnregisteredUsersBySession(
                $session,
                $userType,
                $orderedBy,
                $order,
                $page,
                $max
            ) :
            $this->cursusManager->getSearchedUnregisteredUsersBySession(
                $session,
                $userType,
                $search,
                $orderedBy,
                $order,
                $page,
                $max
            );

        return array(
            'session' => $session,
            'userType' => $userType,
            'users' => $users,
            'search' => $search,
            'max' => $max,
            'orderedBy' => $orderedBy,
            'order' => $order
        );
    }

    /**
     * @EXT\Route(
     *     "cursus/course/session/{session}/register/user/{user}/type/{userType}",
     *     name="claro_cursus_course_session_register_user",
     *     options = {"expose"=true}
     * )
     * @EXT\Method("POST")
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     *
     * @param CourseSession $session
     * @param User $user
     * @param int $userType
     */
    public function courseSessionUserRegisterAction(
        CourseSession $session,
        User $user,
        $userType
    )
    {
        $this->checkToolAccess();
        $this->cursusManager->registerUsersToSession($session, array($user), $userType);

        return new JsonResponse('success', 200);
    }

    /**
     * @EXT\Route(
     *     "cursus/course/session/unregister/user/{sessionUser}",
     *     name="claro_cursus_course_session_unregister_user",
     *     options = {"expose"=true}
     * )
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     *
     * @param CourseSessionUser $sessionUser
     */
    public function courseSessionUserUnregisterAction(CourseSessionUser $sessionUser)
    {
        $this->checkToolAccess();
        $this->cursusManager->unregisterUsersFromSession(array($sessionUser));

        return new JsonResponse('success', 200);
    }

    private function generateWorkspace(Course $course, CourseSession $session, User $user)
    {
        $model = $course->getWorkspaceModel();
        $description = $course->getDescription();
        $displayable = false;
        $selfRegistration = false;
        $selfUnregistration = false;
        $registrationValidation = false;
        $name = $course->getTitle() .
            ' [' .
            $session->getName() .
            ']';
        $code = $this->generateWorkspaceCode($course->getCode());

        if (is_null($model)) {
            $ds = DIRECTORY_SEPARATOR;
            $config = Configuration::fromTemplate(
                $this->templateDir . $ds . 'default.zip'
            );
            $config->setWorkspaceName($name);
            $config->setWorkspaceCode($code);
            $config->setDisplayable($displayable);
            $config->setSelfRegistration($selfRegistration);
            $config->setSelfUnregistration($selfUnregistration);
            $config->setRegistrationValidation($registrationValidation);
            $config->setWorkspaceDescription($description);
            $workspace = $this->workspaceManager->create($config, $user);
        } else {
            $workspace = $this->workspaceManager->createWorkspaceFromModel(
                $model,
                $user,
                $name,
                $code,
                $description,
                $displayable,
                $selfRegistration,
                $selfUnregistration
            );
        }
        $workspace->setWorkspaceType(0);
        $this->workspaceManager->editWorkspace($workspace);

        return $workspace;
    }

    private function generateRoleForSession(Workspace $workspace, $roleName, $type)
    {
        if (empty($roleName)) {

            if ($type === 1) {
                $role = $this->roleManager->getManagerRole($workspace);
            } else {
                $role = $this->roleManager->getCollaboratorRole($workspace);
            }
        } else {
            $roles = $this->roleManager->getRolesByWorkspaceCodeAndTranslationKey(
                $workspace->getCode(),
                $roleName
            );

            if (count($roles) > 0) {
                $role = $roles[0];
            } else {
                $guid = $workspace->getGuid();
                $wsRoleName = 'ROLE_WS_' . strtoupper($roleName) . '_' . $guid;

                $role = $this->roleManager->getRoleByName($wsRoleName);

                if (is_null($role)) {
                    $role = $this->roleManager->createWorkspaceRole(
                        $wsRoleName,
                        $roleName,
                        $workspace
                    );
                }
            }
        }

        return $role;
    }

    private function generateWorkspaceCode($code)
    {
        $workspaceCodes = $this->workspaceManager->getWorkspaceCodesWithPrefix($code);
        $existingCodes = array();

        foreach ($workspaceCodes as $wsCode) {
            $existingCodes[] = $wsCode['code'];
        }

        $index = count($existingCodes) + 1;
        $currentCode = $code . '_' . $index;
        $upperCurrentCode = strtoupper($currentCode);

        while (in_array($upperCurrentCode, $existingCodes)) {
            $index++;
            $currentCode = $code . '_' . $index;
            $upperCurrentCode = strtoupper($currentCode);
        }

        return $currentCode;
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
