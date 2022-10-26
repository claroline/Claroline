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
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Resource\File;
use Claroline\CoreBundle\Event\Resource\CopyResourceEvent;
use Claroline\CoreBundle\Event\Resource\DeleteResourceEvent;
use Claroline\CoreBundle\Event\Resource\DownloadResourceEvent;
use Claroline\CoreBundle\Event\Resource\ExportResourceEvent;
use Claroline\CoreBundle\Event\Resource\ImportResourceEvent;
use Claroline\CoreBundle\Event\Resource\LoadResourceEvent;
use Claroline\CoreBundle\Event\Resource\ResourceActionEvent;
use Claroline\CoreBundle\Manager\ResourceManager;
use Claroline\WebResourceBundle\Manager\WebResourceManager;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\JsonResponse;

class WebResourceListener
{
    /** @var string */
    private $filesDir;

    /** @var ObjectManager */
    private $om;

    /** @var string */
    private $uploadDir;

    /** @var WebResourceManager */
    private $webResourceManager;

    /** @var ResourceManager */
    private $resourceManager;

    /**
     * WebResourceListener constructor.
     *
     * @param string $filesDir
     * @param string $uploadDir
     */
    public function __construct(
        $filesDir,
        ObjectManager $om,
        $uploadDir,
        WebResourceManager $webResourceManager,
        ResourceManager $resourceManager,
        SerializerProvider $serializer
    ) {
        $this->filesDir = $filesDir;
        $this->om = $om;
        $this->uploadDir = $uploadDir;
        $this->webResourceManager = $webResourceManager;
        $this->serializer = $serializer;
        $this->resourceManager = $resourceManager;
    }

    public function onLoad(LoadResourceEvent $event)
    {
        $ds = DIRECTORY_SEPARATOR;
        /** @var File $resource */
        $resource = $event->getResource();

        $hash = $resource->getHashName();
        $workspace = $resource->getResourceNode()->getWorkspace();
        $unzippedPath = $this->uploadDir.$ds.'webresource'.$ds.$workspace->getUuid();
        $srcPath = 'uploads'.$ds.'webresource'.$ds.$workspace->getUuid().$ds.$hash;

        if (!is_dir($srcPath)) {
            $this->webResourceManager->unzip($hash, $workspace);
        }

        $event->setData([
          'path' => rtrim($srcPath.$ds.$this->webResourceManager->guessRootFileFromUnzipped($unzippedPath.$ds.$hash), '/'),
          // common file data
          'file' => $this->serializer->serialize($resource),
        ]);

        $event->stopPropagation();
    }

    public function onExport(ExportResourceEvent $event)
    {
        /** @var File $webResource */
        $webResource = $event->getResource();
        $workspace = $webResource->getResourceNode()->getWorkspace();

        $path = $this->uploadDir.DIRECTORY_SEPARATOR.'webresource'.DIRECTORY_SEPARATOR.$workspace->getUuid().DIRECTORY_SEPARATOR.$webResource->getHashName();

        $event->addFile($webResource->getHashName(), $path);
    }

    public function onImport(ImportResourceEvent $event)
    {
        /** @var File $webResource */
        $webResource = $event->getResource();
        $workspace = $webResource->getResourceNode()->getWorkspace();
        $bag = $event->getFileBag();

        $filesPath = $this->uploadDir.DIRECTORY_SEPARATOR.'webresource'.DIRECTORY_SEPARATOR.$workspace->getUuid().DIRECTORY_SEPARATOR.$webResource->getHashName();

        $fileSystem = new Filesystem();
        $fileSystem->mirror($bag->get($webResource->getHashName()), $filesPath);
    }

    public function onDelete(DeleteResourceEvent $event)
    {
        /** @var File $resource */
        $resource = $event->getResource();
        $workspace = $resource->getResourceNode()->getWorkspace();
        $hashName = $resource->getHashName();

        $files = [];

        $archiveFile = $this->filesDir.DIRECTORY_SEPARATOR.'webresource'.DIRECTORY_SEPARATOR.$workspace->getUuid().DIRECTORY_SEPARATOR.$hashName;
        if (file_exists($archiveFile)) {
            $files[] = $archiveFile;
        }

        $webResourcesPath = $this->uploadDir.DIRECTORY_SEPARATOR.'webresource'.DIRECTORY_SEPARATOR.$workspace->getUuid().DIRECTORY_SEPARATOR.$hashName;
        if (file_exists($webResourcesPath)) {
            $files[] = $webResourcesPath;
        }

        $event->setFiles($files);
        $event->stopPropagation();
    }

    public function onCopy(CopyResourceEvent $event)
    {
        /** @var File $webResource */
        $webResource = $event->getResource();

        $file = $this->copy($webResource, $event->getCopy());
        $event->setCopy($file);
        $event->stopPropagation();
    }

    public function onDownload(DownloadResourceEvent $event)
    {
        /** @var File $resource */
        $resource = $event->getResource();

        $name = $this->filesDir.DIRECTORY_SEPARATOR.'webresource'.
          DIRECTORY_SEPARATOR.$resource->getResourceNode()->getWorkspace()->getUuid().
          DIRECTORY_SEPARATOR.$resource->getHashName();

        $event->setItem($name);
        $event->stopPropagation();
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
