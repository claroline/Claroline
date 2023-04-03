<?php

namespace Claroline\CursusBundle\Controller\Registration;

use Claroline\AppBundle\Controller\AbstractCrudController;
use Claroline\CoreBundle\Entity\Organization\Organization;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Security\PermissionCheckerTrait;
use Claroline\CoreBundle\Validator\Exception\InvalidDataException;
use Claroline\CursusBundle\Entity\Course;
use Claroline\CursusBundle\Entity\Registration\SessionUser;
use Claroline\CursusBundle\Entity\Session;
use Claroline\CursusBundle\Manager\SessionManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * @Route("/training_session_user")
 */
class SessionUserController extends AbstractCrudController
{
    use PermissionCheckerTrait;

    private TokenStorageInterface $tokenStorage;
    private SessionManager $sessionManager;

    public function __construct(
        AuthorizationCheckerInterface $authorization,
        TokenStorageInterface $tokenStorage,
        SessionManager $sessionManager
    ) {
        $this->authorization = $authorization;
        $this->tokenStorage = $tokenStorage;
        $this->sessionManager = $sessionManager;
    }

    public function getName(): string
    {
        return 'training_session_user';
    }

    public function getClass(): ?string
    {
        return SessionUser::class;
    }

    /**
     * List registered users to sessions.
     *
     * @Route("/{id}", name="apiv2_training_session_user_list", methods={"GET"})
     * @Route("/{id}/{sessionId}", name="apiv2_training_session_user_list", methods={"GET"})
     * @EXT\ParamConverter("course", class="Claroline\CursusBundle\Entity\Course", options={"mapping": {"id": "uuid"}})
     */
    public function listByCourseAction(Request $request, Course $course, ?string $sessionId = null): JsonResponse
    {
        $this->checkPermission('REGISTER', $course, [], true);

        $params = $request->query->all();
        $params['hiddenFilters'] = $this->getDefaultHiddenFilters();

        if (!empty($sessionId)) {
            $params['hiddenFilters']['session'] = $sessionId;
        } else {
            $params['hiddenFilters']['course'] = $course->getUuid();
        }

        return new JsonResponse(
            $this->finder->search(SessionUser::class, $params)
        );
    }

    /**
     * Move user's registration from a session to another.
     *
     * @Route("/move/{type}/{targetId}", name="apiv2_training_session_user_move", methods={"PUT"})
     * @EXT\ParamConverter("session", class="Claroline\CursusBundle\Entity\Session", options={"mapping": {"targetId": "uuid"}})
     */
    public function moveAction(Session $session, string $type, Request $request): JsonResponse
    {
        $this->checkPermission('REGISTER', $session, [], true);

        $data = $this->decodeRequest($request);
        if (empty($data['sessionUsers'])) {
            throw new InvalidDataException('Missing user registrations to move.');
        }

        $sessionUsers = [];
        foreach ($data['sessionUsers'] as $sessionUserId) {
            $sessionUser = $this->om->getRepository(SessionUser::class)->findOneBy([
                'uuid' => $sessionUserId,
            ]);

            if (!empty($sessionUser)) {
                $sessionUsers[] = $sessionUser;
            }
        }

        $this->sessionManager->moveUsers($session, $sessionUsers, $type);

        return new JsonResponse();
    }

    /**
     * @Route("/confirm", name="apiv2_training_session_user_confirm", methods={"PUT"})
     */
    public function confirmAction(Request $request): JsonResponse
    {
        /** @var SessionUser[] $sessionUsers */
        $sessionUsers = $this->decodeIdsString($request, SessionUser::class);

        $this->om->startFlushSuite();
        foreach ($sessionUsers as $sessionUser) {
            $this->checkPermission('REGISTER', $sessionUser->getSession(), [], true);

            $this->sessionManager->confirmUsers([$sessionUser]);
        }
        $this->om->endFlushSuite();

        return new JsonResponse(array_map(function (SessionUser $sessionUser) {
            return $this->serializer->serialize($sessionUser);
        }, $sessionUsers));
    }

    /**
     * @Route("/validate", name="apiv2_cursus_session_validate_pending", methods={"PUT"})
     */
    public function validateAction(Request $request): JsonResponse
    {
        /** @var SessionUser[] $sessionUsers */
        $sessionUsers = $this->decodeIdsString($request, SessionUser::class);

        $this->om->startFlushSuite();
        foreach ($sessionUsers as $sessionUser) {
            $this->checkPermission('REGISTER', $sessionUser->getSession(), [], true);

            $this->sessionManager->validateUsers([$sessionUser]);
        }
        $this->om->endFlushSuite();

        return new JsonResponse(array_map(function (SessionUser $sessionUser) {
            return $this->serializer->serialize($sessionUser);
        }, $sessionUsers));
    }

    /**
     * @Route("/invite", name="apiv2_training_session_user_invite", methods={"PUT"})
     */
    public function inviteAction(Request $request): JsonResponse
    {
        /** @var SessionUser[] $sessionUsers */
        $sessionUsers = $this->decodeIdsString($request, SessionUser::class);

        $this->om->startFlushSuite();
        foreach ($sessionUsers as $sessionUser) {
            $this->checkPermission('REGISTER', $sessionUser->getSession());

            $this->sessionManager->sendSessionInvitation($sessionUser->getSession(), [$sessionUser->getUser()], !$sessionUser->isConfirmed());
        }
        $this->om->endFlushSuite();

        return new JsonResponse(null, 204);
    }

    protected function getDefaultHiddenFilters(): array
    {
        // only list participants of the same organization
        if (!$this->authorization->isGranted('ROLE_ADMIN')) {
            /** @var User $user */
            $user = $this->tokenStorage->getToken()->getUser();

            // filter by organizations
            $organizations = [];
            if ($user instanceof User) {
                $organizations = $user->getOrganizations();
            }

            return [
                'organizations' => array_map(function (Organization $organization) {
                    return $organization->getUuid();
                }, $organizations),
            ];
        }

        return [];
    }

    public function getIgnore(): array
    {
        return ['exist', 'copyBulk', 'schema', 'find', 'list'];
    }
}
