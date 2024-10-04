<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Controller\Workspace;

use Claroline\AppBundle\Annotations\ApiDoc;
use Claroline\AppBundle\API\Crud;
use Claroline\AppBundle\API\Finder\FinderQuery;
use Claroline\AppBundle\API\Serializer\SerializerInterface;
use Claroline\AppBundle\API\SerializerProvider;
use Claroline\AppBundle\Controller\RequestDecoderTrait;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Security\PermissionCheckerTrait;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\StreamedJsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

#[Route(path: '/workspace/archives')]
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
     *     description="The list of archived workspace for the current security token.",
     *     queryString={
     *         "$finder=Claroline\CoreBundle\Entity\Workspace\Workspace&!archived",
     *         {"name": "page", "type": "integer", "description": "The queried page."},
     *         {"name": "limit", "type": "integer", "description": "The max amount of objects per page."},
     *         {"name": "sortBy", "type": "string", "description": "Sort by the property if you want to."}
     *     }
     * )
     */
    #[Route(path: '/', name: 'apiv2_workspace_archive_list', methods: ['GET'])]
    public function listAction(
        #[MapQueryString]
        ?FinderQuery $finderQuery = new FinderQuery()
    ): StreamedJsonResponse {
        $this->checkPermission('IS_AUTHENTICATED_FULLY', null, [], true);

        $finderQuery->addFilters([
            'archived' => true,
            /*'roles' => $this->tokenStorage->getToken()->getRoleNames(),
            'administrated' => true,*/
        ]);

        $archives = $this->crud->search(Workspace::class, $finderQuery, [SerializerInterface::SERIALIZE_LIST]);

        return $archives->toResponse();
    }

    /**
     * @ApiDoc(
     *     description="Archive workspaces.",
     *     queryString={
     *         {"name": "ids", "type": "array", "description": "the list of workspace uuids."}
     *     }
     * )
     */
    #[Route(path: '/', name: 'apiv2_workspace_archive', methods: ['POST'])]
    public function archiveAction(Request $request): JsonResponse
    {
        $processed = [];

        $this->om->startFlushSuite();

        /** @var Workspace[] $workspaces */
        $workspaces = self::decodeIdsString($request, Workspace::class);
        foreach ($workspaces as $workspace) {
            if (!$workspace->isArchived()) {
                $this->crud->replace($workspace, 'archived', true);
                $processed[] = $workspace;
            }
        }

        $this->om->endFlushSuite();

        return new JsonResponse(array_map(function (Workspace $workspace) {
            return $this->serializer->serialize($workspace);
        }, $processed));
    }

    /**
     * @ApiDoc(
     *     description="Unarchive workspaces.",
     *     queryString={
     *         {"name": "ids", "type": "array", "description": "the list of workspace uuids."}
     *     }
     * )
     */
    #[Route(path: '/', name: 'apiv2_workspace_restore', methods: ['PUT'])]
    public function restoreAction(Request $request): JsonResponse
    {
        $processed = [];

        $this->om->startFlushSuite();

        /** @var Workspace[] $workspaces */
        $workspaces = self::decodeIdsString($request, Workspace::class);
        foreach ($workspaces as $workspace) {
            if ($workspace->isArchived()) {
                $this->crud->replace($workspace, 'archived', false);
                $processed[] = $workspace;
            }
        }

        $this->om->endFlushSuite();

        return new JsonResponse(array_map(function (Workspace $workspace) {
            return $this->serializer->serialize($workspace);
        }, $processed));
    }
}
