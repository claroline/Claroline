<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Manager;

use Claroline\AppBundle\API\Crud;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\File\PublicFile;
use Claroline\CoreBundle\Entity\File\PublicFileUse;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\File\File;

class FileManager
{
    const MAX_FILES = 1000;

    /** @var string */
    private $fileDir;
    /** @var string */
    private $publicFileDir;

    /** @var Filesystem */
    private $filesystem;
    /** @var PlatformConfigurationHandler */
    private $config;
    /** @var ObjectManager */
    private $om;
    /** @var Crud */
    private $crud;

    public function __construct(
        string $fileDir,
        string $publicFileDir,
        Filesystem $filesystem,
        PlatformConfigurationHandler $config,
        ObjectManager $om,
        Crud $crud
    ) {
        $this->fileDir = $fileDir;
        $this->publicFileDir = $publicFileDir;
        $this->filesystem = $filesystem;
        $this->config = $config;
        $this->om = $om;
        $this->crud = $crud;
    }

    /**
     * Get the path to the files directory.
     */
    public function getDirectory(): string
    {
        return $this->fileDir;
    }

    /**
     * Get the path to the public files directory.
     */
    public function getPublicDirectory(): string
    {
        return $this->publicFileDir;
    }

    public function isStorageFull(): bool
    {
        // TODO : enable when our storage management is fixed
        return false
            && $this->config->getParameter('restrictions.storage')
            && $this->config->getParameter('restrictions.used_storage')
            && $this->config->getParameter('restrictions.used_storage') >= $this->config->getParameter('restrictions.storage');
    }

