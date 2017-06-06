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

use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Manager\ApiManager;
use Claroline\CursusBundle\Entity\CourseSession;
use Claroline\CursusBundle\Entity\SessionEvent;
use Claroline\CursusBundle\Manager\CursusManager;
use JMS\DiExtraBundle\Annotation as DI;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\Serializer;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class SessionEventsToolController extends Controller
{
    private $apiManager;
    private $authorization;
    private $cursusManager;
    private $request;
    private $serializer;

    /**
     * @DI\InjectParams({
     *     "apiManager"    = @DI\Inject("claroline.manager.api_manager"),
     *     "authorization" = @DI\Inject("security.authorization_checker"),
     *     "cursusManager" = @DI\Inject("claroline.manager.cursus_manager"),
     *     "request"       = @DI\Inject("request"),
     *     "serializer"    = @DI\Inject("jms_serializer")
     * })
     */
    public function __construct(
        ApiManager $apiManager,
        AuthorizationCheckerInterface $authorization,
        CursusManager $cursusManager,
        Request $request,
        Serializer $serializer
    ) {
        $this->apiManager = $apiManager;
        $this->authorization = $authorization;
        $this->cursusManager = $cursusManager;
        $this->request = $request;
        $this->serializer = $serializer;
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

        return [
            'workspace' => $workspace,
            'canEdit' => $canEdit ? 1 : 0,
            'sessions' => $sessions,
            'sessionEvents' => $sessionEventsData['sessionEvents'],
            'sessionEventsTotal' => $sessionEventsData['count'],
            'sessionEventUsers' => $eventsUsers,
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
        $sessionEvent = $this->cursusManager->createSessionEvent(
            $session,
            $name,
            $description,
            $startDate,
            $endDate,
            null,
            null,
            null,
            [],
            $registrationType,
            $maxUsers
        );
        $serializedSessionEvent = $this->serializer->serialize(
            $sessionEvent,
            'json',
            SerializationContext::create()->setGroups(['api_cursus_min'])
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
            SerializationContext::create()->setGroups(['api_cursus_min'])
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
        $sessionEvent->setName($name);
        $sessionEvent->setDescription($description);
        $sessionEvent->setStartDate($startDate);
        $sessionEvent->setEndDate($endDate);
        $sessionEvent->setRegistrationType($registrationType);
        $sessionEvent->setMaxUsers($maxUsers);
        $this->cursusManager->persistSessionEvent($sessionEvent);
        $serializedSessionEvent = $this->serializer->serialize(
            $sessionEvent,
            'json',
            SerializationContext::create()->setGroups(['api_cursus_min'])
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
                SerializationContext::create()->setGroups(['api_cursus_min'])
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
