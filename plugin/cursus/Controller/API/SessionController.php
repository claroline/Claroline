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

use Claroline\AppBundle\Controller\AbstractCrudController;
use Claroline\CoreBundle\Entity\Group;
use Claroline\CoreBundle\Entity\Organization\Organization;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Manager\Tool\ToolManager;
use Claroline\CursusBundle\Entity\CourseSession;
use Claroline\CursusBundle\Entity\CourseSessionGroup;
use Claroline\CursusBundle\Entity\CourseSessionRegistrationQueue;
use Claroline\CursusBundle\Entity\CourseSessionUser;
use Claroline\CursusBundle\Entity\SessionEvent;
use Claroline\CursusBundle\Manager\CursusManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * @EXT\Route("/cursus_session")
 */
class SessionController extends AbstractCrudController
{
    /** @var AuthorizationCheckerInterface */
    protected $authorization;

    /** @var CursusManager */
    private $cursusManager;

    /** @var ToolManager */
    private $toolManager;

    /** @var TranslatorInterface */
    private $translator;

    /**
     * SessionController constructor.
     *
     *
     * @param AuthorizationCheckerInterface $authorization
     * @param CursusManager                 $cursusManager
     * @param ToolManager                   $toolManager
     * @param TranslatorInterface           $translator
     */
    public function __construct(
        AuthorizationCheckerInterface $authorization,
        CursusManager $cursusManager,
        ToolManager $toolManager,
        TranslatorInterface $translator
    ) {
        $this->authorization = $authorization;
        $this->cursusManager = $cursusManager;
        $this->toolManager = $toolManager;
        $this->translator = $translator;
    }

    public function getName()
    {
        return 'session';
    }

    public function getClass()
    {
        return CourseSession::class;
    }

    public function getIgnore()
    {
        return ['exist', 'copyBulk', 'schema', 'find', 'list'];
    }

    /**
     * @EXT\Route(
     *     "/list",
     *     name="apiv2_cursus_session_list"
     * )
     * @EXT\ParamConverter("user", converter="current_user", options={"allowAnonymous"=false})
     *
     * @param User    $user
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function sessionsListAction(User $user, Request $request)
    {
        $this->checkToolAccess();
        $params = $request->query->all();

        if (!isset($params['hiddenFilters'])) {
            $params['hiddenFilters'] = [];
        }
        if (!$this->authorization->isGranted('ROLE_ADMIN')) {
            $params['hiddenFilters']['organizations'] = array_map(function (Organization $organization) {
                return $organization->getUuid();
            }, $user->getAdministratedOrganizations()->toArray());
        }
        if (!isset($params['sortBy'])) {
            $params['sortBy'] = '-id';
        }

        return new JsonResponse(
            $this->finder->search(CourseSession::class, $params)
        );
    }

    /**
     * @EXT\Route(
     *     "/{id}/events",
     *     name="apiv2_cursus_session_list_events"
     * )
     * @EXT\ParamConverter(
     *     "session",
     *     class="ClarolineCursusBundle:CourseSession",
     *     options={"mapping": {"id": "uuid"}}
     * )
     * @EXT\ParamConverter("user", converter="current_user", options={"allowAnonymous"=false})
     *
     * @param User          $user
     * @param CourseSession $session
     * @param Request       $request
     *
     * @return JsonResponse
     */
    public function sessionEventsListAction(User $user, CourseSession $session, Request $request)
    {
        $this->checkToolAccess();
        $params = $request->query->all();

        if (!isset($params['hiddenFilters'])) {
            $params['hiddenFilters'] = [];
        }
        $params['hiddenFilters']['session'] = $session->getUuid();

        if (!$this->authorization->isGranted('ROLE_ADMIN')) {
            $params['hiddenFilters']['organizations'] = array_map(function (Organization $organization) {
                return $organization->getUuid();
            }, $user->getAdministratedOrganizations()->toArray());
        }

        return new JsonResponse(
            $this->finder->search(SessionEvent::class, $params)
        );
    }

    /**
     * @EXT\Route(
     *     "/{id}/{type}/users",
     *     name="apiv2_cursus_session_list_users"
     * )
     * @EXT\ParamConverter(
     *     "session",
     *     class="ClarolineCursusBundle:CourseSession",
     *     options={"mapping": {"id": "uuid"}}
     * )
     * @EXT\Method("GET")
     *
     * @param CourseSession $session
     * @param int           $type
     * @param Request       $request
     *
     * @return JsonResponse
     */
    public function listSessionUsersAction(CourseSession $session, $type, Request $request)
    {
        $this->checkToolAccess();
        $params = $request->query->all();

        if (!isset($params['hiddenFilters'])) {
            $params['hiddenFilters'] = [];
        }
        $params['hiddenFilters']['session'] = $session->getUuid();
        $params['hiddenFilters']['type'] = intval($type);

        return new JsonResponse(
            $this->finder->search(CourseSessionUser::class, $params)
        );
    }

