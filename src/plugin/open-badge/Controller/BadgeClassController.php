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

use Claroline\AppBundle\API\Finder\FinderRequest;
use Claroline\AppBundle\API\Serializer\SerializerInterface;
use Claroline\AppBundle\Controller\AbstractCrudController;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Security\PermissionCheckerTrait;
use Claroline\CoreBundle\Validator\Exception\InvalidDataException;
use Claroline\OpenBadgeBundle\Component\BadgeRule\RuleInterface;
use Claroline\OpenBadgeBundle\Component\BadgeRule\RuleProvider;
use Claroline\OpenBadgeBundle\Entity\Assertion;
use Claroline\OpenBadgeBundle\Entity\BadgeClass;
use Claroline\OpenBadgeBundle\Entity\Evidence;
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
        private readonly RuleProvider $ruleProvider,
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
        ?FinderRequest $finderRequest = new FinderRequest()
    ): StreamedJsonResponse {
        $this->checkPermission('OPEN', $workspace, [], true);

        $finderRequest->addFilter('workspace', $workspace->getUuid());

        $assertions = $this->crud->search(BadgeClass::class, $finderRequest, [SerializerInterface::SERIALIZE_LIST]);

        return $assertions->toResponse();
    }

    #[Route(path: '/{badge}/users', name: 'list_assertions', methods: ['GET'])]
    public function listUsersAction(
        #[MapEntity(mapping: ['badge' => 'uuid'])]
        BadgeClass $badge,
        #[MapQueryString]
        ?FinderRequest $finderRequest = new FinderRequest()
    ): StreamedJsonResponse {
        if ($badge->getHideRecipients()) {
            $this->checkPermission('FOLLOW', $badge, [], true);
        } else {
            $this->checkPermission('OPEN', $badge, [], true);
        }

        $finderRequest->addFilter('badge', $badge->getUuid());

        $assertions = $this->crud->search(Assertion::class, $finderRequest, [SerializerInterface::SERIALIZE_LIST]);

        return $assertions->toResponse();
    }

    #[Route(path: '/{badge}/users/current', name: 'current_user', methods: ['GET'])]
    public function getCurrentUserAction(
        #[MapEntity(mapping: ['badge' => 'uuid'])]
        BadgeClass $badge
    ): JsonResponse {
        $this->checkPermission('OPEN', $badge, [], true);

        if ($this->tokenStorage->getToken()?->getUser()) {
            $assertion = $this->om->getRepository(Assertion::class)->findOneBy([
                'badge' => $badge,
                'recipient' => $this->tokenStorage->getToken()?->getUser(),
            ]);
            $evidences = $this->om->getRepository(Evidence::class)->findByUserAndBadge($this->tokenStorage->getToken()?->getUser(), $badge);

            if ($assertion || !empty($evidences)) {
                return new JsonResponse([
                    'assertion' => $assertion ? $this->serializer->serialize($assertion) : null,
                    'evidences' => array_map(function (Evidence $evidence) {
                        return $this->serializer->serialize($evidence, [SerializerInterface::SERIALIZE_MINIMAL]);
                    }, $evidences),
                ]);
            }
        }

        return new JsonResponse(null, 204);
    }

    #[Route(path: '/{badge}/users/add', name: 'add_users', methods: ['PATCH'])]
    public function addUsersAction(
        #[MapEntity(mapping: ['badge' => 'uuid'])]
        BadgeClass $badge,
        Request $request
    ): JsonResponse {
        $this->checkPermission('FOLLOW', $badge, [], true);

        $ids = $this->decodeRequest($request);
        $users = $this->om->getRepository(User::class)->findBy(['uuid' => $ids]);

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
        $this->checkPermission('FOLLOW', $badge, [], true);

        $ids = $this->decodeRequest($request);
        $assertions = $this->om->getRepository(Assertion::class)->findBy(['uuid' => $ids]);

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
        $this->checkPermission('FOLLOW', $badge, [], true);

        if (empty($badge->getRules())) {
            // we can only recompute badges with auto rules
            throw new InvalidDataException('The badge have no rules to check.');
        }

        $this->manager->grantAll($badge);

        return new JsonResponse();
    }

    /**
     * Gets the list of available tools (all tools implemented, not only the enabled ones in the context).
     */
    #[Route(path: '/rules/{context}/{contextId}', name: 'available_rules', methods: ['GET'])]
    public function getAvailableToolsAction(string $context, ?string $contextId = null): JsonResponse
    {
        $this->checkPermission('IS_AUTHENTICATED_FULLY', null, [], true);

        $rules = $this->ruleProvider->getAvailableRules($context, $contextId);

        return new JsonResponse(array_map(function (RuleInterface $rule) {
            return $rule::getName();
        }, $rules));
    }
}
