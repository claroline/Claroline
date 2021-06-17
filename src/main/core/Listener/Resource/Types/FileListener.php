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
use Claroline\CoreBundle\Entity\Resource\File;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Event\ExportObjectEvent;
use Claroline\CoreBundle\Event\GenericDataEvent;
use Claroline\CoreBundle\Event\ImportObjectEvent;
use Claroline\CoreBundle\Event\Resource\CopyResourceEvent;
use Claroline\CoreBundle\Event\Resource\DeleteResourceEvent;
use Claroline\CoreBundle\Event\Resource\DownloadResourceEvent;
use Claroline\CoreBundle\Event\Resource\File\LoadFileEvent;
use Claroline\CoreBundle\Event\Resource\LoadResourceEvent;
use Claroline\CoreBundle\Event\Resource\ResourceActionEvent;
use Claroline\CoreBundle\Library\Utilities\FileUtilities;
use Claroline\CoreBundle\Manager\Resource\ResourceEvaluationManager;
use Claroline\CoreBundle\Manager\ResourceManager;
use Claroline\EvaluationBundle\Entity\Evaluation\AbstractEvaluation;
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

    /** @var string */
    private $filesDir;

    /** @var SerializerProvider */
    private $serializer;

    /** @var ResourceEvaluationManager */
    private $resourceEvalManager;

    /** @var FileUtilities */
    private $fileUtils;

    public function __construct(
        TokenStorageInterface $tokenStorage,
        ObjectManager $om,
        StrictDispatcher $eventDispatcher,
        string $filesDir,
        SerializerProvider $serializer,
        ResourceManager $resourceManager,
        ResourceEvaluationManager $resourceEvalManager,
        FileUtilities $fileUtils
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->om = $om;
        $this->eventDispatcher = $eventDispatcher;
        $this->filesDir = $filesDir;
        $this->serializer = $serializer;
        $this->resourceManager = $resourceManager;
        $this->resourceEvalManager = $resourceEvalManager;
        $this->fileUtils = $fileUtils;
    }

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

        $pathName = $this->filesDir.DIRECTORY_SEPARATOR.$file->getHashName();

        if (file_exists($pathName)) {
            $event->setFiles([$pathName]);
        }

        $event->stopPropagation();
    }

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

    public function onExportFile(ExportObjectEvent $exportEvent)
    {
        $file = $exportEvent->getObject();
        $path = $this->filesDir.DIRECTORY_SEPARATOR.$file->getHashName();
        $file = $exportEvent->getObject();
        $newPath = uniqid().'.'.pathinfo($file->getHashName(), PATHINFO_EXTENSION);
        //get the filePath
        $exportEvent->addFile($newPath, $path);
        $exportEvent->overwrite('_path', $newPath);
    }

    public function onImportFile(ImportObjectEvent $event)
    {
        $data = $event->getData();
        $bag = $event->getFileBag();
        if ($bag) {
            $fileSystem = new Filesystem();
            try {
                $ds = DIRECTORY_SEPARATOR;
                $fileSystem->copy($bag->get($data['_path']), $this->filesDir.$ds.$data['hashName']);
            } catch (\Exception $e) {
            }
        }
        //move filebags elements here
    }

    public function onCopy(CopyResourceEvent $event)
    {
        /** @var File $file */
        $resource = $event->getResource();
        $destParent = $event->getParent();
        $workspace = $destParent->getWorkspace();
        $newFile = $event->getCopy();
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

        $event->setCopy($newFile);

        $event->stopPropagation();
    }

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
            $tracking->setStatus(AbstractEvaluation::STATUS_OPENED);
            $tracking->setNbOpenings($nbLogs);
            $this->om->persist($tracking);
            $this->om->endFlushSuite();
        }
        $event->stopPropagation();
    }
}
