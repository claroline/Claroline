<?php

namespace Claroline\CoreBundle\Component\Resource;

use Claroline\AppBundle\API\Utils\FileBag;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Resource\AbstractResource;
use Claroline\CoreBundle\Manager\FileManager;
use Claroline\CoreBundle\Validator\Exception\InvalidDataException;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;
use Symfony\Component\HttpFoundation\File\File;

/**
 * Helper to manage Resources based on files (aka implements {@see FileAdapterInterface}).
 */
trait FileAdapterTrait
{
    private readonly ObjectManager $om;
    private readonly FileManager $fileManager;

    abstract public static function getName(): string;

    abstract public static function getClass(): string;

    public function create(AbstractResource $resource, array $data): void
    {
        if (!empty($resource->getUrl())) {
            $this->saveFile($resource);
        }
    }

    public function update(AbstractResource $resource, array $data, array $previousData): ?array
    {
        if (!empty($resource->getUrl()) && ($previousData['resource']['url'] !== $resource->getUrl())) {
            $this->saveFile($resource);

            if (!empty($previousData['resource']['url'])) {
                // check if the file is reused between resources
                $countUsages = $this->om->getRepository(static::getClass())->count(['url' => $previousData['resource']['url']]);
                if (0 === $countUsages) {
                    // file is not used anymore, we can delete if from the filesystem
                    $this->fileManager->remove($previousData['resource']['url']);
                }
            }
        }

        return [];
    }

    public function download(AbstractResource $resource, FileBag $fileBag): void
    {
        if ($resource->getUrl() && $this->fileManager->exists($resource->getUrl())) {
            $filePath = $this->fileManager->getAbsolutePath($resource->getUrl());

            $ext = pathinfo($filePath, PATHINFO_EXTENSION);
            $fileBag->add($resource->getName().'.'.$ext, $filePath);
        }
    }

    public function stream(AbstractResource $resource): ?string
    {
        return file_get_contents($this->fileManager->getAbsolutePath($resource->getUrl()));
    }

    public function delete(AbstractResource $resource, FileBag $fileBag, bool $softDelete = true): bool
    {
        if (!$softDelete && $resource->getUrl() && $this->fileManager->exists($resource->getUrl())) {
            // check if the file is reused between resources
            $countUsages = $this->om->getRepository(static::getClass())->count(['url' => $resource->getUrl()]);
            if (1 === $countUsages) {
                $fileBag->add($resource->getUrl(), $this->fileManager->getAbsolutePath($resource->getUrl()));
            }
        }

        return true;
    }

    public function export(AbstractResource $resource, FileBag $fileBag): ?array
    {
        if ($resource->getUrl() && $this->fileManager->exists($resource->getUrl())) {
            $fileBag->add($resource->getUrl(), $this->fileManager->getAbsolutePath($resource->getUrl()));
        }

        return [];
    }

    public function import(AbstractResource $resource, FileBag $fileBag, array $data = []): void
    {
        $toCopy = $fileBag->get($resource->getUrl());
        if ($toCopy && file_exists($toCopy)) {
            $file = new File($toCopy);

            $filePath = static::getFilePath($resource, $file->guessExtension());
            $this->fileManager->copy($toCopy, $filePath);
            $resource->setUrl($filePath);

            $this->om->persist($resource);
        }
    }

    private static function getFilePath(AbstractResource $resource, string $extension): string
    {
        $resourceNode = $resource->getResourceNode();
        $workspace = $resourceNode->getWorkspace();
        $workspaceDir = 'WORKSPACE_'.$workspace->getId();

        return $workspaceDir.DIRECTORY_SEPARATOR.static::getName().DIRECTORY_SEPARATOR.Uuid::uuid4()->toString().'.'.$extension;
    }

    private function saveFile(AbstractResource $resource): void
    {
        try {
            $file = new File($resource->getUrl());
        } catch (FileNotFoundException $e) {
            throw new InvalidDataException(sprintf('Cannot find the file for "%s" resource.', static::getName()));
        }

        $finalPath = static::getFilePath($resource, $file->guessExtension());
        $this->fileManager->move($resource->getUrl(), $finalPath);
        $resource->setUrl($finalPath);

        $this->om->persist($resource);
        $this->om->flush();
    }
}
