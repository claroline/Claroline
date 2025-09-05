<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Controller;

use Claroline\AppBundle\API\Crud;
use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\SerializerProvider;
use Claroline\AppBundle\Controller\RequestDecoderTrait;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Component\Resource\ResourceProvider;
use Claroline\CoreBundle\Entity\Resource\AbstractResource;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\Resource\ResourceRights;
use Claroline\CoreBundle\Event\CatalogEvents\ResourceEvents;
use Claroline\CoreBundle\Event\Resource\UpdateResourceEvent;
use Claroline\CoreBundle\Manager\Resource\ResourceRestrictionsManager;
use Claroline\CoreBundle\Manager\Resource\RightsManager;
use Claroline\CoreBundle\Manager\ResourceManager;
use Claroline\CoreBundle\Security\Collection\ResourceCollection;
use Claroline\CoreBundle\Security\PermissionCheckerTrait;
use Claroline\CoreBundle\Security\PlatformRoles;
use Claroline\CoreBundle\Validator\Exception\InvalidDataException;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * Manages platform resources.
 * ATTENTION. Be careful if you change routes order.
 */
#[Route(path: '/resources')]
class ResourceController
{
    use RequestDecoderTrait;
    use PermissionCheckerTrait;

    public function __construct(
        private readonly TokenStorageInterface $tokenStorage,
        private readonly SerializerProvider $serializer,
        private readonly ResourceProvider $resourceProvider,
        private readonly ResourceManager $manager,
        private readonly ResourceRestrictionsManager $restrictionsManager,
        private readonly ObjectManager $om,
        AuthorizationCheckerInterface $authorization,
        private readonly Crud $crud,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly RightsManager $rightsManager
    ) {
        $this->authorization = $authorization;
    }

    /**
     * Opens a resource.
     */
    #[Route(path: '/load/{id}', name: 'claro_resource_load', methods: ['GET'])]
    #[Route(path: '/load/{id}/embedded/{embedded}', name: 'claro_resource_load_embedded', methods: ['GET'])]
    public function openAction(string $id, int $embedded = 0): JsonResponse
    {
        /** @var ResourceNode $resourceNode */
        $resourceNode = $this->om->getRepository(ResourceNode::class)->findOneByUuidOrSlug($id);
        if (!$resourceNode) {
            return new JsonResponse('Resource not found.', 404);
        }

        if ($this->authorization->isGranted('OPEN', $resourceNode)) {
            $loaded = $this->manager->load($resourceNode, (bool) $embedded);

            return new JsonResponse(
                array_merge($loaded, [
                    'resourceNode' => $this->serializer->serialize($resourceNode, [Options::NO_RIGHTS]),
                ])
            );
        }

        // return the details of access errors to display it to users
        $userRoles = $this->tokenStorage->getToken()?->getRoleNames() ?? [PlatformRoles::ANONYMOUS];
        $accessErrors = $this->restrictionsManager->getErrors($resourceNode, $userRoles);

        return new JsonResponse([
            'resourceNode' => $this->serializer->serialize($resourceNode, [Options::NO_RIGHTS]),
            'accessErrors' => $accessErrors,
        ], 403);
    }

    #[Route(path: '/publish', name: 'claro_resource_publish', methods: ['PUT'])]
    public function publishAction(
        Request $request
    ): JsonResponse {
        $this->checkPermission('IS_AUTHENTICATED_FULLY', null, [], true);

        $data = $this->decodeRequest($request);

        $processed = [];

        $resourceNodes = $this->om->getRepository(ResourceNode::class)->findBy(['uuid' => $data]);
        foreach ($resourceNodes as $resourceNode) {
            if ($this->authorization->isGranted('EDIT', $resourceNode) && !$resourceNode->isPublished()) {
                $processed[] = $this->crud->update($resourceNode, [
                    'id' => $resourceNode->getUuid(),
                    'meta' => ['published' => true],
                ], [Crud::NO_PERMISSIONS, Crud::NO_VALIDATION]);
            }
        }

        return new JsonResponse(array_map(function (ResourceNode $resourceNode) {
            return $this->serializer->serialize($resourceNode, [Options::NO_RIGHTS]);
        }, $processed));
    }

