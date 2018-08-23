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

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Resource\File;
use Claroline\CoreBundle\Event\Resource\CopyResourceEvent;
use Claroline\CoreBundle\Event\Resource\DeleteResourceEvent;
use Claroline\CoreBundle\Event\Resource\DownloadResourceEvent;
use Claroline\CoreBundle\Event\Resource\LoadResourceEvent;
use Claroline\WebResourceBundle\Manager\WebResourceManager;
use JMS\DiExtraBundle\Annotation as DI;
use Ramsey\Uuid\Uuid;

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

    /**
     * WebResourceListener constructor.
     *
     * @DI\InjectParams({
     *     "filesDir"           = @DI\Inject("%claroline.param.files_directory%"),
     *     "om"                 = @DI\Inject("claroline.persistence.object_manager"),
     *     "uploadDir"          = @DI\Inject("%claroline.param.uploads_directory%"),
     *     "webResourceManager" = @DI\Inject("claroline.manager.web_resource_manager")
     * })
     *
     * @param string             $filesDir
     * @param ObjectManager      $om
     * @param string             $uploadDir
     * @param WebResourceManager $webResourceManager
     */
    public function __construct(
        $filesDir,
        ObjectManager $om,
        $uploadDir,
        WebResourceManager $webResourceManager
    ) {
        $this->filesDir = $filesDir;
        $this->om = $om;
        $this->uploadDir = $uploadDir;
        $this->webResourceManager = $webResourceManager;
    }

    /**
     * @DI\Observe("resource.claroline_web_resource.load")
     *
     * @param LoadResourceEvent $event
     */
    public function onLoad(LoadResourceEvent $event)
    {
        $ds = DIRECTORY_SEPARATOR;
        $hash = $event->getResource()->getHashName();
        $workspace = $event->getResource()->getResourceNode()->getWorkspace();
        $unzippedPath = $this->uploadDir.$ds.'webresource'.$ds.$workspace->getUuid();
        $srcPath = 'uploads'.$ds.'webresource'.$ds.$workspace->getUuid().$ds.$hash;
        $event->setData([
          'path' => $srcPath.$ds.$this->webResourceManager->guessRootFileFromUnzipped($unzippedPath.$ds.$hash),
        ]);

        $event->stopPropagation();
    }

    /**
     * @DI\Observe("delete_claroline_web_resource")
     *
     * @param DeleteResourceEvent $event
     */
    public function onDelete(DeleteResourceEvent $event)
    {
        $ds = DIRECTORY_SEPARATOR;
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
     * @DI\Observe("copy_claroline_web_resource")
     *
     * @param CopyResourceEvent $event
     */
    public function onCopy(CopyResourceEvent $event)
    {
        $file = $this->copy($event->getResource());
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
        $event->setItem($this->filesDir.DIRECTORY_SEPARATOR.$event->getResource()->getHashName());
        $event->stopPropagation();
    }

    /**
     * Returns a new hash for a file.
     *
     * @param mixed mixed The extension of the file or an Claroline\CoreBundle\Entity\Resource\File
     *
     * @return string
     */
    private function getHash($mixed)
    {
        if ($mixed instanceof File) {
            $mixed = pathinfo($mixed->getHashName(), PATHINFO_EXTENSION);
        }

        return Uuid::uuid5()->toString().'.'.$mixed;
    }

    /**
     * Copies a file (no persistence).
     *
     * @param File $resource
     *
     * @return File
     */
    private function copy(File $resource)
    {
        $ds = DIRECTORY_SEPARATOR;
        $hash = $this->getHash($resource);

        $file = new File();
        $file->setSize($resource->getSize());
        $file->setName($resource->getName());
        $file->setMimeType($resource->getMimeType());
        $file->setHashName($hash);
        copy($this->filesDir.$ds.$resource->getHashName(), $this->filesDir.$ds.$hash);
        $this->getZip()->open($this->filesDir.$ds.$hash);
        $this->unzip($hash);

        return $file;
    }

    /**
     * Deletes recursively a directory and its content.
     *
     * @param $dirPath The path to the directory to delete
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
}
