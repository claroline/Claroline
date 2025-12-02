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

use Claroline\AppBundle\API\Crud;
use Claroline\AppBundle\API\Finder\FinderRequest;
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
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

#[Route(path: '/workspace/archives')]
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

    #[Route(path: '/', name: 'apiv2_workspace_archive_list', methods: ['GET'])]
    public function listAction(
        #[MapQueryString]
        ?FinderRequest $finderRequest = new FinderRequest()
    ): StreamedJsonResponse {
        $this->checkPermission('IS_AUTHENTICATED_FULLY', null, [], true);

        $archives = $this->crud->search(Workspace::class, $finderRequest->addFilters([
            'archived' => true,
        ]), [SerializerInterface::SERIALIZE_LIST]);

        return $archives->toResponse();
    }

    #[Route(path: '/', name: 'apiv2_workspace_archive', methods: ['POST'])]
    public function archiveAction(Request $request): JsonResponse
    {
        $this->checkPermission('IS_AUTHENTICATED_FULLY', null, [], true);

        $this->om->startFlushSuite();

        $processed = [];
        $workspaceIds = $this->decodeRequest($request);

        /** @var Workspace[] $workspaces */
        $workspaces = $this->om->getRepository(Workspace::class)->findBy(['uuid' => $workspaceIds]);
        foreach ($workspaces as $workspace) {
            if (!$workspace->isArchived()) {
                $processed[] = $this->crud->replace($workspace, 'archived', true);
            }
        }

        $this->om->endFlushSuite();

        return new JsonResponse(array_map(function (Workspace $workspace) {
            return $this->serializer->serialize($workspace);
        }, $processed));
    }

    #[Route(path: '/', name: 'apiv2_workspace_restore', methods: ['PUT'])]
    public function restoreAction(Request $request): JsonResponse
    {
        $this->checkPermission('IS_AUTHENTICATED_FULLY', null, [], true);

        $this->om->startFlushSuite();

        $processed = [];
        $workspaceIds = $this->decodeRequest($request);

        /** @var Workspace[] $workspaces */
        $workspaces = $this->om->getRepository(Workspace::class)->findBy(['uuid' => $workspaceIds]);
        foreach ($workspaces as $workspace) {
            if ($workspace->isArchived()) {
                $processed[] = $this->crud->replace($workspace, 'archived', false);
            }
        }

        $this->om->endFlushSuite();

        return new JsonResponse(array_map(function (Workspace $workspace) {
            return $this->serializer->serialize($workspace);
        }, $processed));
    }
}
