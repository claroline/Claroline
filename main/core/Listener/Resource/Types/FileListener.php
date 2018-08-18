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
use Claroline\AppBundle\Event\StrictDispatcher;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Resource\AbstractResourceEvaluation;
use Claroline\CoreBundle\Entity\Resource\Directory;
use Claroline\CoreBundle\Entity\Resource\File;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Event\GenericDataEvent;
use Claroline\CoreBundle\Event\Resource\CopyResourceEvent;
use Claroline\CoreBundle\Event\Resource\DeleteResourceEvent;
use Claroline\CoreBundle\Event\Resource\DownloadResourceEvent;
use Claroline\CoreBundle\Event\Resource\File\LoadFileEvent;
use Claroline\CoreBundle\Event\Resource\LoadResourceEvent;
use Claroline\CoreBundle\Library\Utilities\MimeTypeGuesser;
use Claroline\CoreBundle\Manager\Resource\ResourceEvaluationManager;
use Claroline\CoreBundle\Manager\ResourceManager;
use JMS\DiExtraBundle\Annotation as DI;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Integrates the File resource into Claroline.
 *
 * @DI\Service
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

    /** @var string */
    private $filesDir;

    /** @var MimeTypeGuesser */
    private $mimeTypeGuesser;

    /** @var SerializerProvider */
    private $serializer;

    /** @var ResourceEvaluationManager */
    private $resourceEvalManager;

    /**
     * FileListener constructor.
     *
     * @DI\InjectParams({
     *     "tokenStorage"        = @DI\Inject("security.token_storage"),
     *     "om"                  = @DI\Inject("claroline.persistence.object_manager"),
     *     "eventDispatcher"     = @DI\Inject("claroline.event.event_dispatcher"),
     *     "filesDir"            = @DI\Inject("%claroline.param.files_directory%"),
     *     "mimeTypeGuesser"     = @DI\Inject("claroline.utilities.mime_type_guesser"),
     *     "serializer"          = @DI\Inject("claroline.api.serializer"),
     *     "resourceManager"     = @DI\Inject("claroline.manager.resource_manager"),
     *     "resourceEvalManager" = @DI\Inject("claroline.manager.resource_evaluation_manager")
     * })
     *
     * @param TokenStorageInterface     $tokenStorage
     * @param ObjectManager             $om
     * @param StrictDispatcher          $eventDispatcher
     * @param string                    $filesDir
     * @param MimeTypeGuesser           $mimeTypeGuesser
     * @param SerializerProvider        $serializer
     * @param ResourceManager           $resourceManager
     * @param ResourceEvaluationManager $resourceEvalManager
     */
    public function __construct(
        TokenStorageInterface $tokenStorage,
        ObjectManager $om,
        StrictDispatcher $eventDispatcher,
        $filesDir,
        MimeTypeGuesser $mimeTypeGuesser,
        SerializerProvider $serializer,
        ResourceManager $resourceManager,
        ResourceEvaluationManager $resourceEvalManager)
    {
        $this->tokenStorage = $tokenStorage;
        $this->om = $om;
        $this->eventDispatcher = $eventDispatcher;
        $this->filesDir = $filesDir;
        $this->mimeTypeGuesser = $mimeTypeGuesser;
        $this->serializer = $serializer;
        $this->resourceManager = $resourceManager;
        $this->resourceEvalManager = $resourceEvalManager;
    }

    /**
     * @DI\Observe("resource.file.load")
     *
     * @param LoadResourceEvent $event
     */
    public function onLoad(LoadResourceEvent $event)
    {
        /** @var File $resource */
        $resource = $event->getResource();
        $path = $this->filesDir.DIRECTORY_SEPARATOR.$resource->getHashName();

        $additionalFileData = [];

        /** @var LoadFileEvent $loadEvent */
        $loadEvent = $this->eventDispatcher->dispatch(
            $this->generateEventName($resource->getResourceNode(), 'load'),
            LoadFileEvent::class,
            [$resource, $path]
        );

        if ($event->isPopulated()) {
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
     * @DI\Observe("delete_file")
     *
     * @param DeleteResourceEvent $event
     */
    public function onDelete(DeleteResourceEvent $event)
    {
        /** @var File $file */
        $file = $event->getResource();

        $pathName = $this->filesDir.DIRECTORY_SEPARATOR.$file->getHashName();

        if (file_exists($pathName)) {
            $event->setFiles([$pathName]);
        }

        $event->stopPropagation();
    }

    /**
     * @DI\Observe("copy_file")
     *
     * @param CopyResourceEvent $event
     */
    public function onCopy(CopyResourceEvent $event)
    {
        /** @var File $file */
        $file = $event->getResource();

        $newFile = $this->copy($file, $event->getParent());
        $event->setCopy($newFile);

        $event->stopPropagation();
    }

    /**
     * @DI\Observe("download_file")
     *
     * @param DownloadResourceEvent $event
     */
    public function onDownload(DownloadResourceEvent $event)
    {
        /** @var File $file */
        $file = $event->getResource();

        $event->setItem(
            $this->filesDir.DIRECTORY_SEPARATOR.$file->getHashName()
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

    /**
     * Copies a file (no persistence).
     *
     * @param File         $resource
     * @param ResourceNode $destParent
     *
     * @return File
     */
    private function copy(File $resource, ResourceNode $destParent)
    {
        $workspace = $destParent->getWorkspace();
        $newFile = new File();
        $newFile->setSize($resource->getSize());
        $newFile->setName($resource->getName());
        $newFile->setMimeType($resource->getMimeType());
        $hashName = join('.', [
            'WORKSPACE_'.$workspace->getId(),
            Uuid::uuid4()->toString(),
            pathinfo($resource->getHashName(), PATHINFO_EXTENSION),
        ]);
        $newFile->setHashName($hashName);
        $filePath = $this->filesDir.DIRECTORY_SEPARATOR.$resource->getHashName();
        $newPath = $this->filesDir.DIRECTORY_SEPARATOR.$hashName;
        $workspaceDir = $this->filesDir.DIRECTORY_SEPARATOR.'WORKSPACE_'.$workspace->getId();

        if (!is_dir($workspaceDir)) {
            mkdir($workspaceDir);
        }

        try {
            copy($filePath, $newPath);
        } catch (\Exception $e) {
            //do nothing yet
            //maybe log an error
        }

        return $newFile;
    }

    /**
     * @DI\Observe("generate_resource_user_evaluation_file")
     *
     * @param GenericDataEvent $event
     */
    public function onGenerateResourceTracking(GenericDataEvent $event)
    {
        $data = $event->getData();
        $node = $data['resourceNode'];
        $user = $data['user'];
        $startDate = $data['startDate'];

        $logs = $this->resourceEvalManager->getLogsForResourceTracking(
            $node,
            $user,
            ['resource-read'],
            $startDate
        );
        $nbLogs = count($logs);

        if ($nbLogs > 0) {
            $this->om->startFlushSuite();
            $tracking = $this->resourceEvalManager->getResourceUserEvaluation($node, $user);
            $tracking->setDate($logs[0]->getDateLog());
            $tracking->setStatus(AbstractResourceEvaluation::STATUS_OPENED);
            $tracking->setNbOpenings($nbLogs);
            $this->om->persist($tracking);
            $this->om->endFlushSuite();
        }
        $event->stopPropagation();
    }

    private function uploadDir(
        $dir,
        ResourceNode $parent,
        array $perms,
        $first = false,
        $published = true
    ) {
        $resources = [];
        $iterator = new \DirectoryIterator($dir);

        foreach ($iterator as $item) {
            if ($item->isFile()) {
                $resources[] = $this->uploadFile($item, $parent, $perms, $published);
            }

            if ($item->isDir() && !$item->isDot()) {
                //create new dir
                $directory = new Directory();
                $directory->setName($item->getBasename());
                $resources[] = $this->resourceManager->create(
                    $directory,
                    $this->resourceManager->getResourceTypeByName('directory'),
                    $this->tokenStorage->getToken()->getUser(),
                    $parent->getWorkspace(),
                    $parent,
                    null,
                    $perms,
                    $published
                );

                $this->uploadDir(
                    $dir.DIRECTORY_SEPARATOR.$item->getBasename(),
                    $directory->getResourceNode(),
                    $perms,
                    false,
                    $published
                );
            }
        }

        // set order manually as we are inside a flush suite
        for ($i = 0, $count = count($resources); $i < $count; ++$i) {
            $resources[$i]->getResourceNode()->setIndex($i + 1);
        }

        return $resources;
    }

    private function uploadFile(
        \DirectoryIterator $file,
        ResourceNode $parent,
        array $perms,
        $published = true
    ) {
        $workspaceId = $parent->getWorkspace()->getId();
        $entityFile = new File();
        $fileName = $file->getFilename();
        $size = @filesize($file);
        $extension = pathinfo($fileName, PATHINFO_EXTENSION);

        // why don't we retrieve it directly from the file info .
        $mimeType = $this->mimeTypeGuesser->guess($extension);

        $hashName = join('.', [
            'WORKSPACE_'.$workspaceId,
            Uuid::uuid4()->toString(),
            $extension,
        ]);

        $destination = $this->filesDir.DIRECTORY_SEPARATOR.$hashName;
        copy($file->getPathname(), $destination);
        $entityFile->setSize($size);
        $entityFile->setName($fileName);
        $entityFile->setHashName($hashName);
        $entityFile->setMimeType($mimeType);

        return $this->resourceManager->create(
            $entityFile,
            $this->resourceManager->getResourceTypeByName('file'),
            $this->tokenStorage->getToken()->getUser(),
            $parent->getWorkspace(),
            $parent,
            null,
            $perms,
            $published
        );
    }
}
