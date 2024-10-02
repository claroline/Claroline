<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\OpenBadgeBundle\Controller;

use Claroline\AppBundle\API\Finder\FinderQuery;
use Claroline\AppBundle\API\Serializer\SerializerInterface;
use Claroline\AppBundle\Controller\AbstractCrudController;
use Claroline\CoreBundle\Entity\Organization\Organization;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Security\PermissionCheckerTrait;
use Claroline\CoreBundle\Validator\Exception\InvalidDataException;
use Claroline\OpenBadgeBundle\Entity\Assertion;
use Claroline\OpenBadgeBundle\Entity\BadgeClass;
use Claroline\OpenBadgeBundle\Manager\AssertionManager;
use Claroline\OpenBadgeBundle\Manager\BadgeManager;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\StreamedJsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

#[Route(path: '/badge', name: 'apiv2_badge_')]
class BadgeClassController extends AbstractCrudController
{
    use PermissionCheckerTrait;

    public function __construct(
        AuthorizationCheckerInterface $authorization,
        private readonly TokenStorageInterface $tokenStorage,
        private readonly BadgeManager $manager,
        private readonly AssertionManager $assertionManager
    ) {
        $this->authorization = $authorization;
    }

    public static function getName(): string
    {
        return 'badge';
    }

    public static function getClass(): string
    {
        return BadgeClass::class;
    }

    #[Route(path: '/workspace/{workspace}', name: 'workspace_list', methods: ['GET'])]
    public function listByWorkspaceAction(
        #[MapEntity(mapping: ['workspace' => 'uuid'])]
        Workspace $workspace,
        #[MapQueryString]
        ?FinderQuery $finderQuery = new FinderQuery()
    ): StreamedJsonResponse {
        $this->checkPermission('OPEN', $workspace, [], true);

        $finderQuery->addFilter('workspace', $workspace->getUuid());

        $assertions = $this->crud->search(BadgeClass::class, $finderQuery, [SerializerInterface::SERIALIZE_LIST]);

        return $assertions->toResponse();
    }

    #[Route(path: '/unarchive', name: 'unarchive', methods: ['PUT'])]
    public function unarchiveAction(Request $request): JsonResponse
    {
        $badges = $this->decodeIdsString($request, BadgeClass::class);

        foreach ($badges as $badge) {
            try {
                $this->crud->replace($badge, 'archived', false);
            } catch (\Exception $e) {
                // do not break the whole process if user has no right on one of the badges
            }
        }

        return new JsonResponse(
            array_map(function (BadgeClass $badge) {
                return $this->serializer->serialize($badge);
            }, $badges)
        );
    }

    #[Route(path: '/archive', name: 'archive', methods: ['PUT'])]
    public function archiveAction(Request $request): JsonResponse
    {
        $badges = $this->decodeIdsString($request, BadgeClass::class);

        foreach ($badges as $badge) {
            try {
                $this->crud->replace($badge, 'archived', true);
            } catch (\Exception $e) {
                // do not break the whole process if user has no right on one of the badges
            }
        }

        return new JsonResponse(
            array_map(function (BadgeClass $badge) {
                return $this->serializer->serialize($badge);
            }, $badges)
        );
    }

    #[Route(path: '/{badge}/users', name: 'list_assertions', methods: ['GET'])]
    public function listUsersAction(
        Request $request,
        #[MapEntity(mapping: ['badge' => 'uuid'])]
        BadgeClass $badge
    ): JsonResponse {
        if ($badge->getHideRecipients()) {
            $this->checkPermission('GRANT', $badge, [], true);
        } else {
            $this->checkPermission('OPEN', $badge, [], true);
        }

        return new JsonResponse(
            $this->crud->list(Assertion::class, array_merge(
                $request->query->all(),
                ['hiddenFilters' => ['badge' => $badge->getUuid(), 'revoked' => false]]
            ))
        );
    }

    #[Route(path: '/{badge}/users/add', name: 'add_users', methods: ['PATCH'])]
    public function addUsersAction(
        #[MapEntity(mapping: ['badge' => 'uuid'])]
        BadgeClass $badge,
        Request $request
    ): JsonResponse {
        $this->checkPermission('GRANT', $badge, [], true);

        $users = $this->decodeIdsString($request, User::class);

        foreach ($users as $user) {
            $this->assertionManager->create($badge, $user);
        }

        return new JsonResponse(
            $this->serializer->serialize($badge)
        );
    }

    #[Route(path: '/{badge}/users/remove', name: 'remove_users', methods: ['DELETE'])]
    public function removeUsersAction(
        #[MapEntity(mapping: ['badge' => 'uuid'])]
        BadgeClass $badge,
        Request $request
    ): JsonResponse {
        $this->checkPermission('GRANT', $badge, [], true);

        $assertions = $this->decodeIdsString($request, Assertion::class);

        foreach ($assertions as $assertion) {
            $this->assertionManager->delete($assertion);
        }

        return new JsonResponse(
            $this->serializer->serialize($badge)
        );
    }

    /**
     * Searches for users which meet the badge rules and grant them the badge.
     */
    #[Route(path: '/{badge}/users/recalculate', name: 'recalculate', methods: ['POST'])]
    public function recalculateAction(
        #[MapEntity(mapping: ['badge' => 'uuid'])]
        BadgeClass $badge
    ): JsonResponse {
        $this->checkPermission('GRANT', $badge, [], true);

        if (empty($badge->getRules())) {
            // we can only recompute badges with auto rules
            throw new InvalidDataException('The badge have no rules to check.');
        }

        $this->manager->grantAll($badge);

        return new JsonResponse();
    }

    protected function getDefaultHiddenFilters(): array
    {
        if (!$this->authorization->isGranted('ROLE_ADMIN')) {
            $user = $this->tokenStorage->getToken()?->getUser();

            if ($user instanceof User) {
                return [
                    'organizations' => array_map(function (Organization $organization) {
                        return $organization->getUuid();
                    }, $user->getOrganizations()),
                ];
            }

            return ['organizations' => []];
        }

        return [];
    }
}
