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
use Claroline\CoreBundle\Entity\Organization\Organization;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Manager\Tool\ToolManager;
use Claroline\CursusBundle\Entity\CourseSession;
use Claroline\CursusBundle\Entity\SessionEvent;
use Claroline\CursusBundle\Entity\SessionEventUser;
use Claroline\CursusBundle\Manager\CursusManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * @EXT\Route("/cursus_session_event")
 */
class SessionEventController extends AbstractCrudController
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
     * SessionEventController constructor.
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
        return 'session_event';
    }

    public function getClass()
    {
        return SessionEvent::class;
    }

    public function getIgnore()
    {
        return ['exist', 'copyBulk', 'schema', 'find', 'list'];
    }

    /**
     * @EXT\Route(
     *     "/list",
     *     name="apiv2_cursus_session_event_list"
     * )
     * @EXT\ParamConverter("user", converter="current_user", options={"allowAnonymous"=false})
     *
     * @param User    $user
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function sessionEventsListAction(User $user, Request $request)
    {
        $this->checkToolAccess();
        $params = $request->query->all();

        if (!$this->authorization->isGranted('ROLE_ADMIN')) {
            if (!isset($params['hiddenFilters'])) {
                $params['hiddenFilters'] = [];
            }
            $params['hiddenFilters']['organizations'] = array_map(function (Organization $organization) {
                return $organization->getUuid();
            }, $user->getAdministratedOrganizations()->toArray());
        }
        if (!isset($params['sortBy'])) {
            $params['sortBy'] = '-id';
        }

        return new JsonResponse(
            $this->finder->search(SessionEvent::class, $params)
        );
    }

    /**
     * @EXT\Route(
     *     "/{id}/users",
     *     name="apiv2_cursus_session_event_list_users"
     * )
     * @EXT\ParamConverter(
     *     "sessionEvent",
     *     class="ClarolineCursusBundle:SessionEvent",
     *     options={"mapping": {"id": "uuid"}}
     * )
     * @EXT\Method("GET")
     *
     * @param SessionEvent $sessionEvent
     * @param Request      $request
     *
     * @return JsonResponse
     */
    public function listSessionEventUsersAction(SessionEvent $sessionEvent, Request $request)
    {
        $this->checkToolAccess();
        $params = $request->query->all();

        if (!isset($params['hiddenFilters'])) {
            $params['hiddenFilters'] = [];
        }
        $params['hiddenFilters']['sessionEvent'] = $sessionEvent->getUuid();

        return new JsonResponse(
            $this->finder->search(SessionEventUser::class, $params)
        );
    }

    /**
     * @EXT\Route(
     *     "/{id}/users",
     *     name="apiv2_cursus_session_event_add_users"
     * )
     * @EXT\ParamConverter(
     *     "sessionEvent",
     *     class="ClarolineCursusBundle:SessionEvent",
     *     options={"mapping": {"id": "uuid"}}
     * )
     * @EXT\Method("PATCH")
     *
     * @param SessionEvent $sessionEvent
     * @param Request      $request
     *
     * @return JsonResponse
     */
    public function addUsersAction(SessionEvent $sessionEvent, Request $request)
    {
        $this->checkToolAccess();
        $users = $this->decodeIdsString($request, User::class);
        $nbUsers = count($users);

        if (!$this->cursusManager->checkSessionEventCapacity($sessionEvent, $nbUsers)) {
            $errors = [$this->translator->trans('users_limit_reached', ['%count%' => $nbUsers], 'cursus')];

            return new JsonResponse(['errors' => $errors], 405);
        } else {
            $sessionEventUsers = $this->cursusManager->addUsersToSessionEvent($sessionEvent, $users);

            return new JsonResponse(array_map(function (SessionEventUser $sessionEventUser) {
                return $this->serializer->serialize($sessionEventUser);
            }, $sessionEventUsers));
        }
    }

    /**
     * @EXT\Route(
     *     "/remove/users",
     *     name="apiv2_cursus_session_event_remove_users"
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
        $sessionEventUsers = $this->decodeIdsString($request, SessionEventUser::class);
        $this->cursusManager->deleteEntities($sessionEventUsers);

        return new JsonResponse();
    }

    /**
     * @EXT\Route(
     *     "/{id}/self/register",
     *     name="apiv2_cursus_session_event_self_register"
     * )
     * @EXT\ParamConverter(
     *     "sessionEvent",
     *     class="ClarolineCursusBundle:SessionEvent",
     *     options={"mapping": {"id": "uuid"}}
     * )
     * @EXT\ParamConverter("user", converter="current_user", options={"allowAnonymous"=false})
     * @EXT\Method("PUT")
     *
     * @param SessionEvent $sessionEvent
     * @param User         $user
     *
     * @return JsonResponse
     */
    public function selfRegisterAction(SessionEvent $sessionEvent, User $user)
    {
        if (CourseSession::REGISTRATION_PUBLIC !== $sessionEvent->getRegistrationType()) {
            throw new AccessDeniedException();
        }
        $this->cursusManager->registerUserToSessionEvent($sessionEvent, $user);

        $eventsRegistration = [];
        $eventUsers = !is_null($user) ?
            $this->finder->fetch(
                SessionEventUser::class,
                ['session' => $sessionEvent->getSession()->getUuid(), 'user' => $user->getUuid()]
            ) :
            [];

        foreach ($eventUsers as $eventUser) {
            $event = $eventUser->getSessionEvent();
            $set = $event->getEventSet();
            $eventsRegistration[$event->getUuid()] = true;

            if ($set) {
                $setName = $set->getName();

                if (!isset($eventsRegistration[$setName])) {
                    $eventsRegistration[$setName] = $set->getLimit();
                }
                --$eventsRegistration[$setName];
            }
        }

        return new JsonResponse($eventsRegistration);
    }

    /**
     * @EXT\Route(
     *     "/{id}/all/invite",
     *     name="apiv2_cursus_session_event_invite_all"
     * )
     * @EXT\ParamConverter(
     *     "sessionEvent",
     *     class="ClarolineCursusBundle:SessionEvent",
     *     options={"mapping": {"id": "uuid"}}
     * )
     * @EXT\Method("PUT")
     *
     * @param SessionEvent $sessionEvent
     *
     * @return JsonResponse
     */
    public function inviteAllUsersAction(SessionEvent $sessionEvent)
    {
        $this->checkToolAccess();
        $this->cursusManager->inviteAllSessionEventUsers($sessionEvent);

        return new JsonResponse();
    }

    /**
     * @EXT\Route(
     *     "/{id}/users/invite",
     *     name="apiv2_cursus_session_event_invite_users"
     * )
     * @EXT\ParamConverter(
     *     "sessionEvent",
     *     class="ClarolineCursusBundle:SessionEvent",
     *     options={"mapping": {"id": "uuid"}}
     * )
     * @EXT\Method("PUT")
     *
     * @param SessionEvent $sessionEvent
     * @param Request      $request
     *
     * @return JsonResponse
     */
    public function inviteUsersAction(SessionEvent $sessionEvent, Request $request)
    {
        $this->checkToolAccess();
        $users = $this->decodeIdsString($request, User::class);
        $this->cursusManager->sendEventInvitation($sessionEvent, $users);

        return new JsonResponse();
    }

    /**
     * @EXT\Route(
     *     "/{id}/certificate/all/generate",
     *     name="apiv2_cursus_session_event_certificate_generate_all"
     * )
     * @EXT\ParamConverter(
     *     "sessionEvent",
     *     class="ClarolineCursusBundle:SessionEvent",
     *     options={"mapping": {"id": "uuid"}}
     * )
     * @EXT\Method("PUT")
     *
     * @param SessionEvent $sessionEvent
     *
     * @return JsonResponse
     */
    public function generateAllCertificatesAction(SessionEvent $sessionEvent)
    {
        $this->checkToolAccess();
        $this->cursusManager->generateAllEventCertificates($sessionEvent);

        return new JsonResponse();
    }

    /**
     * @EXT\Route(
     *     "/{id}/certificate/users/generate",
     *     name="apiv2_cursus_session_event_certificate_generate_users"
     * )
     * @EXT\ParamConverter(
     *     "sessionEvent",
     *     class="ClarolineCursusBundle:SessionEvent",
     *     options={"mapping": {"id": "uuid"}}
     * )
     * @EXT\Method("PUT")
     *
     * @param SessionEvent $sessionEvent
     * @param Request      $request
     *
     * @return JsonResponse
     */
    public function generateUsersCertificatesAction(SessionEvent $sessionEvent, Request $request)
    {
        $this->checkToolAccess();
        $users = $this->decodeIdsString($request, User::class);
        $this->cursusManager->generateEventCertificates($sessionEvent, $users);

        return new JsonResponse();
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