    #[Route(path: '/unpublish', name: 'claro_resource_unpublish', methods: ['PUT'])]
    public function unpublishAction(
        Request $request
    ): JsonResponse {
        $this->checkPermission('IS_AUTHENTICATED_FULLY', null, [], true);

        $resourceIds = $this->decodeRequest($request);

        $processed = [];

        $resourceNodes = $this->om->getRepository(ResourceNode::class)->findBy(['uuid' => $resourceIds]);
        foreach ($resourceNodes as $resourceNode) {
            if ($this->authorization->isGranted('EDIT', $resourceNode) && $resourceNode->isPublished()) {
                $processed[] = $this->crud->update($resourceNode, [
                    'id' => $resourceNode->getUuid(),
                    'meta' => ['published' => false],
                ], [Crud::NO_PERMISSIONS, Crud::NO_VALIDATION]);
            }
        }

        return new JsonResponse(array_map(function (ResourceNode $resourceNode) {
            return $this->serializer->serialize($resourceNode, [Options::NO_RIGHTS]);
        }, $processed));
    }

    #[Route(path: '/', name: 'claro_resource_delete', methods: ['DELETE'])]
    public function deleteAction(Request $request): JsonResponse
    {
        $this->checkPermission('IS_AUTHENTICATED_FULLY', null, [], true);

        $resourceIds = $this->decodeRequest($request);
        $resourceNodes = $this->om->getRepository(ResourceNode::class)->findBy(['uuid' => $resourceIds]);

        $this->om->startFlushSuite();

        foreach ($resourceNodes as $resourceNode) {
            $this->crud->delete($resourceNode);
        }

        $this->om->endFlushSuite();

        return new JsonResponse(null, 204);
    }

    /**
     * Embeds a resource inside a rich text content.
     */
    #[Route(path: '/embed/{id}', name: 'claro_resource_embed', methods: ['GET'])]
    public function embedAction(ResourceNode $resourceNode): Response
    {
        return new Response($this->manager->embed($resourceNode));
    }

    /**
     * Downloads a list of Resources.
     */
    #[Route(path: '/download', name: 'claro_resource_download', methods: ['GET'])]
    public function downloadAction(Request $request): JsonResponse|BinaryFileResponse
    {
        $ids = $request->query->all('ids');
        $nodes = $this->om->getRepository(ResourceNode::class)->findBy(['uuid' => $ids]);

        $download = $this->manager->download($nodes);

        if (empty($download)) {
            return new JsonResponse(null, 404);
        }

        return new BinaryFileResponse($download['path'], 200, [
            'Content-Disposition' => "attachment; filename={$download['filename']}",
        ]);
    }

    /**
     * Submit access code.
     */
    #[Route(path: '/unlock/{id}', name: 'claro_resource_unlock', methods: ['POST'])]
    public function unlockAction(
        #[MapEntity(mapping: ['id' => 'uuid'])]
        ResourceNode $resourceNode,
        Request $request
    ): JsonResponse {
        $this->restrictionsManager->unlock($resourceNode, json_decode($request->getContent(), true)['code']);

        return new JsonResponse(null, 204);
    }

    /**
     * Checks if a resource is creatable for the submitted file.
     */
    #[Route(path: '/check/file/{resourceType}', name: 'claro_resource_check_file', defaults: ['resourceType' => null], methods: ['POST'])]
    public function checkFileAction(Request $request, ?string $resourceType = null): JsonResponse
    {
        $this->checkPermission('IS_AUTHENTICATED_FULLY', null, [], true);

        $files = $request->files->all();

        foreach ($files as $file) {
            $fileData = $this->resourceProvider->fromFile($file, $resourceType);
            if (empty($fileData)) {
                return new JsonResponse(null, 404);
            }

            return new JsonResponse($fileData);
        }

        return new JsonResponse(null, 404);
    }

