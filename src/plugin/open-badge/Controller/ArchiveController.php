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

use Claroline\AppBundle\Annotations\ApiDoc;
use Claroline\AppBundle\API\Crud;
use Claroline\AppBundle\API\Finder\FinderQuery;
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
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

#[Route(path: '/badge/archives')]
class ArchiveController
{
    use PermissionCheckerTrait;
    use RequestDecoderTrait;

    public function __construct(
        private readonly TokenStorageInterface $tokenStorage,
        AuthorizationCheckerInterface $authorization,
        private readonly ObjectManager $om,
        private readonly SerializerProvider $serializer,
        private readonly Crud $crud
    ) {
        $this->authorization = $authorization;
    }

    /**
     * @ApiDoc(
     *     description="The list of archived badges for the current security token.",
     *     queryString={
     *         "$finder=Claroline\OpenBadgeBundle\Entity\BadgeClass&!archived",
     *         {"name": "page", "type": "integer", "description": "The queried page."},
     *         {"name": "limit", "type": "integer", "description": "The max amount of objects per page."},
     *         {"name": "sortBy", "type": "string", "description": "Sort by the property if you want to."}
     *     }
     * )
     */
    #[Route(path: '/{contextId}', name: 'apiv2_badge_archive_list', methods: ['GET'])]
    public function listAction(
        ?string $contextId = null,
        #[MapQueryString]
        ?FinderQuery $finderQuery = new FinderQuery()
    ): StreamedJsonResponse {
        $this->checkPermission('IS_AUTHENTICATED_FULLY', null, [], true);

        $finderQuery->addFilter('archived', true);
        if ($contextId) {
            $finderQuery->addFilter('workspace', $contextId);
        }

        $archives = $this->crud->search(BadgeClass::class, $finderQuery, [SerializerInterface::SERIALIZE_LIST]);

        return $archives->toResponse();
    }

    /**
     * @ApiDoc(
     *     description="Archive badges.",
     *     queryString={
     *         {"name": "ids", "type": "array", "description": "the list of badge uuids."}
     *     }
     * )
     */
    #[Route(path: '/', name: 'apiv2_badge_archive', methods: ['POST'])]
    public function archiveAction(Request $request): JsonResponse
    {
        $processed = [];

        $this->om->startFlushSuite();

        /** @var BadgeClass[] $badges */
        $badges = self::decodeIdsString($request, BadgeClass::class);
        foreach ($badges as $badge) {
            if (!$badge->isArchived()) {
                $this->crud->replace($badge, 'archived', true);
                $processed[] = $badge;
            }
        }

        $this->om->endFlushSuite();

        return new JsonResponse(array_map(function (BadgeClass $badge) {
            return $this->serializer->serialize($badge);
        }, $processed));
    }

    /**
     * @ApiDoc(
     *     description="Unarchive badges.",
     *     queryString={
     *         {"name": "ids", "type": "array", "description": "the list of badge uuids."}
     *     }
     * )
     */
    #[Route(path: '/', name: 'apiv2_badge_restore', methods: ['PUT'])]
    public function restoreAction(Request $request): JsonResponse
    {
        $processed = [];

        $this->om->startFlushSuite();

        /** @var BadgeClass[] $badges */
        $badges = self::decodeIdsString($request, BadgeClass::class);
        foreach ($badges as $badge) {
            if ($badge->isArchived()) {
                $this->crud->replace($badge, 'archived', false);
                $processed[] = $badge;
            }
        }

        $this->om->endFlushSuite();

        return new JsonResponse(array_map(function (BadgeClass $badge) {
            return $this->serializer->serialize($badge);
        }, $processed));
    }
}