    /**
     * Computes the size of the files directory in bytes.
     */
    public function computeUsedStorage(): int
    {
        $filesDirSize = 0;

        foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($this->fileDir)) as $file) {
            if ('.' !== $file->getFilename() && '..' !== $file->getFilename()) {
                $filesDirSize += $file->getSize();
            }
        }

        return $filesDirSize;
    }

    /**
     * Dumps used storage in platform_options for performances (it's heavy to scan the whole files directory to get the files sizes).
     */
    public function updateUsedStorage(int $usedStorage): void
    {
        $this->config->setParameter('restrictions.used_storage', $usedStorage);
    }

    public function getPath(PublicFile $file): string
    {
        return $this->fileDir.DIRECTORY_SEPARATOR.$file->getUrl();
    }

    public function getContents(PublicFile $file): ?string
    {
        $content = file_get_contents($this->getPath($file));
        if ($content) {
            // remove BOM if any
            $bom = pack('H*', 'EFBBBF');
            $content = preg_replace("/^$bom/", '', $content);

            return $content;
        }

        return null;
    }

    /**
     * Creates a file into public files directory.
     * Then creates a <PublicFileUse> for created public file if $objectClass and $objectUuid are specified.
     */
    public function createFile(
        File $tmpFile,
        string $name = null,
        string $objectClass = null,
        string $objectUuid = null,
        string $objectName = null,
        string $sourceType = null
    ): PublicFile {
        $fileName = $name ? $name : $tmpFile->getFilename();
        $directoryName = $this->getActiveDirectoryName();
        $size = filesize($tmpFile);
        $mimeType = $tmpFile->getMimeType();
        $hashName = Uuid::uuid4()->toString().'.'.$tmpFile->guessExtension();
        $prefix = 'data'.DIRECTORY_SEPARATOR.$directoryName;
        $url = $prefix.DIRECTORY_SEPARATOR.$hashName;

        $this->om->startFlushSuite();
        $publicFile = new PublicFile();
        $publicFile->setDirectoryName($directoryName);
        $publicFile->setFilename($fileName);
        $publicFile->setSize($size);
        $publicFile->setMimeType($mimeType);
        $publicFile->setCreationDate(new \DateTime());
        $publicFile->setUrl($url);
        $publicFile->setSourceType($sourceType);

        $tmpFile->move($this->fileDir.DIRECTORY_SEPARATOR.$prefix, $hashName);
        $this->om->persist($publicFile);

        if (!is_null($objectClass) && !is_null($objectUuid)) {
            $this->createFileUse($publicFile, $objectClass, $objectUuid, $objectName);
        }
        $this->om->endFlushSuite();

        return $publicFile;
    }

    /**
     * Creates a file from given data into public files directory.
     * Then creates a <PublicFileUse> for created public file if $objectClass and $objectUuid are specified.
     *
     * @deprecated only used by quiz GraphicQuestion
     */
    public function createFileFromData(
        string $data,
        string $fileName,
        string $objectClass = null,
        string $objectUuid = null,
        string $objectName = null,
        string $sourceType = null
    ): PublicFile {
        $directoryName = $this->getActiveDirectoryName();
        $dataParts = explode(',', $data);
        $dataBin = base64_decode($dataParts[1]);
        $length = strlen($dataBin);
        $size = ceil(4 * $length / 3);
        $matches = [];
        $extension = '';
        if (1 === preg_match('#^.+\.([^\.]+)$#', $fileName, $matches)) {
            if (isset($matches[1])) {
                $extension = $matches[1];
            }
        }
        $hashName = Uuid::uuid4()->toString();
        if (!empty($extension)) {
            $hashName .= '.'.$extension;
        }
        $prefix = 'data'.DIRECTORY_SEPARATOR.$directoryName;
        $url = $prefix.DIRECTORY_SEPARATOR.$hashName;

        $this->om->startFlushSuite();
        $publicFile = new PublicFile();
        $publicFile->setDirectoryName($directoryName);
        $publicFile->setFilename($fileName);
        $publicFile->setSize($size);
        $publicFile->setCreationDate(new \DateTime());
        $publicFile->setUrl($url);
        $publicFile->setSourceType($sourceType);

        $this->filesystem->dumpFile($url, $dataBin);
        $mimeType = mime_content_type($url);
        $publicFile->setMimeType($mimeType);
        $this->om->persist($publicFile);

        if (!is_null($objectClass) && !is_null($objectUuid)) {
            $this->createFileUse($publicFile, $objectClass, $objectUuid, $objectName);
        }
        $this->om->endFlushSuite();

        return $publicFile;
    }

    public function updateFile(string $linkedClass, string $linkedId, ?string $fileUrl = null, ?string $oldFileUrl = null)
    {
        if (empty($fileUrl) && empty($oldFileUrl)) {
            return;
        }

        if ($fileUrl === $oldFileUrl) {
            return;
        }

        if (!empty($oldFileUrl)) {
            $this->unlinkFile($linkedClass, $linkedId, $oldFileUrl);
        }

        if (!empty($fileUrl)) {
            $this->linkFile($linkedClass, $linkedId, $fileUrl);
        }
    }

    public function linkFile(string $linkedClass, string $linkedId, string $fileUrl)
    {
        $file = $this->om->getRepository(PublicFile::class)->findOneBy(['url' => $fileUrl]);
        if ($file) {
            $this->createFileUse($file, $linkedClass, $linkedId);
        }
    }

    public function unlinkFile(string $linkedClass, string $linkedId, string $fileUrl)
    {
        $file = $this->om->getRepository(PublicFile::class)->findOneBy(['url' => $fileUrl]);
        if (!$file) {
            return;
        }

        $count = $this->om->getRepository(PublicFileUse::class)->count([
            'publicFile' => $file,
        ]);

        $publicFileUse = $this->om->getRepository(PublicFileUse::class)->findOneBy([
            'publicFile' => $file,
            'objectClass' => $linkedClass,
            'id' => $linkedId,
        ]);

        $this->om->startFlushSuite();
        if ($publicFileUse) {
            $this->om->remove($publicFileUse);
        }

        if (0 === $count || (1 === $count && $publicFileUse)) {
            // the current object is the only user of the file, we can remove it now
            $this->crud->delete($file);
        }

        $this->om->endFlushSuite();
    }

    public function exists(string $filePath, bool $isAbsolutePath = false): bool
    {
        return $this->filesystem->exists(
            !$isAbsolutePath ? $this->getDirectory().DIRECTORY_SEPARATOR.$filePath : $filePath
        );
    }

    public function remove(string $filePath, bool $isAbsolutePath = false)
    {
        $path = !$isAbsolutePath ? $this->getDirectory().DIRECTORY_SEPARATOR.$filePath : $filePath;
        if ($this->filesystem->exists($path)) {
            $this->filesystem->remove($path);
        }
    }

    public function getActiveDirectoryName()
    {
        $finder = new Finder();
        $finder->depth('== 0');
        $finder->directories()->in($this->publicFileDir)->name('/^[a-zA-Z]{20}$/');
        $finder->sortByName();
        if (0 === $finder->count()) {
            $activeDirectoryName = $this->generateNextDirectoryName();
        } else {
            $i = 0;
            $cnt = $finder->count();
            foreach ($finder as $dir) {
                ++$i;
                if ($i === $cnt) {
                    $subFinder = new Finder();
                    $subFinder->in($dir->getRealPath());
                    $dirName = $dir->getFilename();

                    if ($subFinder->count() >= self::MAX_FILES) {
                        $activeDirectoryName = $this->generateNextDirectoryName($dirName);
                    } else {
                        $activeDirectoryName = $dirName;
                    }
                }
            }
        }

        $newDir = $this->publicFileDir.DIRECTORY_SEPARATOR.$activeDirectoryName;

        if (!$this->filesystem->exists($newDir)) {
            $this->filesystem->mkdir($newDir);
        }

        return $activeDirectoryName;
    }

    private function generateNextDirectoryName($name = null)
    {
        if (is_null($name)) {
            $next = 'aaaaaaaaaaaaaaaaaaaa';
        } elseif ('zzzzzzzzzzzzzzzzzzzz' === strtolower($name)) {
            $next = $name;
        } else {
            $next = ++$name;
        }
        $newDir = $this->publicFileDir.DIRECTORY_SEPARATOR.$next;

        if (!$this->filesystem->exists($newDir)) {
            $this->filesystem->mkdir($newDir);
        }

        return $next;
    }

    private function createFileUse(PublicFile $publicFile, $class, $uuid, $name = null)
    {
        $cleanClass = str_replace('Proxies\\__CG__\\', '', $class);
        $repo = $this->om->getRepository(PublicFileUse::class);
        $publicFileUse = $repo->findOneBy(['publicFile' => $publicFile, 'objectClass' => $class, 'id' => $uuid]);

        if (!$publicFileUse) {
            $publicFileUse = new PublicFileUse();
            $publicFileUse->setPublicFile($publicFile);
            $publicFileUse->setObjectClass($cleanClass);
            $publicFileUse->setObjectUuid($uuid);
            $publicFileUse->setObjectName($name);
            $this->om->persist($publicFileUse);
            $this->om->flush();
        }

        return $publicFileUse;
    }
}
