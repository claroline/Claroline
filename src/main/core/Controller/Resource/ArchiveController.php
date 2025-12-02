<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Controller\Resource;

use Claroline\AppBundle\API\Crud;
use Claroline\AppBundle\API\Finder\FinderRequest;
use Claroline\AppBundle\API\Serializer\SerializerInterface;
use Claroline\AppBundle\API\SerializerProvider;
use Claroline\AppBundle\Controller\RequestDecoderTrait;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Security\PermissionCheckerTrait;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\StreamedJsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

#[Route(path: '/resource/archives')]
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

    #[Route(path: '/{contextId}', name: 'claro_resource_archive_list', methods: ['GET'])]
    public function listAction(
        ?string $contextId = null,
        #[MapQueryString]
        ?FinderRequest $finderRequest = new FinderRequest()
    ): StreamedJsonResponse {
        $this->checkPermission('IS_AUTHENTICATED_FULLY', null, [], true);

        $finderRequest->addFilter('active', false);
        if ($contextId) {
            $finderRequest->addFilter('workspace', $contextId);
        }

        $archives = $this->crud->search(ResourceNode::class, $finderRequest, [SerializerInterface::SERIALIZE_LIST]);

        return $archives->toResponse();
    }

    #[Route(path: '/', name: 'claro_resource_archive', methods: ['POST'])]
    public function archiveAction(Request $request): JsonResponse
    {
        $this->checkPermission('IS_AUTHENTICATED_FULLY', null, [], true);

        $this->om->startFlushSuite();

        $processed = [];
        $resourceIds = $this->decodeRequest($request);

        /** @var ResourceNode[] $resourceNodes */
        $resourceNodes = $this->om->getRepository(ResourceNode::class)->findBy(['uuid' => $resourceIds]);
        foreach ($resourceNodes as $resourceNode) {
            if ($resourceNode->isActive()) {
                $processed[] = $this->crud->replace($resourceNode, 'active', false);
            }
        }

        $this->om->endFlushSuite();

        return new JsonResponse(array_map(function (ResourceNode $resourceNode) {
            return $this->serializer->serialize($resourceNode);
        }, $processed));
    }

    #[Route(path: '/', name: 'claro_resource_restore', methods: ['PUT'])]
    public function restoreAction(Request $request): JsonResponse
    {
        $this->checkPermission('IS_AUTHENTICATED_FULLY', null, [], true);

        $this->om->startFlushSuite();

        $processed = [];
        $resourceIds = $this->decodeRequest($request);

        /** @var ResourceNode[] $resourceNodes */
        $resourceNodes = $this->om->getRepository(ResourceNode::class)->findBy(['uuid' => $resourceIds]);
        foreach ($resourceNodes as $resourceNode) {
            if (!$resourceNode->isActive()) {
                $processed[] = $this->crud->replace($resourceNode, 'active', true);
            }
        }

        $this->om->endFlushSuite();

        return new JsonResponse(array_map(function (ResourceNode $resourceNode) {
            return $this->serializer->serialize($resourceNode);
        }, $processed));
    }
}
