<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Listener\Resource\Types;

use Claroline\AppBundle\API\Crud;
use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\SerializerProvider;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\File\PublicFile;
use Claroline\CoreBundle\Entity\Resource\AbstractResource;
use Claroline\CoreBundle\Entity\Resource\Directory;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Event\Resource\DeleteResourceEvent;
use Claroline\CoreBundle\Event\Resource\LoadResourceEvent;
use Claroline\CoreBundle\Event\Resource\ResourceActionEvent;
use Claroline\CoreBundle\Manager\FileManager;
use Claroline\CoreBundle\Manager\Resource\ResourceActionManager;
use Claroline\CoreBundle\Manager\Resource\RightsManager;
use Claroline\CoreBundle\Manager\ResourceManager;
use Claroline\CoreBundle\Security\Collection\ResourceCollection;
use Claroline\CoreBundle\Validator\Exception\InvalidDataException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Integrates the "Directory" resource.
 */
class DirectoryListener
{
    public function __construct(
        private readonly ObjectManager $om,
        private readonly SerializerProvider $serializer,
        private readonly Crud $crud,
        private readonly FileManager $fileManager,
        private readonly ResourceManager $resourceManager,
        private readonly ResourceActionManager $actionManager,
        private readonly RightsManager $rightsManager
    ) {
    }

    /**
     * Loads a directory.
     */
    public function onLoad(LoadResourceEvent $event): void
    {
        $event->setData([
            'directory' => $this->serializer->serialize($event->getResource()),
            'storageLock' => $this->fileManager->isStorageFull(),
        ]);

        $event->stopPropagation();
    }

    /**
     * Adds a new resource inside a directory.
     */
    public function onAdd(ResourceActionEvent $event): void
    {
        $data = $event->getData();
        $parent = $event->getResourceNode();

        $add = $this->actionManager->get($parent, 'add');

        // checks if the current user can add
        $collection = new ResourceCollection([$parent], ['type' => $data['resourceNode']['meta']['type']]);
        if (!$this->actionManager->hasPermission($add, $collection)) {
            throw new AccessDeniedException($collection->getErrorsForDisplay());
        }

        // create the resource node
        $created = $this->createResource($parent, $data['resourceNode'], !empty($data['resource']) ? $data['resource'] : [], $event->getOptions());

        $event->setResponse(new JsonResponse([
            'resourceNode' => $this->serializer->serialize($created->getResourceNode()),
            'resource' => $this->serializer->serialize($created),
        ], 201));
    }

    /**
     * Adds multiple files inside a directory.
     */
    public function onAddFiles(ResourceActionEvent $event): void
    {
        $files = $event->getFiles();
        $parent = $event->getResourceNode();

        $add = $this->actionManager->get($parent, 'add');

        $collection = new ResourceCollection([$parent], ['type' => 'file']);
        if (!$this->actionManager->hasPermission($add, $collection)) {
            throw new AccessDeniedException($collection->getErrorsForDisplay());
        }

        $publicFiles = [];
        foreach ($files as $file) {
            $publicFiles[] = $this->crud->create(PublicFile::class, [], ['file' => $file, Crud::THROW_EXCEPTION]);
        }

        $this->om->startFlushSuite();

        $resourceType = $this->resourceManager->getResourceTypeByName('file');
        $resources = [];
        foreach ($publicFiles as $publicFile) {
            $extension = pathinfo($publicFile->getFilename(), PATHINFO_EXTENSION);
            // clean up filename to generate the resource name
            $resourceName = str_replace('.'.$extension, '', $publicFile->getFilename());
            $resourceName = str_replace('_', ' ', $resourceName);
            $resourceName = ucfirst($resourceName);

            $created = $this->createResource($parent, [
                'name' => $resourceName,
                'meta' => [
                    'type' => $resourceType->getName(),
                    'mimeType' => $publicFile->getMimeType(),
                ],
            ], [
                'size' => $publicFile->getSize(),
                'hashName' => $publicFile->getUrl(),
            ], $event->getOptions());

            $resources[] = $created->getResourceNode();
        }
        $this->om->endFlushSuite();

        $event->setResponse(new JsonResponse(array_map(function (ResourceNode $fileNode) {
            return $this->serializer->serialize($fileNode);
        }, $resources)));
    }

    public function onDelete(DeleteResourceEvent $event): void
    {
        // delete all children of the current directory
        // this may be interesting to put it in the messenger bus
        $resourceNode = $event->getResource()->getResourceNode();

        if (!empty($resourceNode->getChildren())) {
            $this->crud->deleteBulk($resourceNode->getChildren()->toArray(), $event->isSoftDelete() ? [Options::SOFT_DELETE] : []);
        }
    }

    private function createResource(ResourceNode $parent, array $nodeData, ?array $resourceData = [], ?array $options = []): AbstractResource
    {
        $this->om->startFlushSuite();

        // initialize resource node Entity
        try {
            /** @var ResourceNode $resourceNode */
            $resourceNode = $this->crud->create(ResourceNode::class, $nodeData, array_merge([Options::NO_RIGHTS, Crud::THROW_EXCEPTION], $options));
            $resourceNode->setParent($parent);
            $resourceNode->setWorkspace($parent->getWorkspace());
        } catch (InvalidDataException $e) {
            // for resource creation we submit the resourceNode and resource data at once
            // we need to update the errors path for correct rendering in form
            $errors = array_map(function (array $error) {
                return [
                    'path' => 'resourceNode/'.ltrim($error['path'], '/'),
                    'message' => $error['message'],
                ];
            }, $e->getErrors());

            throw new InvalidDataException(sprintf('%s is not valid', ResourceNode::class), $errors);
        }

        // initialize custom resource Entity
        $resourceClass = $resourceNode->getResourceType()->getClass();

        try {
            /** @var AbstractResource $resource */
            $resource = new $resourceClass();
            $resource->setResourceNode($resourceNode);

            $this->crud->create($resource, $resourceData, array_merge([Crud::THROW_EXCEPTION], $options));
        } catch (InvalidDataException $e) {
            // for resource creation we submit the resourceNode and resource data at once
            // we need to update the errors path for correct rendering in form
            $errors = array_map(function (array $error) {
                return [
                    'path' => 'resource/'.ltrim($error['path'], '/'),
                    'message' => $error['message'],
                ];
            }, $e->getErrors());

            throw new InvalidDataException(sprintf('%s is not valid', $resourceClass), $errors);
        }

        $this->om->endFlushSuite();

        // initialize resource rights
        if (!empty($nodeData['rights'])) {
            foreach ($nodeData['rights'] as $rights) {
                /** @var Role $role */
                $role = $this->om->getRepository(Role::class)->findOneBy(['name' => $rights['name']]);

                $creation = [];
                if (!empty($rights['permissions']['create']) && $resource instanceof Directory) {
                    // only forward creation rights to resource which can handle it (only directories atm)
                    $creation = $rights['permissions']['create'];
                }
                $this->rightsManager->update($rights['permissions'], $role, $resourceNode, false, $creation);
            }
        } else {
            // copy parent rights on the new resource
            $this->rightsManager->copy($parent, $resourceNode);
        }

        return $resource;
    }
}