    /**
     * @EXT\Route(
     *     "/{id}/{type}/users",
     *     name="apiv2_cursus_session_add_users"
     * )
     * @EXT\ParamConverter(
     *     "session",
     *     class="ClarolineCursusBundle:CourseSession",
     *     options={"mapping": {"id": "uuid"}}
     * )
     * @EXT\Method("PATCH")
     *
     * @param CourseSession $session
     * @param int           $type
     * @param Request       $request
     *
     * @return JsonResponse
     */
    public function addUsersAction(CourseSession $session, $type, Request $request)
    {
        $this->checkToolAccess();
        $typeInt = intval($type);
        $users = $this->decodeIdsString($request, User::class);
        $nbUsers = count($users);

        if (CourseSessionUser::TYPE_LEARNER === $typeInt && !$this->cursusManager->checkSessionCapacity($session, $nbUsers)) {
            $errors = [$this->translator->trans('users_limit_reached', ['%count%' => $nbUsers], 'cursus')];

            return new JsonResponse(['errors' => $errors], 405);
        } else {
            $sessionUsers = $this->cursusManager->addUsersToSession($session, $users, $typeInt);

            return new JsonResponse(array_map(function (CourseSessionUser $sessionUser) {
                return $this->serializer->serialize($sessionUser);
            }, $sessionUsers));
        }
    }

    /**
     * @EXT\Route(
     *     "/remove/users",
     *     name="apiv2_cursus_session_remove_users"
     * )
     * @EXT\Method("DELETE")
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function removeUsersAction(Request $request)
    {
        $this->checkToolAccess();
        $sessionUsers = $this->decodeIdsString($request, CourseSessionUser::class);
        $this->cursusManager->deleteEntities($sessionUsers);

        return new JsonResponse();
    }

    /**
     * @EXT\Route(
     *     "/{id}/{type}/groups",
     *     name="apiv2_cursus_session_list_groups"
     * )
     * @EXT\ParamConverter(
     *     "session",
     *     class="ClarolineCursusBundle:CourseSession",
     *     options={"mapping": {"id": "uuid"}}
     * )
     * @EXT\Method("GET")
     *
     * @param CourseSession $session
     * @param int           $type
     * @param Request       $request
     *
     * @return JsonResponse
     */
    public function listSessionGroupsAction(CourseSession $session, $type, Request $request)
    {
        $this->checkToolAccess();
        $params = $request->query->all();

        if (!isset($params['hiddenFilters'])) {
            $params['hiddenFilters'] = [];
        }
        $params['hiddenFilters']['session'] = $session->getUuid();
        $params['hiddenFilters']['type'] = intval($type);

        return new JsonResponse(
            $this->finder->search(CourseSessionGroup::class, $params)
        );
    }

    /**
     * @EXT\Route(
     *     "/{id}/{type}/groups",
     *     name="apiv2_cursus_session_add_groups"
     * )
     * @EXT\ParamConverter(
     *     "session",
     *     class="ClarolineCursusBundle:CourseSession",
     *     options={"mapping": {"id": "uuid"}}
     * )
     * @EXT\Method("PATCH")
     *
     * @param CourseSession $session
     * @param int           $type
     * @param Request       $request
     *
     * @return JsonResponse
     */
    public function addGroupsAction(CourseSession $session, $type, Request $request)
    {
        $this->checkToolAccess();
        $typeInt = intval($type);
        $groups = $this->decodeIdsString($request, Group::class);
        $nbUsers = 0;

        foreach ($groups as $group) {
            $nbUsers += count($group->getUsers()->toArray());
        }

        if (CourseSessionGroup::TYPE_LEARNER === $typeInt && !$this->cursusManager->checkSessionCapacity($session, $nbUsers)) {
            $errors = [$this->translator->trans('users_limit_reached', ['%count%' => $nbUsers], 'cursus')];

            return new JsonResponse(['errors' => $errors], 405);
        } else {
            $sessionGroups = $this->cursusManager->addGroupsToSession($session, $groups, $typeInt);

            return new JsonResponse(array_map(function (CourseSessionGroup $sessionGroup) {
                return $this->serializer->serialize($sessionGroup);
            }, $sessionGroups));
        }
    }

