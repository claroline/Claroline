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

use Claroline\AppBundle\API\Crud;
use Claroline\AppBundle\API\Finder\FinderRequest;
use Claroline\AppBundle\API\Serializer\SerializerInterface;
use Claroline\AppBundle\API\SerializerProvider;
use Claroline\AppBundle\Controller\RequestDecoderTrait;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Security\PermissionCheckerTrait;
use Claroline\OpenBadgeBundle\Entity\BadgeClass;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\StreamedJsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

#[Route(path: '/badge/archives')]
class ArchiveController
{
    use PermissionCheckerTrait;
    use RequestDecoderTrait;

    public function __construct(
        AuthorizationCheckerInterface $authorization,
        private readonly ObjectManager $om,
        private readonly SerializerProvider $serializer,
        private readonly Crud $crud
    ) {
        $this->authorization = $authorization;
    }

    #[Route(path: '/{contextId}', name: 'apiv2_badge_archive_list', methods: ['GET'])]
    public function listAction(
        ?string $contextId = null,
        #[MapQueryString]
        ?FinderRequest $finderRequest = new FinderRequest()
    ): StreamedJsonResponse {
        $this->checkPermission('IS_AUTHENTICATED_FULLY', null, [], true);

        $finderRequest->addFilter('archived', true);
        if ($contextId) {
            $finderRequest->addFilter('workspace', $contextId);
        }

        $archives = $this->crud->search(BadgeClass::class, $finderRequest, [SerializerInterface::SERIALIZE_LIST]);

        return $archives->toResponse();
    }

    #[Route(path: '/', name: 'apiv2_badge_archive', methods: ['POST'])]
    public function archiveAction(Request $request): JsonResponse
    {
        $this->om->startFlushSuite();

        $processed = [];
        $badgeIds = $this->decodeRequest($request);

        /** @var BadgeClass[] $badges */
        $badges = $this->om->getRepository(BadgeClass::class)->findBy(['uuid' => $badgeIds]);
        foreach ($badges as $badge) {
            if (!$badge->isArchived()) {
                $processed[] = $this->crud->replace($badge, 'archived', true);
            }
        }

        $this->om->endFlushSuite();

        return new JsonResponse(array_map(function (BadgeClass $badge) {
            return $this->serializer->serialize($badge);
        }, $processed));
    }

    #[Route(path: '/', name: 'apiv2_badge_restore', methods: ['PUT'])]
    public function restoreAction(Request $request): JsonResponse
    {
        $this->om->startFlushSuite();

        $processed = [];
        $badgeIds = $this->decodeRequest($request);

        /** @var BadgeClass[] $badges */
        $badges = $this->om->getRepository(BadgeClass::class)->findBy(['uuid' => $badgeIds]);
        foreach ($badges as $badge) {
            if ($badge->isArchived()) {
                $processed[] = $this->crud->replace($badge, 'archived', false);
            }
        }

        $this->om->endFlushSuite();

        return new JsonResponse(array_map(function (BadgeClass $badge) {
            return $this->serializer->serialize($badge);
        }, $processed));
    }
}
