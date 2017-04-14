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
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Claroline\CoreBundle\Manager\ToolManager;
use Claroline\CursusBundle\Entity\CourseRegistrationQueue;
use Claroline\CursusBundle\Entity\CourseSession;
use Claroline\CursusBundle\Entity\CourseSessionRegistrationQueue;
use Claroline\CursusBundle\Entity\CourseSessionUser;
use Claroline\CursusBundle\Manager\CursusManager;
use JMS\DiExtraBundle\Annotation as DI;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Translation\TranslatorInterface;

class CursusRegistrationController extends Controller
{
    private $authorization;
    private $cursusManager;
    private $platformConfigHandler;
    private $router;
    private $session;
    private $toolManager;
    private $translator;

    /**
     * @DI\InjectParams({
     *     "authorization"         = @DI\Inject("security.authorization_checker"),
     *     "cursusManager"         = @DI\Inject("claroline.manager.cursus_manager"),
     *     "platformConfigHandler" = @DI\Inject("claroline.config.platform_config_handler"),
     *     "router"                = @DI\Inject("router"),
     *     "session"               = @DI\Inject("session"),
     *     "toolManager"           = @DI\Inject("claroline.manager.tool_manager"),
     *     "translator"            = @DI\Inject("translator")
     * })
     */
    public function __construct(
        AuthorizationCheckerInterface $authorization,
        CursusManager $cursusManager,
        PlatformConfigurationHandler $platformConfigHandler,
        RouterInterface $router,
        SessionInterface $session,
        ToolManager $toolManager,
        TranslatorInterface $translator
    ) {
        $this->authorization = $authorization;
        $this->cursusManager = $cursusManager;
        $this->platformConfigHandler = $platformConfigHandler;
        $this->router = $router;
        $this->session = $session;
        $this->toolManager = $toolManager;
        $this->translator = $translator;
    }

    /**
     * @EXT\Route(
     *     "/tool/registration/index",
     *     name="claro_cursus_tool_registration_index",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     * @EXT\Template()
     *
     * @param User $user
     *
     * @return array
     */
    public function cursusToolRegistrationIndexAction(User $user)
    {
        $this->checkToolAccess();
        $isAdmin = $this->authorization->isGranted('ROLE_ADMIN');
        $organizations = $isAdmin ? [] : $user->getAdministratedOrganizations()->toArray();

        return ['isAuthorized' => $isAdmin || count($organizations) > 0];
    }

    /**
     * @EXT\Route(
     *     "/user/{user}/course/sessions/management",
     *     name="claro_cursus_user_sessions_management",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     * @EXT\Template()
     */
    public function userSessionsManagementAction(User $user)
    {
        $this->checkToolAccess();
        $sessionUsers = $this->cursusManager->getSessionUsersByUser($user);
        $tutorSessions = [];
        $learnerSessions = [];
        $sessionEvents = [];
        $eventsUsers = $this->cursusManager->getSessionEventUsersByUser($user);

        foreach ($sessionUsers as $sessionUser) {
            $type = $sessionUser->getUserType();
            $session = $sessionUser->getSession();
            $course = $session->getCourse();
            $courseCode = $course->getCode();

            if ($type === CourseSessionUser::LEARNER) {
                if (!isset($learnerSessions[$courseCode])) {
                    $learnerSessions[$courseCode] = [];
                    $learnerSessions[$courseCode]['course'] = $course;
                    $learnerSessions[$courseCode]['sessions'] = [];
                }
                $learnerSessions[$courseCode]['sessions'][] = $sessionUser;
            } elseif ($type === CourseSessionUser::TEACHER) {
                if (!isset($tutorSessions[$courseCode])) {
                    $tutorSessions[$courseCode] = [];
                    $tutorSessions[$courseCode]['course'] = $course;
                    $tutorSessions[$courseCode]['sessions'] = [];
                }
                $tutorSessions[$courseCode]['sessions'][] = $sessionUser;
            }
        }
        foreach ($eventsUsers as $eventUser) {
            $event = $eventUser->getSessionEvent();
            $session = $event->getSession();
            $sessionId = $session->getId();

            if (!isset($sessionEvents[$sessionId])) {
                $sessionEvents[$sessionId] = [];
            }
            $sessionEvents[$sessionId][] = ['event' => $event, 'eventUser' => $eventUser];
        }

        return [
            'user' => $user,
            'tutorSessions' => $tutorSessions,
            'learnerSessions' => $learnerSessions,
            'sessionEvents' => $sessionEvents,
        ];
    }