    /**
     * @EXT\Route(
     *     "/remove/groups",
     *     name="apiv2_cursus_session_remove_groups"
     * )
     * @EXT\Method("DELETE")
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function removeGroupsAction(Request $request)
    {
        $this->checkToolAccess();
        $sessionGroups = $this->decodeIdsString($request, CourseSessionGroup::class);
        $this->cursusManager->deleteEntities($sessionGroups);

        return new JsonResponse();
    }

    /**
     * @EXT\Route(
     *     "/{id}/self/register",
     *     name="apiv2_cursus_session_self_register"
     * )
     * @EXT\ParamConverter(
     *     "session",
     *     class="ClarolineCursusBundle:CourseSession",
     *     options={"mapping": {"id": "uuid"}}
     * )
     * @EXT\ParamConverter("user", converter="current_user", options={"allowAnonymous"=false})
     * @EXT\Method("PUT")
     *
     * @param CourseSession $session
     * @param User          $user
     *
     * @return JsonResponse
     */
    public function selfRegisterAction(CourseSession $session, User $user)
    {
        if (!$session->getPublicRegistration()) {
            throw new AccessDeniedException();
        }
        $result = $this->cursusManager->registerUserToSession($session, $user);
        $data = null;

        if ($result instanceof CourseSessionRegistrationQueue) {
            $data = $this->serializer->serialize($result);
        } elseif (is_array($result) && 0 < count($result)) {
            $data = $this->serializer->serialize($result[0]);
        }

        return new JsonResponse($data);
    }

    /**
     * @EXT\Route(
     *     "/queues",
     *     name="apiv2_cursus_session_list_queues"
     * )
     * @EXT\ParamConverter("user", converter="current_user", options={"allowAnonymous"=false})
     * @EXT\Method("GET")
     *
     * @param User    $user
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function listSessionQueuesAction(User $user, Request $request)
    {
        $this->checkToolAccess();
        $params = $request->query->all();

        if (!isset($params['hiddenFilters'])) {
            $params['hiddenFilters'] = [];
        }
        if (!$this->authorization->isGranted('ROLE_ADMIN')) {
            $params['hiddenFilters']['organizations'] = array_map(function (Organization $organization) {
                return $organization->getUuid();
            }, $user->getAdministratedOrganizations()->toArray());
        }

        return new JsonResponse(
            $this->finder->search(CourseSessionRegistrationQueue::class, $params)
        );
    }

    /**
     * @EXT\Route(
     *     "/remove/queues",
     *     name="apiv2_cursus_session_remove_queues"
     * )
     * @EXT\Method("DELETE")
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function removeQueuesAction(Request $request)
    {
        $this->checkToolAccess();
        $sessionQueues = $this->decodeIdsString($request, CourseSessionRegistrationQueue::class);
        $this->cursusManager->deleteEntities($sessionQueues);

        return new JsonResponse();
    }

    /**
     * @EXT\Route(
     *     "/queue/{queue}/validate",
     *     name="apiv2_cursus_session_validate_queue"
     * )
     * @EXT\ParamConverter(
     *     "queue",
     *     class="ClarolineCursusBundle:CourseSessionRegistrationQueue",
     *     options={"mapping": {"queue": "uuid"}}
     * )
     * @EXT\Method("PUT")
     *
     * @param CourseSessionRegistrationQueue $queue
     *
     * @return JsonResponse
     */
    public function sessionQueueValidateAction(CourseSessionRegistrationQueue $queue)
    {
        $this->checkToolAccess();

        if ($this->cursusManager->checkSessionCapacity($queue->getSession())) {
            $this->cursusManager->validateSessionQueue($queue);

            return new JsonResponse();
        } else {
            $errors = [$this->translator->trans('users_limit_reached', ['%count%' => 1], 'cursus')];

            return new JsonResponse(['errors' => $errors], 405);
        }
    }

    /**
     * @EXT\Route(
     *     "/{id}/all/invite",
     *     name="apiv2_cursus_session_invite_all"
     * )
     * @EXT\ParamConverter(
     *     "session",
     *     class="ClarolineCursusBundle:CourseSession",
     *     options={"mapping": {"id": "uuid"}}
     * )
     * @EXT\Method("PUT")
     *
     * @param CourseSession $session
     *
     * @return JsonResponse
     */
    public function inviteAllAction(CourseSession $session)
    {
        $this->checkToolAccess();
        $this->cursusManager->inviteAllSessionLearners($session);

        return new JsonResponse();
    }

    /**
     * @EXT\Route(
     *     "/{id}/users/invite",
     *     name="apiv2_cursus_session_invite_users"
     * )
     * @EXT\ParamConverter(
     *     "session",
     *     class="ClarolineCursusBundle:CourseSession",
     *     options={"mapping": {"id": "uuid"}}
     * )
     * @EXT\Method("PUT")
     *
     * @param CourseSession $session
     * @param Request       $request
     *
     * @return JsonResponse
     */
    public function inviteUsersAction(CourseSession $session, Request $request)
    {
        $this->checkToolAccess();
        $users = $this->decodeIdsString($request, User::class);
        $this->cursusManager->sendSessionInvitation($session, $users);

        return new JsonResponse();
    }

