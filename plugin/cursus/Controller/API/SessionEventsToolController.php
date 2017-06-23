<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CursusBundle\Controller\API;

use Claroline\CoreBundle\Entity\Organization\Location;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Claroline\CoreBundle\Manager\ApiManager;
use Claroline\CoreBundle\Manager\Organization\LocationManager;
use Claroline\CoreBundle\Manager\UserManager;
use Claroline\CursusBundle\Entity\CourseSession;
use Claroline\CursusBundle\Entity\CourseSessionUser;
use Claroline\CursusBundle\Entity\SessionEvent;
use Claroline\CursusBundle\Entity\SessionEventComment;
use Claroline\CursusBundle\Entity\SessionEventSet;
use Claroline\CursusBundle\Entity\SessionEventUser;
use Claroline\CursusBundle\Manager\CursusManager;
use JMS\DiExtraBundle\Annotation as DI;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\Serializer;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class SessionEventsToolController extends Controller
{
    private $apiManager;
    private $authorization;
    private $configHandler;
    private $cursusManager;
    private $locationManager;
    private $request;
    private $serializer;
    private $tokenStorage;
    private $userManager;

    /**
     * @DI\InjectParams({
     *     "apiManager"      = @DI\Inject("claroline.manager.api_manager"),
     *     "authorization"   = @DI\Inject("security.authorization_checker"),
     *     "configHandler"   = @DI\Inject("claroline.config.platform_config_handler"),
     *     "cursusManager"   = @DI\Inject("claroline.manager.cursus_manager"),
     *     "locationManager" = @DI\Inject("claroline.manager.organization.location_manager"),
     *     "request"         = @DI\Inject("request"),
     *     "serializer"      = @DI\Inject("jms_serializer"),
     *     "tokenStorage"    = @DI\Inject("security.token_storage"),
     *     "userManager"     = @DI\Inject("claroline.manager.user_manager")
     * })
     */
    public function __construct(
        ApiManager $apiManager,
        AuthorizationCheckerInterface $authorization,
        PlatformConfigurationHandler $configHandler,
        CursusManager $cursusManager,
        LocationManager $locationManager,
        Request $request,
        Serializer $serializer,
        TokenStorageInterface $tokenStorage,
        UserManager $userManager
    ) {
        $this->apiManager = $apiManager;
        $this->authorization = $authorization;
        $this->configHandler = $configHandler;
        $this->cursusManager = $cursusManager;
        $this->locationManager = $locationManager;
        $this->request = $request;
        $this->serializer = $serializer;
        $this->tokenStorage = $tokenStorage;
        $this->userManager = $userManager;
    }

    /**
     * @EXT\Route(
     *     "/workspace/{workspace}/tool/session/events/index",
     *     name="claro_cursus_session_events_tool_index"
     * )
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     * @EXT\Template()
     *
     * @return array
     */
    public function indexAction(User $user, Workspace $workspace)
    {
        $this->checkToolAccess($workspace);
        $canEdit = $this->authorization->isGranted(['claroline_session_events_tool', 'edit'], $workspace);
        $sessions = $this->cursusManager->getSessionsByWorkspace($workspace);
        $sessionEventsData = count($sessions) > 0 ?
            $this->cursusManager->searchSessionEventsPartialList($sessions[0], [], 0, 20) :
            ['sessionEvents' => [], 'count' => 0];
        $sessionEventUsers = $this->cursusManager->getSessionEventUsersByUser($user);
        $eventsUsers = [];

        foreach ($sessionEventUsers as $sessionEventUser) {
            $sessionEvent = $sessionEventUser->getSessionEvent();
            $eventsUsers[$sessionEvent->getId()] = $sessionEventUser;
        }
        $disableRegistration = $this->configHandler->hasParameter('cursus_disable_session_event_registration') ?
            $this->configHandler->getParameter('cursus_disable_session_event_registration') :
            true;

        return [
            'workspace' => $workspace,
            'canEdit' => $canEdit ? 1 : 0,
            'sessions' => $sessions,
            'sessionEvents' => $sessionEventsData['sessionEvents'],
            'sessionEventsTotal' => $sessionEventsData['count'],
            'sessionEventUsers' => $eventsUsers,
            'disableRegistration' => $disableRegistration ? 1 : 0,
        ];
    }

    /**
     * @EXT\Route(
     *     "/workspace/session/{session}/event/create",
     *     name="claro_cursus_session_event_create",
     *     options = {"expose"=true}
     * )
     */
    public function sessionEventCreateAction(CourseSession $session)
    {
        $this->checkToolAccess($session->getWorkspace(), 'edit');
        $name = $this->request->get('name', false) ? $this->request->get('name') : null;
        $description = $this->request->get('description', false) ? $this->request->get('description') : null;
        $startDate = $this->request->get('startDate', false) ? new \DateTime($this->request->get('startDate')) : null;
        $endDate = $this->request->get('endDate', false) ? new \DateTime($this->request->get('endDate')) : null;
        $registrationType = $this->request->get('registrationType', false) ?
            intval($this->request->get('registrationType')) :
            CourseSession::REGISTRATION_AUTO;
        $maxUsers = $this->request->get('maxUsers', false);
        $maxUsers = $maxUsers !== false && $maxUsers !== '' ? intval($maxUsers) : null;
        $type = $this->request->get('type', false) !== false ? $this->request->get('type') : SessionEvent::TYPE_NONE;
        $locationExtra = $this->request->get('locationExtra', false) ? $this->request->get('locationExtra') : null;
        $locationId = intval($this->request->get('location', false));
        $location = $locationId ? $this->locationManager->getLocationById($locationId) : null;
        $teachers = [];
        $teachersParams = $this->request->get('teachers', false);

        if ($teachersParams) {
            $teachersIds = explode(',', $teachersParams);

            if ($teachersIds) {
                if (intval($teachersIds[0]) === 0) {
                    array_splice($teachersIds, 0, 1);
                }
                $teachers = $this->userManager->getUsersByIds($teachersIds);
            }
        }
        $eventSetName = $this->request->get('eventSet', false);
        $eventSet = $eventSetName && $registrationType === CourseSession::REGISTRATION_PUBLIC ?
            $this->cursusManager->getSessionEventSet($session, $eventSetName) :
             null;
        $sessionEvent = $this->cursusManager->createSessionEvent(
            $session,
            $name,
            $description,
            $startDate,
            $endDate,
            $location,
            $locationExtra,
            null,
            $teachers,
            $registrationType,
            $maxUsers,
            $type,
            $eventSet
        );
        $serializedSessionEvent = $this->serializer->serialize(
            $sessionEvent,
            'json',
            SerializationContext::create()->setGroups(['api_user_min'])
        );

        return new JsonResponse($serializedSessionEvent, 200);
    }

    /**
     * @EXT\Route(
     *     "/workspace/session/event/{sessionEvent}/fetch",
     *     name="claro_cursus_session_event_fetch",
     *     options = {"expose"=true}
     * )
     */
    public function sessionEventFetchAction(SessionEvent $sessionEvent)
    {
        $this->checkToolAccess($sessionEvent->getSession()->getWorkspace(), 'open');
        $serializedSessionEvent = $this->serializer->serialize(
            $sessionEvent,
            'json',
            SerializationContext::create()->setGroups(['api_user_min'])
        );
        $sessionEventUsers = $this->cursusManager->getSessionEventUsersBySessionEvent($sessionEvent);
        $serializedParticipants = $this->serializer->serialize(
            $sessionEventUsers,
            'json',
            SerializationContext::create()->setGroups(['api_user_min'])
        );

        return new JsonResponse(['data' => $serializedSessionEvent, 'participants' => $serializedParticipants], 200);
    }

    /**
     * @EXT\Route(
     *     "/workspace/session/event/{sessionEvent}/edit",
     *     name="claro_cursus_session_event_edit",
     *     options = {"expose"=true}
     * )
     */
    public function sessionEventEditAction(SessionEvent $sessionEvent)
    {
        $this->checkToolAccess($sessionEvent->getSession()->getWorkspace(), 'edit');
        $name = $this->request->get('name', false) ? $this->request->get('name') : null;
        $description = $this->request->get('description', false) ? $this->request->get('description') : null;
        $startDate = $this->request->get('startDate', false) ? new \DateTime($this->request->get('startDate')) : null;
        $endDate = $this->request->get('endDate', false) ? new \DateTime($this->request->get('endDate')) : null;
        $registrationType = $this->request->get('registrationType', false) ?
            intval($this->request->get('registrationType')) :
            CourseSession::REGISTRATION_AUTO;
        $maxUsers = $this->request->get('maxUsers', false);
        $maxUsers = $maxUsers !== false && $maxUsers !== '' ? intval($maxUsers) : null;
        $type = $this->request->get('type', false) !== false ? $this->request->get('type') : SessionEvent::TYPE_NONE;
        $locationExtra = $this->request->get('locationExtra', false) ? $this->request->get('locationExtra') : null;
        $eventSetName = $this->request->get('eventSet', false);
        $sessionEvent->emptyTutors();
        $teachersParams = $this->request->get('teachers', false);

        $sessionEvent->setName($name);
        $sessionEvent->setDescription($description);
        $sessionEvent->setStartDate($startDate);
        $sessionEvent->setEndDate($endDate);
        $sessionEvent->setRegistrationType($registrationType);
        $sessionEvent->setMaxUsers($maxUsers);
        $sessionEvent->setType($type);
        $sessionEvent->setLocationExtra($locationExtra);
        $locationId = intval($this->request->get('location', false));
        $location = $locationId ? $this->locationManager->getLocationById($locationId) : null;
        $sessionEvent->setLocation($location);

        if ($teachersParams) {
            $teachersIds = explode(',', $teachersParams);

            if ($teachersIds) {
                if (intval($teachersIds[0]) === 0) {
                    array_splice($teachersIds, 0, 1);
                }
                $teachers = $this->userManager->getUsersByIds($teachersIds);

                foreach ($teachers as $teacher) {
                    $sessionEvent->addTutor($teacher);
                }
            }
        }
        $eventSet = $eventSetName && $registrationType === CourseSession::REGISTRATION_PUBLIC ?
            $this->cursusManager->getSessionEventSet($sessionEvent->getSession(), $eventSetName) :
             null;
        $sessionEvent->setEventSet($eventSet);
        $this->cursusManager->persistSessionEvent($sessionEvent);
        $serializedSessionEvent = $this->serializer->serialize(
            $sessionEvent,
            'json',
            SerializationContext::create()->setGroups(['api_user_min'])
        );

        return new JsonResponse($serializedSessionEvent, 200);
    }

    /**
     * @EXT\Route(
     *     "/workspace/{workspace}/session/event/{sessionEvent}/delete",
     *     name="claro_cursus_session_event_delete",
     *     options = {"expose"=true}
     * )
     */
    public function sessionEventDeleteAction(Workspace $workspace, SessionEvent $sessionEvent)
    {
        $this->checkSessionEventEditionAccess($workspace, $sessionEvent);
        $this->cursusManager->deleteSessionEvent($sessionEvent);

        return new JsonResponse('success', 200);
    }

    /**
     * @EXT\Route(
     *     "/workspace/{workspace}/session/events/delete",
     *     name="claro_cursus_session_events_delete",
     *     options = {"expose"=true}
     * )
     */
    public function sessionEventsDeleteAction(Workspace $workspace)
    {
        $sessionEvents = $this->apiManager->getParameters('ids', 'Claroline\CursusBundle\Entity\SessionEvent');
        $this->checkSessionEventsEditionAccess($workspace, $sessionEvents);
        $this->cursusManager->deleteSessionEvents($sessionEvents);

        return new JsonResponse('success', 200);
    }

    /**
     * @EXT\Route(
     *     "/workspace/session/{session}/events/page/{page}/limit/{limit}/search",
     *     name="claro_cursus_session_events_search",
     *     options = {"expose"=true}
     * )
     */
    public function sessionEventsSearchAction(CourseSession $session, $page, $limit)
    {
        $workspace = $session->getWorkspace();
        $this->checkToolAccess($workspace);
        $searches = $this->request->query->all();
        $data = $this->cursusManager->searchSessionEventsPartialList($session, $searches, $page, $limit);
        $content = [
            'sessionEvents' => $this->serializer->serialize(
                $data['sessionEvents'],
                'json',
                SerializationContext::create()->setGroups(['api_user_min'])
            ),
            'total' => $data['count'],
        ];

        return new JsonResponse($content, 200);
    }

    /**
     * @EXT\Route(
     *     "/workspace/session/event/{sessionEvent}/users/register",
     *     name="claro_cursus_session_event_users_register",
     *     options = {"expose"=true}
     * )
     */
    public function sessionEventUsersRegisterAction(SessionEvent $sessionEvent)
    {
        $this->checkToolAccess($sessionEvent->getSession()->getWorkspace(), 'edit');
        $users = $this->apiManager->getParameters('ids', 'Claroline\CoreBundle\Entity\User');
        $results = $this->cursusManager->registerUsersToSessionEvent($sessionEvent, $users);

        return new JsonResponse($results, 200);
    }

    /**
     * @EXT\Route(
     *     "/workspace/session/event/users/delete",
     *     name="claro_cursus_session_event_users_delete",
     *     options = {"expose"=true}
     * )
     */
    public function sessionEventUsersDeleteAction()
    {
        $sessionEventUsers = $this->apiManager->getParameters('ids', 'Claroline\CursusBundle\Entity\SessionEventUser');
        $workspaces = [];

        foreach ($sessionEventUsers as $sessionEventUser) {
            $workspace = $sessionEventUser->getSessionEvent()->getSession()->getWorkspace();
            $workspaces[$workspace->getId()] = $workspace;
        }
        foreach ($workspaces as $workspace) {
            $this->checkToolAccess($workspace, 'edit');
        }
        $serializedSessionEventUsers = $this->serializer->serialize(
            $sessionEventUsers,
            'json',
            SerializationContext::create()->setGroups(['api_cursus_min'])
        );
        $this->cursusManager->unregisterUsersFromSessionEvent($sessionEventUsers);

        return new JsonResponse($serializedSessionEventUsers, 200);
    }

    /**
     * @EXT\Route(
     *     "/workspace/session/event/user/{sessionEventUser}/delete",
     *     name="claro_cursus_session_event_user_accept",
     *     options = {"expose"=true}
     * )
     */
    public function sessionEventUserAcceptAction(SessionEventUser $sessionEventUser)
    {
        $this->checkToolAccess($sessionEventUser->getSessionEvent()->getSession()->getWorkspace(), 'edit');
        $results = $this->cursusManager->acceptSessionEventUser($sessionEventUser);

        if ($results['status'] === 'success') {
            $results['data'] = $this->serializer->serialize(
                $results['data'],
                'json',
                SerializationContext::create()->setGroups(['api_user_min'])
            );
        }

        return new JsonResponse($results, 200);
    }

    /**
     * @EXT\Route(
     *     "/workspace/session/event/{sessionEvent}/repeat",
     *     name="claro_cursus_session_event_repeat",
     *     options = {"expose"=true}
     * )
     */
    public function postSessionEventRepeatAction(SessionEvent $sessionEvent)
    {
        $this->checkToolAccess($sessionEvent->getSession()->getWorkspace(), 'edit');
        $monday = boolval($this->request->get('monday', false));
        $tuesday = boolval($this->request->get('tuesday', false));
        $wednesday = boolval($this->request->get('wednesday', false));
        $thursday = boolval($this->request->get('thursday', false));
        $friday = boolval($this->request->get('friday', false));
        $saturday = boolval($this->request->get('saturday', false));
        $sunday = boolval($this->request->get('sunday', false));
        $iteration = [
            'Monday' => $monday,
            'Tuesday' => $tuesday,
            'Wednesday' => $wednesday,
            'Thursday' => $thursday,
            'Friday' => $friday,
            'Saturday' => $saturday,
            'Sunday' => $sunday,
        ];
        $endDate = $this->request->get('until', false) ? new \DateTime($this->request->get('until')) : null;
        $duration = $this->request->get('duration', false);
        $duration = $duration !== false && $duration !== '' ? intval($duration) : null;

        $createdSessionEvents = $this->cursusManager->repeatSessionEvent($sessionEvent, $iteration, $endDate, $duration);
        $serializedSessionEvents = $this->serializer->serialize(
            $createdSessionEvents,
            'json',
            SerializationContext::create()->setGroups(['api_cursus_min'])
        );

        return new JsonResponse($serializedSessionEvents, 200);
    }

    /**
     * @EXT\Route(
     *     "/workspace/{workspace}/cursus/locations/retrieve",
     *     name="claro_cursus_locations_retrieve",
     *     options = {"expose"=true}
     * )
     */
    public function locationsRetrieveAction(Workspace $workspace)
    {
        $this->checkToolAccess($workspace, 'edit');
        $locations = $this->locationManager->getByTypes([Location::TYPE_DEPARTMENT, Location::TYPE_TRAINING]);
        $serializedLocations = $this->serializer->serialize(
            $locations,
            'json',
            SerializationContext::create()->setGroups(['api_user_min'])
        );

        return new JsonResponse($serializedLocations, 200);
    }

    /**
     * @EXT\Route(
     *     "/workspace/cursus/session/{session}/teachers/retrieve",
     *     name="claro_cursus_session_teachers_retrieve",
     *     options = {"expose"=true}
     * )
     */
    public function sessionTeachersRetrieveAction(CourseSession $session)
    {
        $this->checkToolAccess($session->getWorkspace(), 'edit');
        $users = $this->cursusManager->getUsersBySessionAndType($session, CourseSessionUser::TEACHER);
        $serializedUsers = $this->serializer->serialize(
            $users,
            'json',
            SerializationContext::create()->setGroups(['api_user_min'])
        );

        return new JsonResponse($serializedUsers, 200);
    }

    /**
     * @EXT\Route(
     *     "/workspace/cursus/session/event/{sessionEvent}/comments/retrieve",
     *     name="claro_cursus_session_event_comments_retrieve",
     *     options = {"expose"=true}
     * )
     */
    public function sessionEventCommentsRetrieveAction(SessionEvent $sessionEvent)
    {
        $this->checkToolAccess($sessionEvent->getSession()->getWorkspace());
        $comments = $this->cursusManager->getSessionEventCommentsBySessionEvent($sessionEvent);
        $serializedComments = $this->serializer->serialize(
            $comments,
            'json',
            SerializationContext::create()->setGroups(['api_cursus'])
        );

        return new JsonResponse($serializedComments, 200);
    }

    /**
     * @EXT\Route(
     *     "/workspace/cursus/session/event/{sessionEvent}/comment/create",
     *     name="claro_cursus_session_event_comment_create",
     *     options = {"expose"=true}
     * )
     * @EXT\ParamConverter("user", converter="current_user")
     */
    public function sessionEventCommentCreateAction(User $user, SessionEvent $sessionEvent)
    {
        $this->checkToolAccess($sessionEvent->getSession()->getWorkspace(), 'edit');
        $content = $this->request->get('content', false);
        $comment = $this->cursusManager->createSessionEventComment($user, $sessionEvent, $content);
        $serializedComment = $this->serializer->serialize(
            $comment,
            'json',
            SerializationContext::create()->setGroups(['api_cursus'])
        );

        return new JsonResponse($serializedComment, 200);
    }

    /**
     * @EXT\Route(
     *     "/workspace/cursus/session/event/comment/{sessionEventComment}/edit",
     *     name="claro_cursus_session_event_comment_edit",
     *     options = {"expose"=true}
     * )
     * @EXT\ParamConverter("user", converter="current_user")
     */
    public function sessionEventCommentEditAction(SessionEventComment $sessionEventComment)
    {
        $this->checkToolAccess($sessionEventComment->getSessionEvent()->getSession()->getWorkspace(), 'edit');
        $content = $this->request->get('content', false);

        if ($content) {
            $sessionEventComment->setContent($content);
            $sessionEventComment->setEditionDate(new \DateTime());
            $this->cursusManager->persistSessionEventComment($sessionEventComment);
        }
        $serializedComment = $this->serializer->serialize(
            $sessionEventComment,
            'json',
            SerializationContext::create()->setGroups(['api_cursus'])
        );

        return new JsonResponse($serializedComment, 200);
    }

    /**
     * @EXT\Route(
     *     "/workspace/cursus/session/event/comment/{sessionEventComment}/delete",
     *     name="claro_cursus_session_event_comment_delete",
     *     options = {"expose"=true}
     * )
     */
    public function sessionEventCommentDeleteAction(SessionEventComment $sessionEventComment)
    {
        $this->checkToolAccess($sessionEventComment->getSessionEvent()->getSession()->getWorkspace(), 'edit');
        $this->cursusManager->deleteSessionEventComment($sessionEventComment);

        return new JsonResponse('success', 200);
    }

    /**
     * @EXT\Route(
     *     "/workspace/cursus/session/event/set/{sessionEventSet}/edit",
     *     name="claro_cursus_session_event_set_edit",
     *     options = {"expose"=true}
     * )
     */
    public function sessionEventSetEditAction(SessionEventSet $sessionEventSet)
    {
        $this->checkToolAccess($sessionEventSet->getSession()->getWorkspace(), 'edit');
        $name = $this->request->get('name', false);
        $limit = $this->request->get('limit', false);

        if ($name) {
            $sessionEventSet->setName($name);
        }
        if ($limit) {
            $sessionEventSet->setLimit($limit);
        }
        $this->cursusManager->persistSessionEventSet($sessionEventSet);
        $serializedSet = $this->serializer->serialize(
            $sessionEventSet,
            'json',
            SerializationContext::create()->setGroups(['api_user_min'])
        );

        return new JsonResponse($serializedSet, 200);
    }

    /**
     * @EXT\Route(
     *     "/workspace/cursus/session/event/set/{sessionEventSet}/events/retrieve",
     *     name="claro_cursus_session_event_set_events_retrieve",
     *     options = {"expose"=true}
     * )
     */
    public function sessionEventSetEventRetrieveAction(SessionEventSet $sessionEventSet)
    {
        $this->checkToolAccess($sessionEventSet->getSession()->getWorkspace(), 'open');
        $events = $sessionEventSet->getEvents();
        $user = $this->tokenStorage->getToken()->getUser();
        $eventUsers = $user !== 'anon.' ?
            $this->cursusManager->getSessionEventUsersByUserAndEventSet($user, $sessionEventSet) :
            [];
        $registrations = [];

        foreach ($eventUsers as $eventUser) {
            $sessionEventId = $eventUser->getSessionEvent()->getId();
            $registrations[$sessionEventId] = $eventUser;
        }
        $serializedEvents = $this->serializer->serialize(
            $events,
            'json',
            SerializationContext::create()->setGroups(['api_user_min'])
        );
        $serializedRegs = $this->serializer->serialize(
            $registrations,
            'json',
            SerializationContext::create()->setGroups(['api_cursus_min'])
        );

        return new JsonResponse(['events' => $serializedEvents, 'registrations' => $serializedRegs], 200);
    }

    /**
     * @EXT\Route(
     *     "/workspace/cursus/session/event/set/{sessionEventSet}/delete",
     *     name="claro_cursus_session_event_set_delete",
     *     options = {"expose"=true}
     * )
     */
    public function sessionEventSetDeleteAction(SessionEventSet $sessionEventSet)
    {
        $this->checkToolAccess($sessionEventSet->getSession()->getWorkspace(), 'edit');
        $this->cursusManager->deleteSessionEventSet($sessionEventSet);

        return new JsonResponse('success', 200);
    }

    private function checkToolAccess(Workspace $workspace = null, $right = 'open')
    {
        if (is_null($workspace) || !$this->authorization->isGranted(['claroline_session_events_tool', $right], $workspace)) {
            throw new AccessDeniedException();
        }
    }

    private function checkSessionEventEditionAccess(Workspace $workspace, SessionEvent $sessionEvent)
    {
        if (!$this->authorization->isGranted(['claroline_session_events_tool', 'edit'], $workspace) ||
            $workspace->getId() !== $sessionEvent->getSession()->getWorkspace()->getId()
        ) {
            throw new AccessDeniedException();
        }
    }

    private function checkSessionEventsEditionAccess(Workspace $workspace, array $sessionEvents)
    {
        if (!$this->authorization->isGranted(['claroline_session_events_tool', 'edit'], $workspace)) {
            throw new AccessDeniedException();
        }
        foreach ($sessionEvents as $sessionEvent) {
            if ($workspace->getId() !== $sessionEvent->getSession()->getWorkspace()->getId()) {
                throw new AccessDeniedException();
            }
        }
    }
}
