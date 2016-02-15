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

use Claroline\CoreBundle\Entity\Group;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Manager\MailManager;
use Claroline\CoreBundle\Manager\RoleManager;
use Claroline\CoreBundle\Manager\ToolManager;
use Claroline\CoreBundle\Manager\WorkspaceManager;
use Claroline\CursusBundle\Entity\Course;
use Claroline\CursusBundle\Entity\CourseRegistrationQueue;
use Claroline\CursusBundle\Entity\CourseSession;
use Claroline\CursusBundle\Entity\CourseSessionRegistrationQueue;
use Claroline\CursusBundle\Entity\CourseSessionGroup;
use Claroline\CursusBundle\Entity\CourseSessionUser;
use Claroline\CursusBundle\Entity\CursusDisplayedWord;
use Claroline\CursusBundle\Form\CourseQueuedUserTransferType;
use Claroline\CursusBundle\Form\CourseSessionEditType;
use Claroline\CursusBundle\Form\CourseSessionType;
use Claroline\CursusBundle\Form\CourseType;
use Claroline\CursusBundle\Form\FileSelectType;
use Claroline\CursusBundle\Manager\CursusManager;
use JMS\DiExtraBundle\Annotation as DI;
use JMS\SecurityExtraBundle\Annotation as SEC;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * @DI\Tag("security.secure_service")
 * @SEC\PreAuthorize("canOpenAdminTool('claroline_cursus_tool')")
 */
class CourseController extends Controller
{
    private $authorization;
    private $cursusManager;
    private $formFactory;
    private $mailManager;
    private $request;
    private $roleManager;
    private $router;
    private $toolManager;
    private $translator;
    private $workspaceManager;