    /**
     * @EXT\Route(
     *     "/{id}/groups/invite",
     *     name="apiv2_cursus_session_invite_groups"
     * )
     * @EXT\ParamConverter(
     *     "session",
     *     class="ClarolineCursusBundle:CourseSession",
     *     options={"mapping": {"id": "uuid"}}
     * )
     * @EXT\Method("PUT")
     *
     * @param CourseSession $session
     * @param Request       $request
     *
     * @return JsonResponse
     */
    public function inviteGroupsAction(CourseSession $session, Request $request)
    {
        $this->checkToolAccess();
        $groups = $this->decodeIdsString($request, Group::class);
        $users = [];

        foreach ($groups as $group) {
            $groupUsers = $group->getUsers();

            foreach ($groupUsers as $user) {
                $users[$user->getUuid()] = $user;
            }
        }
        $this->cursusManager->sendSessionInvitation($session, $users);

        return new JsonResponse();
    }

    /**
     * @EXT\Route(
     *     "/{id}/certificate/all/generate",
     *     name="apiv2_cursus_session_certificate_generate_all"
     * )
     * @EXT\ParamConverter(
     *     "session",
     *     class="ClarolineCursusBundle:CourseSession",
     *     options={"mapping": {"id": "uuid"}}
     * )
     * @EXT\Method("PUT")
     *
     * @param CourseSession $session
     *
     * @return JsonResponse
     */
    public function generateAllCertificatesAction(CourseSession $session)
    {
        $this->checkToolAccess();
        $this->cursusManager->generateAllSessionCertificates($session);

        return new JsonResponse();
    }

    /**
     * @EXT\Route(
     *     "/{id}/certificate/users/generate",
     *     name="apiv2_cursus_session_certificate_generate_users"
     * )
     * @EXT\ParamConverter(
     *     "session",
     *     class="ClarolineCursusBundle:CourseSession",
     *     options={"mapping": {"id": "uuid"}}
     * )
     * @EXT\Method("PUT")
     *
     * @param CourseSession $session
     * @param Request       $request
     *
     * @return JsonResponse
     */
    public function generateUsersCertificatesAction(CourseSession $session, Request $request)
    {
        $this->checkToolAccess();
        $users = $this->decodeIdsString($request, User::class);
        $this->cursusManager->generateSessionCertificates($session, $users);

        return new JsonResponse();
    }

    /**
     * @EXT\Route(
     *     "/{id}/certificate/groups/generate",
     *     name="apiv2_cursus_session_certificate_generate_groups"
     * )
     * @EXT\ParamConverter(
     *     "session",
     *     class="ClarolineCursusBundle:CourseSession",
     *     options={"mapping": {"id": "uuid"}}
     * )
     * @EXT\Method("PUT")
     *
     * @param CourseSession $session
     * @param Request       $request
     *
     * @return JsonResponse
     */
    public function generateGroupsCertificatesAction(CourseSession $session, Request $request)
    {
        $this->checkToolAccess();
        $groups = $this->decodeIdsString($request, Group::class);
        $users = [];

        foreach ($groups as $group) {
            $groupUsers = $group->getUsers();

            foreach ($groupUsers as $user) {
                $users[$user->getUuid()] = $user;
            }
        }
        $this->cursusManager->generateSessionCertificates($session, $users);

        return new JsonResponse();
    }

    /**
     * @EXT\Route(
     *     "/public/list",
     *     name="apiv2_cursus_session_public_list"
     * )
     * @EXT\ParamConverter("user", converter="current_user", options={"allowAnonymous"=false})
     *
     * @param User    $user
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function publicSessionsListAction(User $user, Request $request)
    {
        $params = $request->query->all();

        if (!isset($params['hiddenFilters'])) {
            $params['hiddenFilters'] = [];
        }
        $params['hiddenFilters']['publicRegistration'] = true;
        $params['hiddenFilters']['terminated'] = false;

        if (!isset($params['sortBy'])) {
            $params['sortBy'] = '-id';
        }

        return new JsonResponse(
            $this->finder->search(CourseSession::class, $params)
        );
    }

    /**
     * @param string $rights
     */
    private function checkToolAccess($rights = 'OPEN')
    {
        $cursusTool = $this->toolManager->getAdminToolByName('claroline_cursus_tool');

        if (is_null($cursusTool) || !$this->authorization->isGranted($rights, $cursusTool)) {
            throw new AccessDeniedException();
        }
    }
}
