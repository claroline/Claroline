<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\ScormBundle\Listener;

use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\SerializerProvider;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Event\Resource\CopyResourceEvent;
use Claroline\CoreBundle\Event\Resource\DeleteResourceEvent;
use Claroline\CoreBundle\Event\Resource\DownloadResourceEvent;
use Claroline\CoreBundle\Event\Resource\ExportResourceEvent;
use Claroline\CoreBundle\Event\Resource\ImportResourceEvent;
use Claroline\CoreBundle\Event\Resource\LoadResourceEvent;
use Claroline\CoreBundle\Event\Resource\ResourceActionEvent;
use Claroline\EvaluationBundle\Manager\ResourceEvaluationManager;
use Claroline\ScormBundle\Entity\Scorm;
use Claroline\ScormBundle\Entity\ScoTracking;
use Claroline\ScormBundle\Manager\ScormManager;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class ScormListener
{
    /** @var string */
    private $filesDir;
    /** @var Filesystem */
    private $fileSystem;
    /** @var ObjectManager */
    private $om;
    /** @var ResourceEvaluationManager */
    private $resourceEvalManager;
    /** @var ScormManager */
    private $scormManager;
    /** @var SerializerProvider */
    private $serializer;
    /** @var TokenStorageInterface */
    private $tokenStorage;
    /** @var string */
    private $uploadDir;

    private $scormResourceRepo;
    private $scoTrackingRepo;

    public function __construct(
        string $filesDir,
        Filesystem $fileSystem,
        ObjectManager $om,
        ResourceEvaluationManager $resourceEvalManager,
        ScormManager $scormManager,
        SerializerProvider $serializer,
        TokenStorageInterface $tokenStorage,
        string $uploadDir
    ) {
        $this->filesDir = $filesDir;
        $this->fileSystem = $fileSystem;
        $this->om = $om;
        $this->resourceEvalManager = $resourceEvalManager;
        $this->scormManager = $scormManager;
        $this->serializer = $serializer;
        $this->tokenStorage = $tokenStorage;
        $this->uploadDir = $uploadDir;

        $this->scormResourceRepo = $om->getRepository(Scorm::class);
        $this->scoTrackingRepo = $om->getRepository(ScoTracking::class);
    }

    public function onLoad(LoadResourceEvent $event)
    {
        $scorm = $event->getResource();
        $user = $this->tokenStorage->getToken()->getUser();
        if (!$user instanceof User) {
            $user = null;
        }

        $event->setData([
            'scorm' => $this->serializer->serialize($scorm),
            'userEvaluation' => is_null($user) ?
                null :
                $this->serializer->serialize(
                    $this->resourceEvalManager->getUserEvaluation($scorm->getResourceNode(), $user),
                    [Options::SERIALIZE_MINIMAL]
                ),
            'trackings' => $this->scormManager->generateScosTrackings($scorm->getRootScos(), $user),
        ]);
        $event->stopPropagation();
    }

    public function onDelete(DeleteResourceEvent $event)
    {
        $scorm = $event->getResource();
        $workspace = $scorm->getResourceNode()->getWorkspace();
        $hashName = $scorm->getHashName();

        $nbScorm = (int) $this->scormResourceRepo->findNbScormWithSameSource($hashName, $workspace);
        if (1 === $nbScorm) {
            $files = [];

            $scormArchiveFile = $this->filesDir.DIRECTORY_SEPARATOR.'scorm'.DIRECTORY_SEPARATOR.$workspace->getUuid().DIRECTORY_SEPARATOR.$hashName;
            if (file_exists($scormArchiveFile)) {
                $files[] = $scormArchiveFile;
            }

            $scormResourcesPath = $this->uploadDir.DIRECTORY_SEPARATOR.'scorm'.DIRECTORY_SEPARATOR.$workspace->getUuid().DIRECTORY_SEPARATOR.$hashName;
            if (file_exists($scormResourcesPath)) {
                $files[] = $scormResourcesPath;
            }

            $event->setFiles($files);
        }

        $event->stopPropagation();
    }

    public function onExport(ExportResourceEvent $event)
    {
        /** @var Scorm $scorm */
        $scorm = $event->getResource();

        // get the file path
        $event->addFile($scorm->getHashName(), $this->getScormArchive($scorm));
    }

    public function onImport(ImportResourceEvent $event)
    {
        /** @var Scorm $scorm */
        $scorm = $event->getResource();
        $resourceNde = $scorm->getResourceNode();
        $bag = $event->getFileBag();

        try {
            $file = new File($bag->get($scorm->getHashName()));
            $this->scormManager->unzipScormArchive($resourceNde->getWorkspace(), $file, $scorm->getHashName());
        } catch (\Exception $e) {
            // scorm was invalid.
        }
    }

    public function onCopy(CopyResourceEvent $event)
    {
        $resource = $event->getResource();
        $workspace = $resource->getResourceNode()->getWorkspace();
        $newWorkspace = $event->getCopy()->getResourceNode()->getWorkspace();

        $hashName = $resource->getHashName();

        if ($workspace->getId() !== $newWorkspace->getId()) {
            $ds = DIRECTORY_SEPARATOR;
            /* Copies archive file & unzipped files */
            if ($this->fileSystem->exists($this->filesDir.$ds.'scorm'.$ds.$workspace->getUuid().$ds.$hashName)) {
                $this->fileSystem->copy(
                    $this->filesDir.$ds.'scorm'.$ds.$workspace->getUuid().$ds.$hashName,
                    $this->filesDir.$ds.'scorm'.$ds.$newWorkspace->getUuid().$ds.$hashName
                );
            }
            if ($this->fileSystem->exists($this->uploadDir.$ds.'scorm'.$ds.$workspace->getUuid().$ds.$hashName)) {
                $this->fileSystem->mirror(
                    $this->uploadDir.$ds.'scorm'.$ds.$workspace->getUuid().$ds.$hashName,
                    $this->uploadDir.$ds.'scorm'.$ds.$newWorkspace->getUuid().$ds.$hashName
                );
            }
        }

        $event->stopPropagation();
    }

    public function onDownload(DownloadResourceEvent $event)
    {
        /** @var Scorm $scorm */
        $scorm = $event->getResource();

        $event->setItem($this->getScormArchive($scorm));
        $event->setExtension('zip');
        $event->stopPropagation();
    }

    public function onFileChange(ResourceActionEvent $event)
    {
        /** @var ResourceNode $node */
        $node = $event->getResourceNode();
        /** @var Scorm $scorm */
        $scorm = $event->getResource();

        $parameters = $event->getData();
        $filePath = $parameters['file']['url'];

        if (!empty($filePath)) {
            $data = $this->scormManager->uploadScormArchive($node->getWorkspace(), new File($this->filesDir.DIRECTORY_SEPARATOR.$filePath));
            if ($data) {
                $oldFile = $scorm->getHashName();

                // update scorm
                $scorm = $this->serializer->deserialize($data, $scorm);

                // remove old zip
                unlink($this->filesDir.DIRECTORY_SEPARATOR.'scorm'.DIRECTORY_SEPARATOR.$node->getWorkspace()->getUuid().DIRECTORY_SEPARATOR.$oldFile);
                // remove old unzipped scorm
                $this->deleteFiles($this->uploadDir.DIRECTORY_SEPARATOR.'scorm'.DIRECTORY_SEPARATOR.$node->getWorkspace()->getUuid().DIRECTORY_SEPARATOR.$oldFile);

                $this->om->persist($scorm);
                $this->om->flush();
            }
        }

        $event->setResponse(new JsonResponse($this->serializer->serialize($node)));
    }

    private function getScormArchive(Scorm $scorm)
    {
        $workspace = $scorm->getResourceNode()->getWorkspace();
        $ds = DIRECTORY_SEPARATOR;
        $supposedArchiveLocation = $this->filesDir.$ds.'scorm'.$ds.$workspace->getUuid().$ds.$scorm->getHashName();

        if (is_file($supposedArchiveLocation)) {
            return $supposedArchiveLocation;
        }

        $uploadArchiveLocation = $this->uploadDir.$ds.'scorm'.$ds.$workspace->getUuid().$ds.$scorm->getHashName();

        if (!is_dir($this->filesDir.$ds.'scorm'.$ds.$workspace->getUuid())) {
            mkdir($this->filesDir.$ds.'scorm'.$ds.$workspace->getUuid());
        }
        // initialize the ZIP archive
        $zip = new \ZipArchive();
        $zip->open($supposedArchiveLocation, \ZipArchive::CREATE);

        // create recursive directory iterator
        $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($uploadArchiveLocation), \RecursiveIteratorIterator::LEAVES_ONLY);

        // let's iterate
        foreach ($files as $file) {
            $filePath = $file->getRealPath();

            if (file_exists($filePath) && is_file($filePath)) {
                $rel = $this->getRelativePath($filePath, $scorm->getHashName(), $workspace->getUuid());
                $zip->addFile($filePath, $rel);
            }
        }

        $zip->close();

        return $supposedArchiveLocation;
    }

    /**
     * Gets the relative path between 2 instances (not optimized yet).
     */
    private function getRelativePath($current, $hashName, $wuid): string
    {
        return substr($current, strlen(realpath($this->uploadDir).'/scorm/'.$wuid.'/'.$hashName.'/'));
    }

    /**
     * Deletes recursively a directory and its content.
     */
    private function deleteFiles(string $dirPath = '')
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
