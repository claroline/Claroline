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
use Claroline\CoreBundle\Event\ExportObjectEvent;
use Claroline\CoreBundle\Event\ImportObjectEvent;
use Claroline\CoreBundle\Event\Resource\CopyResourceEvent;
use Claroline\CoreBundle\Event\Resource\DeleteResourceEvent;
use Claroline\CoreBundle\Event\Resource\DownloadResourceEvent;
use Claroline\CoreBundle\Event\Resource\LoadResourceEvent;
use Claroline\CoreBundle\Event\Resource\ResourceActionEvent;
use Claroline\CoreBundle\Manager\ResourceManager;
use Claroline\WebResourceBundle\Manager\WebResourceManager;
use JMS\DiExtraBundle\Annotation as DI;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * @DI\Service("claroline.listener.web_resource_listener")
 */
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
     * @DI\InjectParams({
     *     "filesDir"           = @DI\Inject("%claroline.param.files_directory%"),
     *     "om"                 = @DI\Inject("claroline.persistence.object_manager"),
     *     "uploadDir"          = @DI\Inject("%claroline.param.uploads_directory%"),
     *     "serializer"         = @DI\Inject("claroline.api.serializer"),
     *     "webResourceManager" = @DI\Inject("Claroline\WebResourceBundle\Manager\WebResourceManager"),
     *     "resourceManager"    = @DI\Inject("claroline.manager.resource_manager")
     * })
     *
     * @param string             $filesDir
     * @param ObjectManager      $om
     * @param string             $uploadDir
     * @param ResourceManager    $resourceManager
     * @param WebResourceManager $webResourceManager
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

    /**
     * @DI\Observe("resource.claroline_web_resource.load")
     *
     * @param LoadResourceEvent $event
     */
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

    /**
     * @DI\Observe("transfer.claroline_web_resource.import.before")
     */
    public function onImportBefore(ImportObjectEvent $event)
    {
        $data = $event->getData();
        $replaced = json_encode($event->getExtra());

        $hashName = pathinfo($data['hashName'], PATHINFO_BASENAME);
        $uuid = Uuid::uuid4()->toString();
        $replaced = str_replace($hashName, $uuid, $replaced);

        $data = json_decode($replaced, true);
        $event->setExtra($data);
    }

    /**
     * @DI\Observe("transfer.claroline_web_resource.export")
     */
    public function onExportFile(ExportObjectEvent $exportEvent)
    {
        $file = $exportEvent->getObject();
        $workspace = $exportEvent->getWorkspace();
        $ds = DIRECTORY_SEPARATOR;
        $path = $this->uploadDir.$ds.'webresource'.$ds.$workspace->getUuid().$ds.$file->getHashName();
        //probably make it a zip here
        $file = $exportEvent->getObject();
        $newPath = uniqid().'.'.pathinfo($file->getHashName(), PATHINFO_EXTENSION);
        //get the filePath
        $exportEvent->addFile($newPath, $path);
        $exportEvent->overwrite('_path', $newPath);
    }

    /**
     * @DI\Observe("transfer.claroline_web_resource.import.after")
     */
    public function onImportFile(ImportObjectEvent $event)
    {
        $data = $event->getData();
        $bag = $event->getFileBag();
        $workspace = $event->getWorkspace();

        $fileSystem = new Filesystem();

        $ds = DIRECTORY_SEPARATOR;
        $filesPath = $this->uploadDir.$ds.'webresource'.$ds.$workspace->getUuid().$ds.$data['hashName'];
        $fileSystem->mirror($bag->get($data['_path']), $filesPath);
    }

    /**
     * @DI\Observe("resource.claroline_web_resource.delete")
     *
     * @param DeleteResourceEvent $event
     */
    public function onDelete(DeleteResourceEvent $event)
    {
        $ds = DIRECTORY_SEPARATOR;

        /** @var File $resource */
        $resource = $event->getResource();
        $workspace = $resource->getResourceNode()->getWorkspace();
        $hashName = $resource->getHashName();

        $archiveFile = $this->filesDir.$ds.'webresource'.$ds.$workspace->getUuid().$ds.$hashName;
        $webResourcesPath = $this->uploadDir.$ds.'webresource'.$ds.$workspace->getUuid().$ds.$hashName;

        if (file_exists($archiveFile)) {
            $event->setFiles([$archiveFile]);
        }
        if (file_exists($webResourcesPath)) {
            try {
                $this->deleteFiles($webResourcesPath);
            } catch (\Exception $e) {
            }
        }
        $this->om->remove($resource);
        $event->stopPropagation();
    }

    /**
     * @DI\Observe("resource.claroline_web_resource.copy")
     *
     * @param CopyResourceEvent $event
     */
    public function onCopy(CopyResourceEvent $event)
    {
        /** @var File $webResource */
        $webResource = $event->getResource();

        $file = $this->copy($webResource, $event->getCopy());
        $event->setCopy($file);
        $event->stopPropagation();
    }

    /**
     * @DI\Observe("download_claroline_web_resource")
     *
     * @param DownloadResourceEvent $event
     */
    public function onDownload(DownloadResourceEvent $event)
    {
        /** @var File $resource */
        $resource = $event->getResource();

        $event->setItem($this->filesDir.DIRECTORY_SEPARATOR.$resource->getHashName());
        $event->stopPropagation();
    }

    /**
     * Returns a new hash for a file.
     *
     * @param mixed $mixed The extension of the file or an Claroline\CoreBundle\Entity\Resource\File
     *
     * @return string
     */
    private function getHash($mixed)
    {
        if ($mixed instanceof File) {
            $mixed = pathinfo($mixed->getHashName(), PATHINFO_EXTENSION);
        }

        return Uuid::uuid4()->toString().'.'.$mixed;
    }

    /**
     * Copies a file (no persistence).
     *
     * @param File $resource
     *
     * @return File
     */
    private function copy(File $resource, File $file)
    {
        $ds = DIRECTORY_SEPARATOR;
        $hash = $this->getHash($resource);

        $file->setSize($resource->getSize());
        $file->setName($resource->getName());
        $file->setMimeType($resource->getMimeType());
        $file->setHashName($hash);
        copy($this->filesDir.$ds.$resource->getHashName(), $this->filesDir.$ds.$hash);

        return $file;
    }

    /**
     * Deletes recursively a directory and its content.
     *
     * @param string $dirPath The path to the directory to delete
     */
    private function deleteFiles($dirPath)
    {
        foreach (glob($dirPath.DIRECTORY_SEPARATOR.'{*,.[!.]*,..?*}', GLOB_BRACE) as $content) {
            if (is_dir($content)) {
                $this->deleteFiles($content);
            } else {
                unlink($content);
            }
        }
        rmdir($dirPath);
    }

    /**
     * Changes actual file associated to File resource.
     *
     * @DI\Observe("resource.claroline_web_resource.change_file")
     *
     * @param ResourceActionEvent $event
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
