<?php

namespace Claroline\CursusBundle\Controller\Registration;

use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Claroline\AppBundle\Controller\AbstractCrudController;
use Claroline\CoreBundle\Entity\Organization\Organization;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Security\PermissionCheckerTrait;
use Claroline\CoreBundle\Validator\Exception\InvalidDataException;
use Claroline\CursusBundle\Entity\Course;
use Claroline\CursusBundle\Entity\Registration\SessionGroup;
use Claroline\CursusBundle\Entity\Session;
use Claroline\CursusBundle\Manager\SessionManager;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

#[Route(path: '/training_session_group', name: 'apiv2_training_session_group_')]
class SessionGroupController extends AbstractCrudController
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

    public static function getName(): string
    {
        return 'training_session_group';
    }

    public static function getClass(): string
    {
        return SessionGroup::class;
    }

    public function getIgnore(): array
    {
        return ['list'];
    }

    /**
     * List registered groups to sessions.
     *
     */
    #[Route(path: '/{id}', name: 'list', methods: ['GET'])]
    #[Route(path: '/{id}/{sessionId}', name: 'list', methods: ['GET'])]
    public function listByCourseAction(Request $request, #[MapEntity(class: 'Claroline\CursusBundle\Entity\Course', mapping: ['id' => 'uuid'])]
    Course $course, string $sessionId = null): JsonResponse
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
            $this->crud->list(SessionGroup::class, $params)
        );
    }

    /**
     * Move user's registration from a session to another.
     *
     */
    #[Route(path: '/move/{type}/{targetId}', name: 'move', methods: ['PUT'])]
    public function moveAction(#[MapEntity(class: 'Claroline\CursusBundle\Entity\Session', mapping: ['targetId' => 'uuid'])]
    Session $session, string $type, Request $request): JsonResponse
    {
        $this->checkPermission('REGISTER', $session, [], true);

        $data = $this->decodeRequest($request);
        if (empty($data['sessionGroups'])) {
            throw new InvalidDataException('Missing group registrations to move.');
        }

        $sessionGroups = [];
        foreach ($data['sessionGroups'] as $sessionGroupId) {
            $sessionGroup = $this->om->getRepository(SessionGroup::class)->findOneBy([
                'uuid' => $sessionGroupId,
            ]);

            if (!empty($sessionGroup)) {
                $sessionGroups[] = $sessionGroup;
            }
        }

        $this->sessionManager->moveGroups($session, $sessionGroups, $type);

        return new JsonResponse();
    }

    #[Route(path: '/invite', name: 'invite', methods: ['PUT'])]
    public function inviteAction(Request $request): JsonResponse
    {
        /** @var SessionGroup $sessionUsers */
        $sessionGroups = $this->decodeIdsString($request, SessionGroup::class);
        $users = [];
        foreach ($sessionGroups as $sessionGroup) {
            $this->checkPermission('REGISTER', $sessionGroup->getSession());

            $groupUsers = $this->om->getRepository(User::class)->findByGroup($sessionGroup->getGroup());

            foreach ($groupUsers as $user) {
                $users[$user->getUuid()] = $user;
            }

            $this->sessionManager->sendSessionInvitation($sessionGroup->getSession(), $users, false);
        }

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
