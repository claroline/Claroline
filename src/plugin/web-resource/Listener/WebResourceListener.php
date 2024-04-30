<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\WebResourceBundle\Listener;

use Claroline\AppBundle\API\SerializerProvider;
use Claroline\AppBundle\API\Utils\FileBag;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Component\Resource\ResourceComponent;
use Claroline\CoreBundle\Entity\Resource\AbstractResource;
use Claroline\CoreBundle\Entity\Resource\File;
use Claroline\CoreBundle\Event\Resource\ResourceActionEvent;
use Claroline\CoreBundle\Manager\ResourceManager;
use Claroline\WebResourceBundle\Manager\WebResourceManager;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\JsonResponse;

class WebResourceListener extends ResourceComponent
{
    public function __construct(
        private readonly string $filesDir,
        private readonly ObjectManager $om,
        private readonly string $uploadDir,
        private readonly WebResourceManager $webResourceManager,
        private readonly ResourceManager $resourceManager,
        private readonly SerializerProvider $serializer
    ) {
    }

    public static function getName(): string
    {
        return 'claroline_web_resource';
    }

    /** @var File $resource */
    public function open(AbstractResource $resource, bool $embedded = false): ?array
    {
        $ds = DIRECTORY_SEPARATOR;

        $hash = $resource->getHashName();
        $workspace = $resource->getResourceNode()->getWorkspace();
        $unzippedPath = $this->uploadDir.DIRECTORY_SEPARATOR.'webresource'.DIRECTORY_SEPARATOR.$workspace->getUuid();

        $srcPath = 'data/uploads'.DIRECTORY_SEPARATOR.'webresource'.DIRECTORY_SEPARATOR.$workspace->getUuid().DIRECTORY_SEPARATOR.$hash;
        if (!is_dir($srcPath)) {
            $this->webResourceManager->unzip($hash, $workspace);
        }

        return [
            'path' => rtrim($srcPath.$ds.$this->webResourceManager->guessRootFileFromUnzipped($unzippedPath.$ds.$hash), '/'),
            // common file data
            'file' => $this->serializer->serialize($resource),
        ];
    }

    /** @var File $resource */
    public function download(AbstractResource $resource): ?string
    {
        return $this->filesDir.DIRECTORY_SEPARATOR.'webresource'.
            DIRECTORY_SEPARATOR.$resource->getResourceNode()->getWorkspace()->getUuid().
            DIRECTORY_SEPARATOR.$resource->getHashName();
    }

    /** @var File $resource */
    public function export(AbstractResource $resource, FileBag $fileBag): ?array
    {
        $workspace = $resource->getResourceNode()->getWorkspace();

        $path = $this->uploadDir.DIRECTORY_SEPARATOR.'webresource'.DIRECTORY_SEPARATOR.$workspace->getUuid().DIRECTORY_SEPARATOR.$resource->getHashName();

        $fileBag->add($resource->getHashName(), $path);

        return [];
    }

    /** @var File $resource */
    public function import(AbstractResource $resource, FileBag $fileBag, array $data = []): void
    {
        $workspace = $resource->getResourceNode()->getWorkspace();

        $filesPath = $this->uploadDir.DIRECTORY_SEPARATOR.'webresource'.DIRECTORY_SEPARATOR.$workspace->getUuid().DIRECTORY_SEPARATOR.$resource->getHashName();

        $fileSystem = new Filesystem();
        $fileSystem->mirror($fileBag->get($resource->getHashName()), $filesPath);
    }

    /** @var File $resource */
    public function delete(AbstractResource $resource, FileBag $fileBag, bool $softDelete = true): bool
    {
        if ($softDelete) {
            return true;
        }

        $workspace = $resource->getResourceNode()->getWorkspace();
        $hashName = $resource->getHashName();

        $archiveFile = $this->filesDir.DIRECTORY_SEPARATOR.'webresource'.DIRECTORY_SEPARATOR.$workspace->getUuid().DIRECTORY_SEPARATOR.$hashName;
        if (file_exists($archiveFile)) {
            $fileBag->add($hashName.'-archive', $archiveFile);
        }

        $webResourcesPath = $this->uploadDir.DIRECTORY_SEPARATOR.'webresource'.DIRECTORY_SEPARATOR.$workspace->getUuid().DIRECTORY_SEPARATOR.$hashName;
        if (file_exists($webResourcesPath)) {
            $fileBag->add($hashName, $webResourcesPath);
        }

        return true;
    }

    /**
     * Changes actual file associated to File resource.
     */
    public function onFileChange(ResourceActionEvent $event)
    {
        $parameters = $event->getData();
        $node = $event->getResourceNode();

        $resource = $this->resourceManager->getResourceFromNode($node);

        if ($resource) {
            $resource->setHashName($parameters['file']['hashName']);
            $this->om->persist($resource);
            $this->om->flush();
        }

        $event->setResponse(new JsonResponse($this->serializer->serialize($node)));
    }
}
