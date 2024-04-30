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

use Claroline\AppBundle\API\Utils\FileBag;
use Claroline\AppBundle\API\SerializerProvider;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Component\Resource\ResourceComponent;
use Claroline\CoreBundle\Entity\Resource\AbstractResource;
use Claroline\CoreBundle\Entity\Resource\File;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Event\Resource\File\LoadFileEvent;
use Claroline\CoreBundle\Event\Resource\ResourceActionEvent;
use Claroline\CoreBundle\Manager\FileManager;
use Ramsey\Uuid\Uuid;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Integrates the File resource into Claroline.
 */
class FileListener extends ResourceComponent
{
    public function __construct(
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly ObjectManager $om,
        private readonly SerializerProvider $serializer,
        private readonly FileManager $fileManager
    ) {
    }

    public static function getName(): string
    {
        return 'file';
    }

    /** @var File $resource */
    public function open(AbstractResource $resource, bool $embedded = false): ?array
    {
        $path = $this->fileManager->getDirectory().DIRECTORY_SEPARATOR.$resource->getHashName();

        $loadEvent = new LoadFileEvent($resource, $path);
        $this->eventDispatcher->dispatch($loadEvent, $this->generateEventName($resource->getResourceNode(), 'load'));

        if (!$loadEvent->isPopulated()) {
            // no listener found, try to dispatch the fallback event
            $this->eventDispatcher->dispatch($loadEvent, $this->generateEventName($resource->getResourceNode(), 'load', true));
        }

        return array_merge([], $loadEvent->getData(), [
            // we put event data first to be sure nobody override the file data
            'file' => $this->serializer->serialize($resource),
        ]);
    }

    /** @var File $resource */
    public function download(AbstractResource $resource): ?string
    {
        if ($this->fileManager->exists($resource->getHashName())) {
            return  $this->fileManager->getDirectory().DIRECTORY_SEPARATOR.$resource->getHashName();
        }

        return null;
    }

    /** @var File $resource */
    public function delete(AbstractResource $resource, FileBag $fileBag, bool $softDelete = true): bool
    {
        if ($softDelete && $this->fileManager->exists($resource->getHashName())) {
            $pathName = $this->fileManager->getDirectory().DIRECTORY_SEPARATOR.$resource->getHashName();
            $fileBag->add($resource->getHashName(), $pathName);
        }

        return true;
    }

    /** @var File $resource */
    public function export(AbstractResource $resource, FileBag $fileBag): ?array
    {
        if ($this->fileManager->exists($resource->getHashName())) {
            $path = $this->fileManager->getDirectory().DIRECTORY_SEPARATOR.$resource->getHashName();
            $fileBag->add($resource->getHashName(), $path);
        }

        return [];
    }

    /** @var File $resource */
    public function import(AbstractResource $resource, FileBag $fileBag, array $data = []): void
    {
        $workspace = $resource->getResourceNode()->getWorkspace();

        $realFile = $fileBag->get($resource->getHashName());
        if (empty($realFile)) {
            return;
        }

        $hashName = 'WORKSPACE_'.$workspace->getId().DIRECTORY_SEPARATOR.Uuid::uuid4()->toString();
        $ext = pathinfo($realFile, PATHINFO_EXTENSION);
        if ($ext) {
            $hashName .= '.'.$ext;
        }

        $fileSystem = new Filesystem();
        // create workspace dir if missing
        $fileSystem->mkdir($this->fileManager->getDirectory().DIRECTORY_SEPARATOR.'WORKSPACE_'.$workspace->getId());
        $fileSystem->copy($realFile, $this->fileManager->getDirectory().DIRECTORY_SEPARATOR.$hashName);

        $resource->setHashName($hashName);

        $this->om->persist($resource);
        $this->om->flush();
    }

    /**
     * @param File $original
     * @param File $copy
     */
    public function copy(AbstractResource $original, AbstractResource $copy): void
    {
        if (!$this->fileManager->exists($original->getHashName())) {
            return;
        }

        $destParent = $original->getResourceNode();
        $workspace = $destParent->getWorkspace();

        $hashName = 'WORKSPACE_'.$workspace->getId().DIRECTORY_SEPARATOR.Uuid::uuid4()->toString();
        $ext = pathinfo($original->getHashName(), PATHINFO_EXTENSION);
        if ($ext) {
            $hashName .= '.'.$ext;
        }
        $fileSystem = new Filesystem();
        // create workspace dir if missing
        $fileSystem->mkdir($this->fileManager->getDirectory().DIRECTORY_SEPARATOR.'WORKSPACE_'.$workspace->getId());
        $fileSystem->copy(
            $this->fileManager->getDirectory().DIRECTORY_SEPARATOR.$original->getHashName(),
            $this->fileManager->getDirectory().DIRECTORY_SEPARATOR.$hashName
        );

        $copy->setHashName($hashName);
        $copy->setSize($original->getSize());
    }

    /**
     * Changes actual file associated to File resource.
     */
    public function onFileChange(ResourceActionEvent $event): void
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

    private function generateEventName(ResourceNode $node, string $event, bool $useBaseType = false): string
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