    /**
     * @EXT\Route(
     *     "/user/{user}/type/{type}/course/sessions/registration/management",
     *     name="claro_cursus_user_sessions_registration_management",
     *     defaults={"page"=1, "max"=50},
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     * @EXT\Template()
     */
    public function userSessionsRegistrationManagementAction(User $user, $type)
    {
        $this->checkToolAccess();
        $sessions = $this->cursusManager->getSessionsByUserAndType($user, intval($type));

        return [
            'user' => $user,
            'type' => $type,
            'sessions' => $sessions,
        ];
    }

    /**
     * @EXT\Route(
     *     "/group/{group}/course/sessions/management",
     *     name="claro_cursus_group_sessions_management",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     * @EXT\Template()
     */
    public function groupSessionsManagementAction(Group $group)
    {
        $this->checkToolAccess();
        $sessionGroups = $this->cursusManager->getSessionGroupsByGroup($group);
        $tutorSessions = [];
        $learnerSessions = [];

        foreach ($sessionGroups as $sessionGroup) {
            $type = $sessionGroup->getGroupType();
            $session = $sessionGroup->getSession();
            $course = $session->getCourse();
            $courseCode = $course->getCode();

            if ($type === 0) {
                if (!isset($learnerSessions[$courseCode])) {
                    $learnerSessions[$courseCode] = [];
                    $learnerSessions[$courseCode]['course'] = $course;
                    $learnerSessions[$courseCode]['sessions'] = [];
                }
                $learnerSessions[$courseCode]['sessions'][] = $sessionGroup;
            } elseif ($type === 1) {
                if (!isset($tutorSessions[$courseCode])) {
                    $tutorSessions[$courseCode] = [];
                    $tutorSessions[$courseCode]['course'] = $course;
                    $tutorSessions[$courseCode]['sessions'] = [];
                }
                $tutorSessions[$courseCode]['sessions'][] = $sessionGroup;
            }
        }

        return [
            'group' => $group,
            'tutorSessions' => $tutorSessions,
            'learnerSessions' => $learnerSessions,
        ];
    }

    /**
     * @EXT\Route(
     *     "/group/{group}/type/{type}/course/sessions/registration/management",
     *     name="claro_cursus_group_sessions_registration_management",
     *     defaults={"page"=1, "max"=50},
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     * @EXT\Template()
     */
    public function groupSessionsRegistrationManagementAction(Group $group, $type)
    {
        $this->checkToolAccess();
        $sessions = $this->cursusManager->getSessionsByGroupAndType($group, intval($type));

        return [
            'group' => $group,
            'type' => $type,
            'sessions' => $sessions,
        ];
    }

    /**
     * @EXT\Route(
     *     "course/sessions/datas/list/page/{page}/max/{max}/search/{search}",
     *     name="claro_cursus_sessions_datas_list",
     *     defaults={"search"="","page"=1, "max"=20},
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     * @EXT\Template()
     */
    public function sessionsDatasListAction($search = '', $page = 1, $max = 20)
    {
        $this->checkToolAccess();
        $sessionsDatas = $this->cursusManager->getSessionsDatas($search, true, $page, $max);

        return [
            'sessionsDatas' => $sessionsDatas,
            'search' => $search,
            'page' => $page,
            'max' => $max,
        ];
    }

    /**
     * @EXT\Route(
     *     "course/sessions/user/{user}/type/{type}/register",
     *     name="claro_cursus_sessions_register",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter(
     *     "sessions",
     *      class="ClarolineCursusBundle:CourseSession",
     *      options={"multipleIds" = true, "name" = "sessionsIds"}
     * )
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     */
    public function sessionsRegisterAction(User $user, $type, array $sessions)
    {
        $this->checkToolAccess();
        $results = $this->cursusManager->registerUsersToSessions($sessions, [$user], $type);

        if ($results['status'] === 'failed') {
            $datas = $results['datas'];
            $sessionFlashBag = $this->session->getFlashBag();

            foreach ($datas as $data) {
                $sessionFlashBag->add(
                    'error',
                    $this->translator->trans(
                        'session_not_enough_place_msg',
                        [
                            '%courseTitle%' => $data['courseTitle'],
                            '%courseCode%' => $data['courseCode'],
                            '%sessionName%' => $data['sessionName'],
                            '%remainingPlaces%' => $data['remainingPlaces'],
                        ],
                        'cursus'
                    )
                );
            }
        }

        return new RedirectResponse(
            $this->router->generate('claro_cursus_user_sessions_management', ['user' => $user->getId()])
        );
    }