    /**
     * @DI\InjectParams({
     *     "authorization"    = @DI\Inject("security.authorization_checker"),
     *     "cursusManager"    = @DI\Inject("claroline.manager.cursus_manager"),
     *     "formFactory"      = @DI\Inject("form.factory"),
     *     "mailManager"      = @DI\Inject("claroline.manager.mail_manager"),
     *     "requestStack"     = @DI\Inject("request_stack"),
     *     "roleManager"      = @DI\Inject("claroline.manager.role_manager"),
     *     "router"           = @DI\Inject("router"),
     *     "toolManager"      = @DI\Inject("claroline.manager.tool_manager"),
     *     "translator"       = @DI\Inject("translator"),
     *     "workspaceManager" = @DI\Inject("claroline.manager.workspace_manager")
     * })
     */
    public function __construct(
        AuthorizationCheckerInterface $authorization,
        CursusManager $cursusManager,
        FormFactory $formFactory,
        MailManager $mailManager,
        RequestStack $requestStack,
        RoleManager $roleManager,
        RouterInterface $router,
        ToolManager $toolManager,
        TranslatorInterface $translator,
        WorkspaceManager $workspaceManager
    )
    {
        $this->authorization = $authorization;
        $this->cursusManager = $cursusManager;
        $this->formFactory = $formFactory;
        $this->mailManager = $mailManager;
        $this->request = $requestStack->getCurrentRequest();
        $this->roleManager = $roleManager;
        $this->router = $router;
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
        $displayedWords = array();

        foreach (CursusDisplayedWord::$defaultKey as $key) {
            $displayedWords[$key] = $this->cursusManager->getDisplayedWord($key);
        }
        $courses = $this->cursusManager->getAllCourses(
            $search,
            $orderedBy,
            $order,
            true,
            $page,
            $max
        );

        return array(
            'defaultWords' => CursusDisplayedWord::$defaultKey,
            'displayedWords' => $displayedWords,
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
     * @EXT\Template("ClarolineCursusBundle:Course:courseCreateForm.html.twig")
     */
    public function courseCreateFormAction(User $authenticatedUser)
    {
        $displayedWords = array();

        foreach (CursusDisplayedWord::$defaultKey as $key) {
            $displayedWords[$key] = $this->cursusManager->getDisplayedWord($key);
        }
        $form = $this->formFactory->create(
            new CourseType($authenticatedUser, $this->cursusManager, $this->translator),
            new Course()
        );

        return array(
            'form' => $form->createView(),
            'displayedWords' => $displayedWords
        );
    }

    /**
     * @EXT\Route(
     *     "cursus/course/create",
     *     name="claro_cursus_course_create",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     * @EXT\Template("ClarolineCursusBundle:Course:courseCreateForm.html.twig")
     */
    public function courseCreateAction(User $authenticatedUser)
    {
        $course = new Course();
        $form = $this->formFactory->create(
            new CourseType($authenticatedUser, $this->cursusManager, $this->translator),
            $course
        );
        $form->handleRequest($this->request);

        if ($form->isValid()) {
            $icon = $form->get('icon')->getData();

            if (!is_null($icon)) {
                $hashName = $this->cursusManager->saveIcon($icon);
                $course->setIcon($hashName);
            }
            $this->cursusManager->persistCourse($course);

            $message = $this->translator->trans(
                'course_creation_confirm_msg' ,
                array(),
                'cursus'
            );
            $session = $this->request->getSession();
            $session->getFlashBag()->add('success', $message);

            return new RedirectResponse(
                $this->router->generate('claro_cursus_tool_course_index')
            );
        } else {
            $displayedWords = array();

            foreach (CursusDisplayedWord::$defaultKey as $key) {
                $displayedWords[$key] = $this->cursusManager->getDisplayedWord($key);
            }

            return array(
                'form' => $form->createView(),
                'displayedWords' => $displayedWords
            );
        }
    }

    /**
     * @EXT\Route(
     *     "cursus/{cursusId}/course/{course}/edit/form/source/{source}",
     *     name="claro_cursus_course_edit_form",
     *     defaults={"source"=0, "cursusId"=-1},
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     * @EXT\Template()
     *
     * @param Course $course
     * @param int $source
     */
    public function courseEditFormAction(
        Course $course,
        User $authenticatedUser,
        $source = 0,
        $cursusId = -1
    )
    {
        $cursus = intval($source) === 2 ?
            $this->cursusManager->getOneCursusById($cursusId) :
            null;
        $displayedWords = array();

        foreach (CursusDisplayedWord::$defaultKey as $key) {
            $displayedWords[$key] = $this->cursusManager->getDisplayedWord($key);
        }
        $form = $this->formFactory->create(
            new CourseType($authenticatedUser, $this->cursusManager, $this->translator),
            $course
        );

        return array(
            'form' => $form->createView(),
            'course' => $course,
            'displayedWords' => $displayedWords,
            'source' => $source,
            'cursus' => $cursus,
            'cursusId' => $cursusId
        );
    }

    /**
     * @EXT\Route(
     *     "cursus/{cursusId}/course/{course}/edit/source/{source}",
     *     name="claro_cursus_course_edit",
     *     defaults={"source"=0, "cursusId"=-1},
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     * @EXT\Template("ClarolineCursusBundle:Course:courseEditForm.html.twig")
     *
     * @param Course $course
     * @param int $source
     */
    public function courseEditAction(
        Course $course,
        User $authenticatedUser,
        $source = 0,
        $cursusId = -1
    )
    {
        $form = $this->formFactory->create(
            new CourseType($authenticatedUser, $this->cursusManager, $this->translator),
            $course
        );
        $form->handleRequest($this->request);

        if ($form->isValid()) {
            $icon = $form->get('icon')->getData();

            if (!is_null($icon)) {
                $hashName = $this->cursusManager->changeIcon($course, $icon);
                $course->setIcon($hashName);
            }
            $this->cursusManager->persistCourse($course);

            $message = $this->translator->trans(
                'course_edition_confirm_msg' ,
                array(),
                'cursus'
            );
            $session = $this->request->getSession();
            $session->getFlashBag()->add('success', $message);
            $route = intval($source) === 0 ?
                $this->router->generate('claro_cursus_tool_course_index') :
                $this->router->generate(
                    'claro_cursus_course_management',
                    array('course' => $course->getId(), 'cursusId' => $cursusId)
                );
            return new RedirectResponse($route);
        } else {
            $cursus = intval($source) === 2 ?
                $this->cursusManager->getOneCursusById($cursusId) :
                null;
            $displayedWords = array();

            foreach (CursusDisplayedWord::$defaultKey as $key) {
                $displayedWords[$key] = $this->cursusManager->getDisplayedWord($key);
            }

            return array(
                'form' => $form->createView(),
                'course' => $course,
                'displayedWords' => $displayedWords,
                'source' => $source,
                'cursus' => $cursus,
                'cursusId' => $cursusId
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
        return array('description' => $course->getDescription());
    }

    /**
     * @EXT\Route(
     *     "cursus/{cursusId}/course/{course}/management",
     *     name="claro_cursus_course_management",
     *     defaults={"cursusId"=-1},
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     * @EXT\Template()
     *
     * @param Course $course
     *
     */
    public function courseManagementAction(Course $course, $cursusId = -1)
    {
        $displayedWords = array();
        $cursus = $this->cursusManager->getOneCursusById($cursusId);
        $type = is_null($cursus) ? 'course' : 'cursus';

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
        $queues = $this->cursusManager->getCourseQueuesByCourse($course);

        return array(
            'defaultWords' => CursusDisplayedWord::$defaultKey,
            'displayedWords' => $displayedWords,
            'type' => $type,
            'cursus' => $cursus,
            'course' => $course,
            'sessionsTab' => $sessionsTab,
            'queues' => $queues
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
        $session = new CourseSession();
        $session->setPublicRegistration($course->getPublicRegistration());
        $session->setPublicUnregistration($course->getPublicUnregistration());
        $session->setRegistrationValidation($course->getRegistrationValidation());
        $session->setMaxUsers($course->getMaxUsers());
        $session->setUserValidation($course->getUserValidation());
        $validators = $course->getValidators();

        foreach ($validators as $validator) {
            $session->addValidator($validator);
        }
        $form = $this->formFactory->create(
            new CourseSessionType($this->cursusManager, $this->translator),
            $session
        );

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
        $session = new CourseSession();
        $form = $this->formFactory->create(
            new CourseSessionType($this->cursusManager, $this->translator),
            $session
        );
        $form->handleRequest($this->request);

        if ($form->isValid()) {
            $creationDate = new \DateTime();
            $session->setCreationDate($creationDate);
            $this->cursusManager->createCourseSessionFromSession(
                $session,
                $course,
                $authenticatedUser
            );

            return new JsonResponse('success', 200);
        } else {

            return array('form' => $form->createView(), 'course' => $course);
        }
    }

    /**
     * @EXT\Route(
     *     "cursus/course/session/{session}/edit/form",
     *     name="claro_cursus_course_session_edit_form",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     * @EXT\Template("ClarolineCursusBundle:Course:courseSessionEditModalForm.html.twig")
     */
    public function courseSessionEditFormAction(CourseSession $session)
    {
        $form = $this->formFactory->create(
            new CourseSessionEditType($session, $this->cursusManager, $this->translator),
            $session
        );

        return array('form' => $form->createView(), 'session' => $session);
    }

    /**
     * @EXT\Route(
     *     "cursus/course/session/{session}/edit",
     *     name="claro_cursus_course_session_edit",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     * @EXT\Template("ClarolineCursusBundle:Course:courseSessionEditModalForm.html.twig")
     */
    public function courseSessionEditAction(CourseSession $session)
    {
        $form = $this->formFactory->create(
            new CourseSessionEditType($session, $this->cursusManager, $this->translator),
            $session
        );
        $form->handleRequest($this->request);

        if ($form->isValid()) {
            $this->cursusManager->persistCourseSession($session);

            return new JsonResponse('success', 200);
        } else {

            return array('form' => $form->createView(), 'session' => $session);
        }
    }

    /**
     * @EXT\Route(
     *     "cursus/course/session/{session}/delete/with/workspace/{mode}",
     *     name="claro_cursus_course_session_delete",
     *     options={"expose"=true}
     * )
     * @EXT\Method("DELETE")
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     */
    public function courseSessionDeleteAction(CourseSession $session, $mode)
    {
        $withWorkspace = (intval($mode) === 1);
        $this->cursusManager->deleteCourseSession($session, $withWorkspace);

        return new JsonResponse('success', 200);
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
        $sessionUsers = $this->cursusManager->getSessionUsersBySession($session);
        $sessionGroups = $this->cursusManager->getSessionGroupsBySession($session);
        $queues = $this->cursusManager->getSessionQueuesBySession($session);
        $learners = array();
        $tutors = array();
        $learnersGroups = array();
        $tutorsGroups = array();

        foreach ($sessionUsers as $sessionUser) {

            if ($sessionUser->getUserType() === 0) {
                $learners[] = $sessionUser;
            } elseif ($sessionUser->getUserType() === 1) {
                $tutors[] = $sessionUser;
            }
        }

        foreach ($sessionGroups as $sessionGroup) {

            if ($sessionGroup->getGroupType() === 0) {
                $learnersGroups[] = $sessionGroup;
            } elseif ($sessionGroup->getGroupType() === 1) {
                $tutorsGroups[] = $sessionGroup;
            }
        }

        return array(
            'session' => $session,
            'learners' => $learners,
            'tutors' => $tutors,
            'learnersGroups' => $learnersGroups,
            'tutorsGroups' => $tutorsGroups,
            'queues' => $queues
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
        $users = $this->cursusManager->getUnregisteredUsersBySession(
            $session,
            $userType,
            $search,
            $orderedBy,
            $order,
            true,
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
        $results = array();
        $sessionUsers = $this->cursusManager->registerUsersToSession(
            $session,
            array($user),
            $userType
        );

        foreach ($sessionUsers as $sessionUser) {
            $user = $sessionUser->getUser();
            $results[] = array(
                'id' => $sessionUser->getId(),
                'user_type' => $sessionUser->getUserType(),
                'user_id' => $user->getId(),
                'username' => $user->getUsername(),
                'user_first_name' => $user->getFirstName(),
                'user_last_name' => $user->getLastName()
            );
        }

        return new JsonResponse($results, 200);
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
        $this->cursusManager->unregisterUsersFromSession(array($sessionUser));

        return new JsonResponse('success', 200);
    }

    /**
     * @EXT\Route(
     *     "cursus/course/session/{session}/registration/unregistered/groups/{groupType}/list/page/{page}/max/{max}/ordered/by/{orderedBy}/order/{order}/search/{search}",
     *     name="claro_cursus_course_session_registration_unregistered_groups_list",
     *     defaults={"groupType"=0, "page"=1, "search"="", "max"=50, "orderedBy"="name","order"="ASC"},
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     * @EXT\Template()
     *
     * Displays the list of users who are not registered to the session.
     *
     * @param CourseSession $session
     * @param integer $groupType
     * @param string  $search
     * @param integer $page
     * @param integer $max
     * @param string  $orderedBy
     * @param string  $order
     */
    public function courseSessionRegistrationUnregisteredGroupsListAction(
        CourseSession $session,
        $groupType = 0,
        $search = '',
        $page = 1,
        $max = 50,
        $orderedBy = 'name',
        $order = 'ASC'
    )
    {
        $groups = $this->cursusManager->getUnregisteredGroupsBySession(
            $session,
            $groupType,
            $search,
            $orderedBy,
            $order,
            true,
            $page,
            $max
        );

        return array(
            'session' => $session,
            'groupType' => $groupType,
            'groups' => $groups,
            'search' => $search,
            'max' => $max,
            'orderedBy' => $orderedBy,
            'order' => $order
        );
    }

    /**
     * @EXT\Route(
     *     "cursus/course/session/{session}/register/group/{group}/type/{groupType}",
     *     name="claro_cursus_course_session_register_group",
     *     options = {"expose"=true}
     * )
     * @EXT\Method("POST")
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     *
     * @param CourseSession $session
     * @param Group $group
     * @param int $groupType
     */
    public function courseSessionGroupRegisterAction(
        CourseSession $session,
        Group $group,
        $groupType
    )
    {
        $this->cursusManager->registerGroupToSessions(
            array($session),
            $group,
            $groupType
        );

        return new JsonResponse('success', 200);
    }

    /**
     * @EXT\Route(
     *     "cursus/course/session/unregister/group/{sessionGroup}",
     *     name="claro_cursus_course_session_unregister_group",
     *     options = {"expose"=true}
     * )
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     *
     * @param CourseSessionGroup $sessionGroup
     */
    public function courseSessionGroupUnregisterAction(CourseSessionGroup $sessionGroup)
    {
        $this->cursusManager->unregisterGroupFromSession($sessionGroup);

        return new JsonResponse('success', 200);
    }

    /**
     * @EXT\Route(
     *     "cursus/course/session/{session}/confirmation/mail/send",
     *     name="claro_cursus_course_session_confirmation_mail_send",
     *     options = {"expose"=true}
     * )
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     *
     * @param CourseSession $session
     */
    public function courseSessionConfirmationMailSendAction(CourseSession $session)
    {
        $confirmationEmail = $this->cursusManager->getConfirmationEmail();

        if (!is_null($confirmationEmail)) {
            $users = array();
            $sessionUsers = $session->getSessionUsers();

            foreach ($sessionUsers as $sessionUser) {

                if ($sessionUser->getUserType() === 0) {
                    $users[] = $sessionUser->getUser();
                }
            }
            $course = $session->getCourse();
            $startDate = $session->getStartDate();
            $endDate = $session->getEndDate();
            $title = $confirmationEmail->getTitle();
            $content = $confirmationEmail->getContent();
            $title = str_replace('%course%', $course->getTitle(), $title);
            $content = str_replace('%course%', $course->getTitle(), $content);
            $title = str_replace('%session%', $session->getName(), $title);
            $content = str_replace('%session%', $session->getName(), $content);

            if (!is_null($startDate)) {
                $title = str_replace('%start_date%', $session->getStartDate(), $title);
                $content = str_replace('%start_date%', $session->getStartDate(), $content);
            }

            if (!is_null($endDate)) {
                $title = str_replace('%end_date%', $session->getEndDate(), $title);
                $content = str_replace('%end_date%', $session->getEndDate(), $content);
            }
            $this->mailManager->send($title, $content, $users);
        }

        return new JsonResponse('success', 200);
    }

    /**
     * @EXT\Route(
     *     "cursus/course/session/{session}/user/{user}/confirmation/mail/send",
     *     name="claro_cursus_course_session_user_confirmation_mail_send",
     *     options = {"expose"=true}
     * )
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     *
     * @param User $user
     */
    public function courseSessionUserConfirmationMailSendAction(
        CourseSession $session,
        User $user
    )
    {
        $confirmationEmail = $this->cursusManager->getConfirmationEmail();

        if (!is_null($confirmationEmail)) {
            $course = $session->getCourse();
            $startDate = $session->getStartDate();
            $endDate = $session->getEndDate();
            $title = $confirmationEmail->getTitle();
            $content = $confirmationEmail->getContent();
            $title = str_replace('%course%', $course->getTitle(), $title);
            $content = str_replace('%course%', $course->getTitle(), $content);
            $title = str_replace('%session%', $session->getName(), $title);
            $content = str_replace('%session%', $session->getName(), $content);

            if (!is_null($startDate)) {
                $title = str_replace('%start_date%', $session->getStartDate()->format('d-m-Y'), $title);
                $content = str_replace('%start_date%', $session->getStartDate()->format('d-m-Y'), $content);
            }

            if (!is_null($endDate)) {
                $title = str_replace('%end_date%', $session->getEndDate()->format('d-m-Y'), $title);
                $content = str_replace('%end_date%', $session->getEndDate()->format('d-m-Y'), $content);
            }
            $this->mailManager->send($title, $content, array($user));
        }

        return new JsonResponse('success', 200);
    }

    /**
     * @EXT\Route(
     *     "cursus/course/session/registration/queue/{queue}/accept",
     *     name="claro_cursus_course_session_user_registration_accept",
     *     options = {"expose"=true}
     * )
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     *
     * @param CourseSession $session
     * @param User $user
     */
    public function courseSessionUserRegistrationAcceptAction(
        CourseSessionRegistrationQueue $queue
    )
    {
        $user = $queue->getUser();
        $session = $queue->getSession();
        $this->cursusManager->registerUsersToSession($session, array($user), 0);
        $this->cursusManager->deleteSessionQueue($queue);

        return new JsonResponse('success', 200);
    }

    /**
     * @EXT\Route(
     *     "cursus/course/session/registration/queue/{queue}/decline",
     *     name="claro_cursus_course_session_user_registration_decline",
     *     options = {"expose"=true}
     * )
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     *
     * @param CourseSession $session
     * @param User $user
     */
    public function courseSessionUserRegistrationDeclineAction(
        CourseSessionRegistrationQueue $queue
    )
    {
        $this->cursusManager->deleteSessionQueue($queue);

        return new JsonResponse('success', 200);
    }

    /**
     * @EXT\Route(
     *     "cursus/course/queue/{queue}/user/transfer/form",
     *     name="claro_cursus_course_queued_user_transfer_form",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     * @EXT\Template("ClarolineCursusBundle:Course:courseQueuedUserTransferModalForm.html.twig")
     */
    public function courseQueuedUserTransferFormAction(CourseRegistrationQueue $queue)
    {
        $course = $queue->getCourse();
        $form = $this->formFactory->create(new CourseQueuedUserTransferType($course));

        return array(
            'form' => $form->createView(),
            'queue' => $queue
        );
    }

    /**
     * @EXT\Route(
     *     "cursus/course/queue/{queue}/user/transfer",
     *     name="claro_cursus_course_queued_user_transfer",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     * @EXT\Template("ClarolineCursusBundle:Course:courseQueuedUserTransferModalForm.html.twig")
     */
    public function courseQueuedUserTransferAction(CourseRegistrationQueue $queue)
    {
        $queueId = $queue->getId();
        $course = $queue->getCourse();
        $form = $this->formFactory->create(new CourseQueuedUserTransferType($course));
        $form->handleRequest($this->request);

        if ($form->isValid()) {
            $session = $form->get('session')->getData();
            $this->cursusManager->transferQueuedUserToSession($queue, $session);

            return new JsonResponse($queueId, 200);
        } else {

            return array(
                'form' => $form->createView(),
                'queue' => $queue
            );
        }
    }

    /**
     * @EXT\Route(
     *     "cursus/course/session/{session}/default/switch",
     *     name="claro_cursus_course_session_default_switch",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     */
    public function courseSessionDefaultSwitchAction(CourseSession $session)
    {
        $isDefault = !$session->isDefaultSession();
        $session->setDefaultSession($isDefault);
        $this->cursusManager->persistCourseSession($session);

        return new JsonResponse(
            array('id' => $session->getId(), 'default' => $isDefault),
            200
        );
    }

    /**
     * @EXT\Route(
     *     "/courses/export",
     *     name="claro_cursus_courses_export",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     */
    public function coursesExportAction()
    {
        $courses = $this->cursusManager->getAllCourses('', 'id', 'ASC', false);
        $zipName = 'courses.zip';
        $mimeType = 'application/zip';
        $file = $this->cursusManager->zipDatas($courses, 'course');;

        $response = new StreamedResponse();
        $response->setCallBack(
            function () use ($file) {
                readfile($file);
            }
        );
        $response->headers->set('Content-Transfer-Encoding', 'octet-stream');
        $response->headers->set('Content-Type', 'application/force-download');
        $response->headers->set('Content-Disposition', 'attachment; filename=' . urlencode($zipName));
        $response->headers->set('Content-Type', $mimeType);
        $response->headers->set('Connection', 'close');
        $response->send();

        return new Response();
    }

    /**
     * @EXT\Route(
     *     "/courses/import/form",
     *     name="claro_cursus_courses_import_form",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     * @EXT\Template("ClarolineCursusBundle:Course:coursesImportModalForm.html.twig")
     */
    public function coursesImportFormAction()
    {
        $form = $this->formFactory->create(new FileSelectType());

        return array('form' => $form->createView());
    }

    /**
     * @EXT\Route(
     *     "/courses/import",
     *     name="claro_cursus_courses_import",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     * @EXT\Template("ClarolineCursusBundle:Course:coursesImportModalForm.html.twig")
     */
    public function coursesImportAction()
    {
        $form = $this->formFactory->create(new FileSelectType());
        $form->handleRequest($this->request);
        $file = $form->get('archive')->getData();
        $zip = new \ZipArchive();

        if (empty($file) || !$zip->open($file) || !$zip->getStream('courses.json')) {
            $form->get('archive')->addError(
                new FormError($this->translator->trans('invalid_file', array(), 'cursus'))
            );
        }

        if ($form->isValid()) {
            $stream = $zip->getStream('courses.json');
            $contents = '';

            while (!feof($stream)) {
                $contents .= fread($stream, 2);
            }
            fclose($stream);
            $courses = json_decode($contents, true);
            $this->cursusManager->importCourses($courses);

            $iconsDir = $this->container->getParameter('claroline.param.thumbnails_directory') . '/';

            for ($i = 0; $i < $zip->numFiles; $i++) {
                $name = $zip->getNameIndex($i);

                if (strpos($name, 'icons/') !== 0) {
                    continue;
                }
                $iconFileName = $iconsDir . substr($name, 6);
                $stream = $zip->getStream($name);
                $destStream = fopen($iconFileName, 'w');

                while ($data = fread($stream, 1024)) {
                    fwrite($destStream, $data);
                }
                fclose($stream);
                fclose($destStream);
            }
            $zip->close();

            return new JsonResponse('success', 200);
        } else {

            return array('form' => $form->createView());
        }
    }

    /**
     * @EXT\Route(
     *     "/course/workspace/{workspace}/retrieve/roles/translation/keys",
     *     name="course_workspace_roles_translation_keys_retrieve",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     */
    public function retrieveRolesTranslationKeysFromWorkspaceAction(Workspace $workspace)
    {
        if (!$this->authorization->isGranted('OPEN', $workspace)) {

            throw new AccessDeniedException();
        }
        $results = array();
        $roles = $this->roleManager->getRolesByWorkspace($workspace);

        foreach ($roles as $role) {
            $results[] = $role->getTranslationKey();
        }

        return new JsonResponse($results);
    }
}
