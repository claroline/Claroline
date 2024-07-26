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
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * @Route("/badge-class")
 */
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

    public function getName(): string
    {
        return 'badge-class';
    }

    public function getClass(): string
    {
        return BadgeClass::class;
    }

    /**
     * @Route("/enable", name="apiv2_badge-class_enable", methods={"PUT"})
     */
    public function enableAction(Request $request): JsonResponse
    {
        $badges = $this->decodeIdsString($request, BadgeClass::class);

        foreach ($badges as $badge) {
            try {
                $this->crud->replace($badge, 'enabled', true);
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

    /**
     * @Route("/disable", name="apiv2_badge-class_disable", methods={"PUT"})
     */
    public function disableAction(Request $request): JsonResponse
    {
        $badges = $this->decodeIdsString($request, BadgeClass::class);

        foreach ($badges as $badge) {
            try {
                $this->crud->replace($badge, 'enabled', false);
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

    /**
     * @Route("/workspace/{workspace}", name="apiv2_badge-class_workspace_badge_list", methods={"GET"})
     *
     * @EXT\ParamConverter("workspace", class="Claroline\CoreBundle\Entity\Workspace\Workspace", options={"mapping": {"workspace": "uuid"}})
     */
    public function listByWorkspaceAction(Request $request, Workspace $workspace): JsonResponse
    {
        return new JsonResponse(
            $this->crud->list(BadgeClass::class, array_merge(
                $request->query->all(),
                ['hiddenFilters' => ['workspace' => $workspace->getUuid()]]
            ))
        );
    }

    /**
     * @Route("/{badge}/users", name="apiv2_badge-class_assertion", methods={"GET"})
     *
     * @EXT\ParamConverter("badge", class="Claroline\OpenBadgeBundle\Entity\BadgeClass", options={"mapping": {"badge": "uuid"}})
     */
    public function listUsersAction(Request $request, BadgeClass $badge): JsonResponse
    {
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

    /**
     * @Route("/{badge}/users/add", name="apiv2_badge-class_add_users", methods={"PATCH"})
     *
     * @EXT\ParamConverter("badge", class="Claroline\OpenBadgeBundle\Entity\BadgeClass", options={"mapping": {"badge": "uuid"}})
     */
    public function addUsersAction(BadgeClass $badge, Request $request): JsonResponse
    {
        $this->checkPermission('GRANT', $badge, [], true);

        $users = $this->decodeIdsString($request, User::class);

        foreach ($users as $user) {
            $this->assertionManager->create($badge, $user);
        }

        return new JsonResponse(
            $this->serializer->serialize($badge)
        );
    }

    /**
     * @Route("/{badge}/users/remove", name="apiv2_badge-class_remove_users", methods={"DELETE"})
     *
     * @EXT\ParamConverter("badge", class="Claroline\OpenBadgeBundle\Entity\BadgeClass", options={"mapping": {"badge": "uuid"}})
     */
    public function removeUsersAction(BadgeClass $badge, Request $request): JsonResponse
    {
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
     *
     * @Route("/{badge}/users/recalculate", name="apiv2_badge-class_recalculate_users", methods={"POST"})
     *
     * @EXT\ParamConverter("badge", class="Claroline\OpenBadgeBundle\Entity\BadgeClass", options={"mapping": {"badge": "uuid"}})
     */
    public function recalculateAction(BadgeClass $badge): JsonResponse
    {
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
            $user = $this->tokenStorage->getToken()->getUser();

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