    /**
     * @EXT\Route(
     *     "course/sessions/group/{group}/type/{type}/register",
     *     name="claro_cursus_sessions_register_group",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter(
     *     "sessions",
     *      class="ClarolineCursusBundle:CourseSession",
     *      options={"multipleIds" = true, "name" = "sessionsIds"}
     * )
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     */
    public function sessionsRegisterGroupAction(Group $group, $type, array $sessions)
    {
        $this->checkToolAccess();
        $results = $this->cursusManager->registerGroupToSessions($sessions, $group, $type);

        if ($results['status'] === 'failed') {
            $datas = $results['datas'];
            $sessionFlashBag = $this->session->getFlashBag();

            foreach ($datas as $data) {
                $sessionFlashBag->add(
                    'error',
                    $this->translator->trans(
                        'session_not_enough_place_msg',
                        [
                            '%courseTitle%' => $data['courseTitle'],
                            '%courseCode%' => $data['courseCode'],
                            '%sessionName%' => $data['sessionName'],
                            '%remainingPlaces%' => $data['remainingPlaces'],
                        ],
                        'cursus'
                    )
                );
            }
        }

        return new RedirectResponse(
            $this->router->generate('claro_cursus_group_sessions_management', ['group' => $group->getId()])
        );
    }

    /**
     * @EXT\Route(
     *     "course/registration/queue/{queue}/user/validate",
     *     name="claro_cursus_course_registration_queue_user_validate",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     */
    public function courseRegistrationQueueUserValidateAction(User $authenticatedUser, CourseRegistrationQueue $queue)
    {
        $user = $queue->getUser();

        if ($authenticatedUser->getId() !== $user->getId()) {
            throw new AccessDeniedException();
        }
        $this->cursusManager->validateUserCourseRegistrationQueue($queue);
        $course = $queue->getCourse();
        $sessionFlashBag = $this->session->getFlashBag();
        $sessionFlashBag->add(
            'success',
            $this->translator->trans(
                'course_request_confirmation_success',
                ['%courseTitle%' => $course->getTitle()],
                'cursus'
            )
        );

        return new RedirectResponse($this->router->generate('claro_desktop_open'));
    }

    /**
     * @EXT\Route(
     *     "session/registration/queue/{queue}/user/validate",
     *     name="claro_cursus_session_registration_queue_user_validate",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     */
    public function sessionRegistrationQueueUserValidateAction(User $authenticatedUser, CourseSessionRegistrationQueue $queue)
    {
        $user = $queue->getUser();

        if ($authenticatedUser->getId() !== $user->getId()) {
            throw new AccessDeniedException();
        }
        $this->cursusManager->validateUserSessionRegistrationQueue($queue);
        $session = $queue->getSession();
        $course = $session->getCourse();
        $sessionFlashBag = $this->session->getFlashBag();
        $sessionFlashBag->add(
            'success',
            $this->translator->trans(
                'session_request_confirmation_success',
                ['%courseTitle%' => $course->getTitle(), '%sessionName%' => $session->getName()],
                'cursus'
            )
        );

        return new RedirectResponse($this->router->generate('claro_desktop_open'));
    }

    /**
     * @EXT\Route(
     *     "session/{session}/events/registration/user/{user}/management",
     *     name="claro_cursus_session_events_registration_management",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     * @EXT\Template()
     */
    public function sessionEventsRegistrationManagementAction(User $user, CourseSession $session)
    {
        $status = [];
        $sessionEvents = $this->cursusManager->getEventsBySession($session);
        $sessionEventsUsers = $this->cursusManager->getSessionEventUsersByUserAndSession($user, $session);

        foreach ($sessionEventsUsers as $sessionEventUser) {
            $sessionEventId = $sessionEventUser->getSessionEvent()->getId();
            $status[$sessionEventId] = $sessionEventUser->getRegistrationStatus();
        }

        return ['sessionEvents' => $sessionEvents, 'status' => $status];
    }

    private function checkToolAccess()
    {
        $cursusTool = $this->toolManager->getAdminToolByName('claroline_cursus_tool_registration');

        if (is_null($cursusTool) || !$this->authorization->isGranted('OPEN', $cursusTool)) {
            throw new AccessDeniedException();
        }
    }
}
