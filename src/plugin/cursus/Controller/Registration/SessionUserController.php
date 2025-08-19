<?php

namespace Claroline\CursusBundle\Controller\Registration;

use Claroline\AppBundle\API\Finder\FinderQuery;
use Claroline\AppBundle\API\Serializer\SerializerInterface;
use Claroline\AppBundle\Controller\AbstractCrudController;
use Claroline\CoreBundle\Component\Context\WorkspaceContext;
use Claroline\CoreBundle\Entity\Organization\Organization;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Security\PermissionCheckerTrait;
use Claroline\CoreBundle\Validator\Exception\InvalidDataException;
use Claroline\CursusBundle\Entity\Course;
use Claroline\CursusBundle\Entity\Registration\SessionUser;
use Claroline\CursusBundle\Entity\Session;
use Claroline\CursusBundle\Manager\SessionManager;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\StreamedJsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

#[Route(path: '/training_session_user', name: 'apiv2_training_session_user_')]
class SessionUserController extends AbstractCrudController
{
    use PermissionCheckerTrait;

    public function __construct(
        AuthorizationCheckerInterface $authorization,
        private readonly TokenStorageInterface $tokenStorage,
        private readonly SessionManager $sessionManager
    ) {
        $this->authorization = $authorization;
    }

    public static function getName(): string
    {
        return 'training_session_user';
    }

    public static function getClass(): string
    {
        return SessionUser::class;
    }

    #[Route(path: '/context/{context}/{contextId}', name: 'context_list', methods: ['GET'])]
    public function listByContextAction(
        string $context,
        string $contextId = null,
        #[MapQueryString]
        ?FinderQuery $finderQuery = new FinderQuery()
    ): StreamedJsonResponse {
        if (WorkspaceContext::getName() === $context) {
            $finderQuery->addFilter('session.workspace', $contextId);
        }

        $options = static::getOptions();
        $assertions = $this->crud->search(SessionUser::class, $finderQuery, $options['list'] ?? []);

        return $assertions->toResponse();
    }

    /**
     * List registered users to sessions.
     */
    #[Route(path: '/{id}', name: 'list', methods: ['GET'])]
    #[Route(path: '/{id}/{sessionId}', name: 'course_list', methods: ['GET'])]
    public function listByCourseAction(
        #[MapEntity(mapping: ['id' => 'uuid'])]
        Course $course,
        string $sessionId = null,
        #[MapQueryString]
        ?FinderQuery $finderQuery = new FinderQuery()
    ): StreamedJsonResponse {
        $this->checkPermission('FOLLOW', $course, [], true);

        if (!empty($sessionId)) {
            $finderQuery->addFilter('session', $sessionId);
        } else {
            $finderQuery->addFilter('course', $course->getUuid());
        }

        $options = static::getOptions();
        $results = $this->crud->search(SessionUser::class, $finderQuery, $options['list'] ?? []);

        return $results->toResponse();
    }

    /**
     * Move user's registration from a session to another.
     */
    #[Route(path: '/move/{type}/{targetId}', name: 'move', defaults: ['targetId' => null], methods: ['PUT'])]
    public function moveAction(
        Request $request,
        string $type,
        #[MapEntity(mapping: ['targetId' => 'uuid'])]
        ?Session $session = null
    ): JsonResponse {
        $data = $this->decodeRequest($request);
        if (empty($data['sessionUsers'])) {
            throw new InvalidDataException('Missing user registrations to move.');
        }

        $sessionUsers = [];
        foreach ($data['sessionUsers'] as $sessionUserId) {
            $sessionUser = $this->om->getRepository(SessionUser::class)->findOneBy([
                'uuid' => $sessionUserId,
            ]);

            if (!empty($sessionUser) && $this->authorization->isGranted('ADMINISTRATE', $sessionUser)) {
                $sessionUsers[] = $sessionUser;
            }
        }

        $this->sessionManager->moveUsers($session, $sessionUsers, $type);

        return new JsonResponse();
    }

    #[Route(path: '/confirm', name: 'confirm', methods: ['PUT'])]
    public function confirmAction(Request $request): JsonResponse
    {
        $ids = $this->decodeRequest($request);
        $sessionUsers = $this->om->getRepository(SessionUser::class)->findBy(['uuid' => $ids]);

        $this->om->startFlushSuite();
        foreach ($sessionUsers as $sessionUser) {
            $this->checkPermission('EDIT', $sessionUser, [], true);

            $this->sessionManager->confirmUsers([$sessionUser]);
        }
        $this->om->endFlushSuite();

        return new JsonResponse(array_map(function (SessionUser $sessionUser) {
            return $this->serializer->serialize($sessionUser);
        }, $sessionUsers));
    }

    #[Route(path: '/validate', name: 'validate', methods: ['PUT'])]
    public function validateAction(Request $request): JsonResponse
    {
        $ids = $this->decodeRequest($request);
        $sessionUsers = $this->om->getRepository(SessionUser::class)->findBy(['uuid' => $ids]);

        $this->om->startFlushSuite();
        foreach ($sessionUsers as $sessionUser) {
            $this->checkPermission('ADMINISTRATE', $sessionUser, [], true);

            $this->sessionManager->validateUsers([$sessionUser]);
        }
        $this->om->endFlushSuite();

        return new JsonResponse(array_map(function (SessionUser $sessionUser) {
            return $this->serializer->serialize($sessionUser);
        }, $sessionUsers));
    }

    /**
     * Send an invitation message to a subset of the registered users.
     */
    #[Route(path: '/invite', name: 'invite', methods: ['PUT'])]
    public function inviteAction(Request $request): JsonResponse
    {
        $ids = $this->decodeRequest($request);
        $sessionUsers = $this->om->getRepository(SessionUser::class)->findBy(['uuid' => $ids]);

        $this->om->startFlushSuite();
        foreach ($sessionUsers as $sessionUser) {
            $this->checkPermission('FOLLOW', $sessionUser->getSession());

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
            $user = $this->tokenStorage->getToken()?->getUser();

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
}
