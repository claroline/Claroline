<?php

namespace Claroline\ScormBundle\Component\Resource;

use Claroline\AppBundle\API\Serializer\SerializerInterface;
use Claroline\AppBundle\API\SerializerProvider;
use Claroline\AppBundle\API\Utils\FileBag;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Component\Resource\DownloadableResourceInterface;
use Claroline\CoreBundle\Component\Resource\FileAdapterInterface;
use Claroline\CoreBundle\Component\Resource\ResourceComponent;
use Claroline\CoreBundle\Entity\Resource\AbstractResource;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Manager\FileManager;
use Claroline\EvaluationBundle\Component\Resource\EvaluatedResourceInterface;
use Claroline\ScormBundle\Entity\Scorm;
use Claroline\ScormBundle\Manager\EvaluationManager;
use Claroline\ScormBundle\Manager\ScormManager;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

final class ScormResource extends ResourceComponent implements DownloadableResourceInterface, EvaluatedResourceInterface, FileAdapterInterface
{
    public function __construct(
        private readonly TokenStorageInterface $tokenStorage,
        private readonly ObjectManager $om,
        private readonly SerializerProvider $serializer,
        private readonly ScormManager $scormManager,
        private readonly EvaluationManager $evaluationManager,
        private readonly FileManager $fileManager,
        private readonly string $uploadDir
    ) {
    }

    public static function getName(): string
    {
        return 'claroline_scorm';
    }

    public static function getClass(): string
    {
        return Scorm::class;
    }

    public static function supportsScore(): bool
    {
        return true;
    }

    public static function supportsAttempts(): bool
    {
        return false;
    }

    /** @param Scorm $resource */
    public function open(AbstractResource $resource, bool $embedded = false): ?array
    {
        $evaluation = null;
        $tracking = [];

        /** @var User $user */
        $user = $this->tokenStorage->getToken()?->getUser();
        if ($user instanceof User) {
            // retrieve user progression
            $evaluation = $this->serializer->serialize(
                $this->evaluationManager->getResourceUserEvaluation($resource, $user),
                [SerializerInterface::SERIALIZE_MINIMAL]
            );

            // retrieve progression for each sco in the scorm
            $tracking = $this->evaluationManager->generateScosTrackings($resource->getRootScos(), $user);
        }

        return [
            'userEvaluation' => $evaluation,
            'trackings' => $tracking,
        ];
    }

    /** @param Scorm $resource */
    public function create(AbstractResource $resource, array $data): void
    {
        $uploaded = $this->scormManager->uploadScormArchive($resource->getResourceNode()->getWorkspace(), new File($resource->getUrl()));
        $resource->setUrl($uploaded['url']);

        $this->om->persist($resource);
        $this->om->flush();
    }

    /** @param Scorm $resource */
    public function update(AbstractResource $resource, array $data, array $previousData): ?array
    {
        if (!empty($resource->getUrl()) && ($previousData['resource']['url'] !== $resource->getUrl())) {
            $uploaded = $this->scormManager->uploadScormArchive($resource->getResourceNode()->getWorkspace(), new File($resource->getUrl()));
            $resource->setUrl($uploaded['url']);

            $this->serializer->deserialize($uploaded, $resource);

            $this->om->persist($resource);
            $this->om->flush();

            if (!empty($previousData['resource']['url'])) {
                // check if the file is reused between resources
                $countUsages = $this->om->getRepository(Scorm::class)->count(['hashName' => $previousData['resource']['url']]);
                if (0 === $countUsages) {
                    $filePath = 'scorm'.DIRECTORY_SEPARATOR.$resource->getResourceNode()->getWorkspace()->getUuid().DIRECTORY_SEPARATOR.$previousData['resource']['url'];

                    // file is not used anymore, we can delete if from the filesystem
                    $scormArchiveFile = $this->fileManager->getDirectory().DIRECTORY_SEPARATOR.$filePath;
                    if (file_exists($scormArchiveFile)) {
                        $this->fileManager->remove($scormArchiveFile, true);
                    }

                    $scormResourcesPath = $this->uploadDir.DIRECTORY_SEPARATOR.$filePath;
                    if (file_exists($scormResourcesPath)) {
                        $this->fileManager->remove($scormResourcesPath, true);
                    }
                }
            }
        }

        return [];
    }

    /** @param Scorm $resource */
    public function download(AbstractResource $resource, FileBag $fileBag): void
    {
        $filePath = $this->getScormArchive($resource);

        if ($filePath && $this->fileManager->exists($filePath, true)) {
            $fileBag->add($resource->getName(), $filePath);
        }
    }