    /**
     * Checks if a resource is creatable for the submitted url.
     */
    #[Route(path: '/check/url', name: 'claro_resource_check_url', methods: ['POST'])]
    public function checkUrlAction(Request $request): JsonResponse
    {
        $this->checkPermission('IS_AUTHENTICATED_FULLY', null, [], true);

        $urls = $this->decodeRequest($request);

        foreach ($urls as $url) {
            $urlData = $this->resourceProvider->fromUrl($url);
            if (empty($urlData)) {
                return new JsonResponse(null, 404);
            }

            return new JsonResponse($urlData);
        }

        return new JsonResponse(null, 404);
    }

    /**
     * Upload a collection of files and create the correct ressource types for it.
     */
    #[Route(path: '/upload/{parentId}', name: 'claro_resource_upload', methods: ['POST'])]
    public function uploadAction(
        #[MapEntity(mapping: ['parentId' => 'uuid'])]
        ResourceNode $parent,
        Request $request
    ): JsonResponse {
        // no need to secure endpoint the manager will do it for us.

        $newFiles = [];
        $uploadedFiles = $request->files->all();
        foreach ($uploadedFiles as $uploadedFile) {
            try {
                $fileData = $this->resourceProvider->fromFile($uploadedFile);
                if (!empty($fileData)) {
                    $newFiles[] = $this->manager->createResource($parent, ['resourceNode' => $fileData, 'resource' => $fileData]);
                }
            } catch (\Exception $e) {
                // do not break the whole process if one ressource fails
            }
        }

        return new JsonResponse(array_map(function (AbstractResource $resource) {
            return $this->serializer->serialize($resource->getResourceNode(), [Options::NO_RIGHTS]);
        }, $newFiles));
    }

    #[Route(path: '/copy/{destinationId}', name: 'claro_resource_copy', methods: ['POST'])]
    public function copyAction(
        #[MapEntity(mapping: ['destinationId' => 'uuid'])]
        ResourceNode $destination,
        Request $request
    ): JsonResponse {
        $this->checkPermission('IS_AUTHENTICATED_FULLY', null, [], true);

        $processed = [];

        $resourceIds = $this->decodeRequest($request);
        $toCopy = $this->om->getRepository(ResourceNode::class)->findBy(['uuid' => $resourceIds]);

        foreach ($toCopy as $resource) {
            // checks if the current user can copy the selected resource AND can create in the target directory
            $collection = new ResourceCollection([$resource], [
                'parent' => $destination,
                'type' => $resource->getType(),
            ]);

            if ($this->checkPermission('COPY', $collection)) {
                $processed[] = $this->crud->copy($resource, [Options::NO_RIGHTS, Crud::NO_PERMISSIONS], ['parent' => $destination]);
            }
        }

        return new JsonResponse(array_map(function (ResourceNode $resourceNode) {
            return $this->serializer->serialize($resourceNode, [Options::NO_RIGHTS]);
        }, $processed));
    }

    #[Route(path: '/move/{destinationId}', name: 'claro_resource_move', methods: ['PUT'])]
    public function moveAction(
        #[MapEntity(mapping: ['destinationId' => 'uuid'])]
        ResourceNode $destination,
        Request $request
    ): JsonResponse {
        $this->checkPermission('IS_AUTHENTICATED_FULLY', null, [], true);

        $processed = [];

        $resourceIds = $this->decodeRequest($request);
        $toMove = $this->om->getRepository(ResourceNode::class)->findBy(['uuid' => $resourceIds]);

        foreach ($toMove as $resource) {
            // checks if the current user can copy the selected resource AND can create in the target directory
            $collection = new ResourceCollection([$destination], ['type' => $resource->getType()]);

            if ($this->checkPermission('ADMINISTRATE', $resource) && $this->checkPermission('CREATE', $collection)) {
                $processed[] = $this->manager->move($resource, $destination);
            }
        }

        return new JsonResponse(array_map(function (ResourceNode $resourceNode) {
            return $this->serializer->serialize($resourceNode, [Options::NO_RIGHTS]);
        }, $processed));
    }

