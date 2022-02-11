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
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Integrates the "Directory" resource.
 */
class DirectoryListener
{
    /** @var ObjectManager */
    private $om;
    /** @var SerializerProvider */
    private $serializer;
    /** @var Crud */
    private $crud;
    /** @var FileManager */
    private $fileManager;
    /** @var ResourceManager */
    private $resourceManager;
    /** @var ResourceActionManager */
    private $actionManager;
    /** @var RightsManager */
    private $rightsManager;

    public function __construct(
        ObjectManager $om,
        SerializerProvider $serializer,
        Crud $crud,
        FileManager $fileManager,
        ResourceManager $resourceManager,
        ResourceActionManager $actionManager,
        RightsManager $rightsManager
    ) {
        $this->om = $om;
        $this->serializer = $serializer;
        $this->crud = $crud;
        $this->fileManager = $fileManager;
        $this->resourceManager = $resourceManager;
        $this->rightsManager = $rightsManager;
        $this->actionManager = $actionManager;
    }

    /**
     * Loads a directory.
     */
    public function onLoad(LoadResourceEvent $event)
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
    public function onAdd(ResourceActionEvent $event)
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
            'resourceNode' => $this->serializer->serialize($created['resourceNode']),
            'resource' => $this->serializer->serialize($created['resource']),
        ], 201));
    }

    /**
     * Adds multiple files inside a directory.
     */
    public function onAddFiles(ResourceActionEvent $event)
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
            $created = $this->createResource($parent, [
                'name' => $publicFile->getFilename(),
                'meta' => [
                    'type' => $resourceType->getName(),
                    'mimeType' => $publicFile->getMimeType(),
                ],
            ], [
                'size' => $publicFile->getSize(),
                'hashName' => $publicFile->getUrl(),
            ], $event->getOptions());

            $resources[] = $created['resourceNode'];
        }
        $this->om->endFlushSuite();

        return new JsonResponse(array_map(function (ResourceNode $fileNode) {
            return $this->serializer->serialize($fileNode);
        }, $resources));
    }

    public function onDelete(DeleteResourceEvent $event)
    {
        // delete all children of the current directory
        // this may by interesting to put it in the messenger bus
        $resourceNode = $event->getResource()->getResourceNode();

        if (!empty($resourceNode->getChildren())) {
            $this->crud->deleteBulk($resourceNode->getChildren()->toArray(), $event->isSoftDelete() ? [Options::SOFT_DELETE] : []);
        }
    }

    private function createResource(ResourceNode $parent, array $nodeData, ?array $resourceData = [], ?array $options = [])
    {
        /** @var ResourceNode $resourceNode */
        $resourceNode = $this->crud->create(ResourceNode::class, $nodeData, $options);
        $resourceNode->setParent($parent);
        $resourceNode->setWorkspace($parent->getWorkspace());

        // initialize custom resource Entity
        $resourceClass = $resourceNode->getResourceType()->getClass();

        /** @var AbstractResource $resource */
        $resource = $this->crud->create($resourceClass, $resourceData, $options);
        $resource->setResourceNode($resourceNode);

        // maybe do it in the serializer (if it can be done without intermediate flush)
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

        $this->om->persist($resource);
        $this->om->persist($resourceNode);

        $this->om->flush();

        return [
            'resourceNode' => $resourceNode,
            'resource' => $resource,
        ];
    }
}
