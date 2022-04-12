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

use Claroline\AppBundle\API\SerializerProvider;
use Claroline\AppBundle\Event\Crud\CopyEvent;
use Claroline\AppBundle\Event\StrictDispatcher;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Resource\File;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Event\Resource\DeleteResourceEvent;
use Claroline\CoreBundle\Event\Resource\DownloadResourceEvent;
use Claroline\CoreBundle\Event\Resource\ExportResourceEvent;
use Claroline\CoreBundle\Event\Resource\File\LoadFileEvent;
use Claroline\CoreBundle\Event\Resource\ImportResourceEvent;
use Claroline\CoreBundle\Event\Resource\LoadResourceEvent;
use Claroline\CoreBundle\Event\Resource\ResourceActionEvent;
use Claroline\CoreBundle\Manager\FileManager;
use Claroline\CoreBundle\Manager\ResourceManager;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Integrates the File resource into Claroline.
 *
 * @todo : move some logic into a manager
 * @todo : move file resource into it's own plugin
 * @todo : maybe use tagged service for file types serialization (see exo items serialization)
 */
class FileListener
{
    /** @var TokenStorageInterface */
    private $tokenStorage;

    /** @var ObjectManager */
    private $om;

    /** @var StrictDispatcher */
    private $eventDispatcher;

    /** @var ResourceManager */
    private $resourceManager;

    /** @var SerializerProvider */
    private $serializer;

    /** @var FileManager */
    private $fileManager;

    public function __construct(
        TokenStorageInterface $tokenStorage,
        ObjectManager $om,
        StrictDispatcher $eventDispatcher,
        SerializerProvider $serializer,
        ResourceManager $resourceManager,
        FileManager $fileManager
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->om = $om;
        $this->eventDispatcher = $eventDispatcher;
        $this->serializer = $serializer;
        $this->resourceManager = $resourceManager;
        $this->fileManager = $fileManager;
    }

    public function onLoad(LoadResourceEvent $event)
    {
        /** @var File $resource */
        $resource = $event->getResource();
        $path = $this->fileManager->getDirectory().DIRECTORY_SEPARATOR.$resource->getHashName();

        $additionalFileData = [];

        /** @var LoadFileEvent $loadEvent */
        $loadEvent = $this->eventDispatcher->dispatch(
            $this->generateEventName($resource->getResourceNode(), 'load'),
            LoadFileEvent::class,
            [$resource, $path]
        );

        if ($loadEvent->isPopulated()) {
            $additionalFileData = $loadEvent->getData();
        } else {
            // no listener found, try to dispatch the fallback event
            /** @var LoadFileEvent $fallBackEvent */
            $fallBackEvent = $this->eventDispatcher->dispatch(
                $this->generateEventName($resource->getResourceNode(), 'load', true),
                LoadFileEvent::class,
                [$resource, $path]
            );

            if ($fallBackEvent->isPopulated()) {
                $additionalFileData = $fallBackEvent->getData();
            }
        }

        $event->setData([
            // common file data
            'file' => array_merge(
                $additionalFileData,
                // standard props are in 2nd to make sure custom file serializer doesn't override them
                $this->serializer->serialize($resource)
            ),
        ]);
    }

    /**
     * Changes actual file associated to File resource.
     */
    public function onFileChange(ResourceActionEvent $event)
    {
        /** @var File $file */
        $file = $event->getResource();
        $node = $event->getResourceNode();
        $data = $event->getData();

        if ($file && !empty($data) && !empty($data['file'])) {
            $file->setHashName($data['file']['url']);
            $file->setSize($data['file']['size']);

            $file->setMimeType($data['file']['mimeType']);
            $node->setMimeType($data['file']['mimeType']);
            $node->setModificationDate(new \DateTime());

            $this->om->persist($file);
            $this->om->persist($node);
            $this->om->flush();
        }

        $event->setResponse(
            new JsonResponse($this->serializer->serialize($node))
        );
    }

    public function onDelete(DeleteResourceEvent $event)
    {
        /** @var File $file */
        $file = $event->getResource();

        $pathName = $this->fileManager->getDirectory().DIRECTORY_SEPARATOR.$file->getHashName();
        if (file_exists($pathName)) {
            $event->setFiles([$pathName]);
        }

        $event->stopPropagation();
    }

    public function onExport(ExportResourceEvent $event)
    {
        /** @var File $file */
        $file = $event->getResource();
        $path = $this->fileManager->getDirectory().DIRECTORY_SEPARATOR.$file->getHashName();

        $event->addFile($file->getHashName(), $path);
    }

    public function onImport(ImportResourceEvent $event)
    {
        /** @var File $file */
        $file = $event->getResource();

        $bag = $event->getFileBag();

        $fileSystem = new Filesystem();
        try {
            $newHash = Uuid::uuid4()->toString();
            $fileSystem->copy($bag->get($file->getHashName()), $this->fileManager->getDirectory().DIRECTORY_SEPARATOR.$newHash);
            $file->setHashName($newHash);

            $this->om->persist($file);
            $this->om->flush();
        } catch (\Exception $e) {
        }
    }

    public function onCopy(CopyEvent $event)
    {
        /** @var File $resource */
        $resource = $event->getObject();
        /** @var File $newFile */
        $newFile = $event->getCopy();

        $destParent = $resource->getResourceNode();
        $workspace = $destParent->getWorkspace();

        $hashName = join('.', [
            'WORKSPACE_'.$workspace->getId(),
            Uuid::uuid4()->toString(),
            pathinfo($resource->getHashName(), PATHINFO_EXTENSION),
        ]);
        $newFile->setHashName($hashName);
        $newFile->setSize($resource->getSize());

        $filePath = $this->fileManager->getDirectory().DIRECTORY_SEPARATOR.$resource->getHashName();
        $newPath = $this->fileManager->getDirectory().DIRECTORY_SEPARATOR.$hashName;
        $workspaceDir = $this->fileManager->getDirectory().DIRECTORY_SEPARATOR.'WORKSPACE_'.$workspace->getId();

        if (!is_dir($workspaceDir)) {
            mkdir($workspaceDir);
        }

        try {
            copy($filePath, $newPath);
        } catch (\Exception $e) {
            //do nothing yet
            //maybe log an error
        }
    }

    public function onDownload(DownloadResourceEvent $event)
    {
        /** @var File $file */
        $file = $event->getResource();

        $event->setItem(
            $this->fileManager->getDirectory().DIRECTORY_SEPARATOR.$file->getHashName()
        );

        $event->stopPropagation();
    }

    private function generateEventName(ResourceNode $node, $event, $useBaseType = false)
    {
        $mimeType = $node->getMimeType();

        if ($useBaseType) {
            $mimeElements = explode('/', $mimeType);
            $suffix = strtolower($mimeElements[0]);
        } else {
            $suffix = $mimeType;
        }

        $eventName = strtolower(str_replace('/', '_', $suffix));
        $eventName = str_replace('"', '', $eventName);

        return 'file.'.$eventName.'.'.$event;
    }
}
