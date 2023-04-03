<?php

namespace Claroline\CursusBundle\Controller\Registration;

use Claroline\AppBundle\Controller\AbstractCrudController;
use Claroline\CoreBundle\Entity\Organization\Organization;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Security\PermissionCheckerTrait;
use Claroline\CoreBundle\Validator\Exception\InvalidDataException;
use Claroline\CursusBundle\Entity\Course;
use Claroline\CursusBundle\Entity\Registration\SessionGroup;
use Claroline\CursusBundle\Entity\Session;
use Claroline\CursusBundle\Manager\SessionManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * @Route("/training_session_group")
 */
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

    public function getName(): string
    {
        return 'training_session_group';
    }

    public function getClass(): ?string
    {
        return SessionGroup::class;
    }

    /**
     * List registered groups to sessions.
     *
     * @Route("/{id}", name="apiv2_training_session_group_list", methods={"GET"})
     * @Route("/{id}/{sessionId}", name="apiv2_training_session_group_list", methods={"GET"})
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
            $this->finder->search(SessionGroup::class, $params)
        );
    }

    /**
     * Move user's registration from a session to another.
     *
     * @Route("/move/{type}/{targetId}", name="apiv2_training_session_group_move", methods={"PUT"})
     * @EXT\ParamConverter("session", class="Claroline\CursusBundle\Entity\Session", options={"mapping": {"targetId": "uuid"}})
     */
    public function moveAction(Session $session, string $type, Request $request): JsonResponse
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

    /**
     * @Route("/invite", name="apiv2_training_session_group_invite", methods={"PUT"})
     */
    public function inviteAction(Request $request): JsonResponse
    {
        /** @var SessionGroup $sessionUsers */
        $sessionGroups = $this->decodeIdsString($request, SessionGroup::class);
        $users = [];
        foreach ($sessionGroups as $sessionGroup) {
            $this->checkPermission('REGISTER', $sessionGroup->getSession());

            $groupUsers = $sessionGroup->getGroup()->getUsers();

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