    #[Route(path: '/{parentId}', name: 'claro_resource_create', methods: ['POST'])]
    public function createAction(
        #[MapEntity(mapping: ['parentId' => 'uuid'])]
        ResourceNode $parent,
        Request $request
    ): JsonResponse {
        // no need to secure endpoint the manager will do it for us.

        $data = $this->decodeRequest($request);
        $newResource = $this->manager->createResource($parent, $data);

        return new JsonResponse([
            'resourceNode' => $this->serializer->serialize($newResource->getResourceNode(), [Options::NO_RIGHTS]),
            'resource' => $this->serializer->serialize($newResource),
        ], 201);
    }

    #[Route(path: '/{id}', name: 'claro_resource_update', methods: ['PUT'])]
    public function updateAction(
        #[MapEntity(mapping: ['id' => 'uuid'])]
        ResourceNode $resourceNode,
        Request $request
    ): JsonResponse {
        $this->checkPermission('EDIT', $resourceNode, [], true);

        $resource = $this->om
            ->getRepository($resourceNode->getClass())
            ->findOneBy(['resourceNode' => $resourceNode]);

        $data = $this->decodeRequest($request);

        $isManager = $this->authorization->isGranted('ADMINISTRATE', $resourceNode);

        // store data before update to forward it through event
        $previousData = [
            'resourceNode' => $this->serializer->serialize($resourceNode, [Options::NO_RIGHTS]),
            'resource' => $this->serializer->serialize($resource),
        ];

        if (!empty($data)) {
            $this->om->startFlushSuite();

            if (!empty($data['resourceNode'])) {
                try {
                    $this->crud->update($resourceNode, $data['resourceNode'], [Options::PERSIST_TAG]);
                } catch (InvalidDataException $e) {
                    // for resource edit we submit the resourceNode and resource data at once
                    // we need to update the errors path for correct rendering in form
                    $errors = array_map(function (array $error) {
                        return [
                            'path' => 'resourceNode/'.ltrim($error['path'], '/'),
                            'message' => $error['message'],
                        ];
                    }, $e->getErrors());

                    throw new InvalidDataException(sprintf('%s is not valid', ResourceNode::class), $errors);
                }
            }

            if (!empty($data['resource'])) {
                try {
                    $this->crud->update($resource, $data['resource'], [Options::PERSIST_TAG]);
                } catch (InvalidDataException $e) {
                    // for resource edit we submit the resourceNode and resource data at once
                    // we need to update the errors path for correct rendering in form
                    $errors = array_map(function (array $error) {
                        return [
                            'path' => 'resource/'.ltrim($error['path'], '/'),
                            'message' => $error['message'],
                        ];
                    }, $e->getErrors());

                    throw new InvalidDataException(sprintf('%s is not valid', get_class($resource)), $errors);
                }
            }

            if (!empty($data['rights']) && $isManager) {
                $this->crud->update($resourceNode, ['rights' => $data['rights']]);
            }

            $this->om->endFlushSuite();
        }

        $updateResource = new UpdateResourceEvent($resource, $data, $previousData);
        $this->eventDispatcher->dispatch($updateResource, ResourceEvents::getEventName(ResourceEvents::UPDATE, $resourceNode->getResourceType()->getName()));

        $this->om->refresh($resourceNode);

        return new JsonResponse(array_merge([
            'resource' => $this->serializer->serialize($resource),
        ], $updateResource->getResponse(), [
            'resourceNode' => $this->serializer->serialize($resourceNode, [Options::NO_RIGHTS]),
            'rights' => !empty($data['rights']) && $isManager ? array_map(function (ResourceRights $rights) {
                return $this->serializer->serialize($rights);
            }, $resourceNode->getRights()->toArray()) : [],
        ]));
    }
}