    /** @param Scorm $resource */
    public function delete(AbstractResource $resource, FileBag $fileBag, bool $softDelete = true): bool
    {
        if ($softDelete) {
            return true;
        }

        $hashName = $resource->getUrl();

        $countUsages = $this->om->getRepository(Scorm::class)->count(['hashName' => $resource->getUrl()]);
        if (1 === $countUsages) {
            $workspace = $resource->getResourceNode()->getWorkspace();

            $scormArchiveFile = $this->fileManager->getDirectory().DIRECTORY_SEPARATOR.'scorm'.DIRECTORY_SEPARATOR.$workspace->getUuid().DIRECTORY_SEPARATOR.$hashName;
            if (file_exists($scormArchiveFile)) {
                $fileBag->add($hashName.'-archive', $scormArchiveFile);
            }

            $scormResourcesPath = $this->uploadDir.DIRECTORY_SEPARATOR.'scorm'.DIRECTORY_SEPARATOR.$workspace->getUuid().DIRECTORY_SEPARATOR.$hashName;
            if (file_exists($scormResourcesPath)) {
                $fileBag->add($hashName, $scormResourcesPath);
            }
        }

        return true;
    }

    /** @param Scorm $resource */
    public function export(AbstractResource $resource, FileBag $fileBag): ?array
    {
        // get the file path
        $archivePath = $this->getScormArchive($resource);
        if ($archivePath && $this->fileManager->exists($archivePath, true)) {
            $fileBag->add($resource->getUrl(), $archivePath);
        }

        return [];
    }

    /** @param Scorm $resource */
    public function import(AbstractResource $resource, FileBag $fileBag, array $data = []): void
    {
        $resourceNode = $resource->getResourceNode();
        $filePath = $fileBag->get($resource->getUrl());
        if (!file_exists($filePath)) {
            return;
        }

        try {
            $file = new File($fileBag->get($resource->getUrl()));
            $this->scormManager->unzipScormArchive($resourceNode->getWorkspace(), $file, $resource->getUrl());
        } catch (\Exception $e) {
            // scorm was invalid.
        }
    }

    /**
     * @param Scorm $original
     * @param Scorm $copy
     */
    public function copy(AbstractResource $original, AbstractResource $copy): void
    {
        $this->scormManager->copy($original, $copy->getResourceNode()->getWorkspace());
    }

    private function getScormArchive(Scorm $scorm): ?string
    {
        $workspace = $scorm->getResourceNode()->getWorkspace();
        $ds = DIRECTORY_SEPARATOR;
        $supposedArchiveLocation = $this->fileManager->getDirectory().$ds.'scorm'.$ds.$workspace->getUuid().$ds.$scorm->getUrl();

        if (is_file($supposedArchiveLocation)) {
            return $supposedArchiveLocation;
        }

        $uploadArchiveLocation = $this->uploadDir.$ds.'scorm'.$ds.$workspace->getUuid().$ds.$scorm->getUrl();
        if (!file_exists($uploadArchiveLocation)) {
            return null;
        }

        if (!is_dir($this->fileManager->getDirectory().$ds.'scorm'.$ds.$workspace->getUuid())) {
            mkdir($this->fileManager->getDirectory().$ds.'scorm'.$ds.$workspace->getUuid());
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
                $rel = $this->getRelativePath($filePath, $scorm->getUrl(), $workspace->getUuid());
                $zip->addFile($filePath, $rel);
            }
        }

        $zip->close();

        return $supposedArchiveLocation;
    }

    public function supportsFile(File $file): int
    {
        // Checks if it is a valid scorm archive
        $zip = new \ZipArchive();
        $openValue = $zip->open($file);

        if (true === $openValue && $zip->getStream('imsmanifest.xml')) {
            return FileAdapterInterface::SUPPORTED;
        }

        return FileAdapterInterface::UNSUPPORTED;
    }

    public function fromFile(File $file): ?array
    {
        return $this->scormManager->parseScormArchive($file);
    }

    /**
     * Gets the relative path between 2 instances (not optimized yet).
     */
    private function getRelativePath(string $current, string $hashName, string $wuid): string
    {
        return substr($current, strlen(realpath($this->uploadDir).'/scorm/'.$wuid.'/'.$hashName.'/'));
    }

    public function requireAdapter(): bool
    {
        return true;
    }
}
