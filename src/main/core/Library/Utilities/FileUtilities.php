<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Library\Utilities;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\File\PublicFile;
use Claroline\CoreBundle\Entity\File\PublicFileUse;
use Claroline\CoreBundle\Entity\User;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class FileUtilities
{
    const MAX_FILES = 1000;

    private $filesDir;
    private $fileSystem;
    private $om;
    private $publicFilesDir;
    private $tokenStorage;

    public function __construct(
        string $filesDir,
        Filesystem $fileSystem,
        ObjectManager $om,
        string $publicFilesDir,
        TokenStorageInterface $tokenStorage
    ) {
        $this->filesDir = $filesDir;
        $this->fileSystem = $fileSystem;
        $this->om = $om;
        $this->publicFilesDir = $publicFilesDir;
        $this->tokenStorage = $tokenStorage;
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
        $extension = pathinfo($fileName, PATHINFO_EXTENSION);
        $hashName = Uuid::uuid4()->toString().'.'.$extension;
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

        $user = $this->tokenStorage->getToken()->getUser();
        if ($this->tokenStorage->getToken() && $user instanceof User) {
            $publicFile->setCreator($user);
        }

        $tmpFile->move($this->filesDir.DIRECTORY_SEPARATOR.$prefix, $hashName);
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

        $user = $this->tokenStorage->getToken()->getUser();
        if ($this->tokenStorage->getToken() && $user instanceof User) {
            $publicFile->setCreator($user);
        }

        $this->fileSystem->dumpFile($url, $dataBin);
        $mimeType = mime_content_type($url);
        $publicFile->setMimeType($mimeType);
        $this->om->persist($publicFile);

        if (!is_null($objectClass) && !is_null($objectUuid)) {
            $this->createFileUse($publicFile, $objectClass, $objectUuid, $objectName);
        }
        $this->om->endFlushSuite();

        return $publicFile;
    }

    public function createFileUse(PublicFile $publicFile, $class, $uuid, $name = null)
    {
        $cleanClass = str_replace('Proxies\\__CG__\\', '', $class);
        $repo = $this->om->getRepository('ClarolineCoreBundle:File\PublicFileUse');
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

    public function getActiveDirectoryName()
    {
        $finder = new Finder();
        $finder->depth('== 0');
        $finder->directories()->in($this->publicFilesDir)->name('/^[a-zA-Z]{20}$/');
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

        $newDir = $this->publicFilesDir.DIRECTORY_SEPARATOR.$activeDirectoryName;

        if (!$this->fileSystem->exists($newDir)) {
            $this->fileSystem->mkdir($newDir);
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
        $newDir = $this->publicFilesDir.DIRECTORY_SEPARATOR.$next;

        if (!$this->fileSystem->exists($newDir)) {
            $this->fileSystem->mkdir($newDir);
        }

        return $next;
    }

    public function getOneBy(array $filters): ?PublicFile
    {
        return $this->om->getRepository('ClarolineCoreBundle:File\PublicFile')->findOneBy($filters);
    }

    public function getContents(PublicFile $file)
    {
        return file_get_contents($this->getPath($file));
    }

    public function getPath(PublicFile $file)
    {
        return $this->filesDir.DIRECTORY_SEPARATOR.$file->getUrl();
    }

    /**
     * Take a file size (B) and displays it in a more readable way.
     *
     * @param float $fileSize
     *
     * @return string
     *
     * @deprecated. just let the client do it for you
     */
    public function formatFileSize($fileSize)
    {
        //don't format if it's already formatted.
        $validUnits = ['KB', 'MB', 'GB', 'TB'];

        foreach ($validUnits as $unit) {
            if (strpos($unit, $fileSize)) {
                return $fileSize;
            }
        }

        if ($fileSize < 1024) {
            return $fileSize.' B';
        } elseif ($fileSize < 1048576) {
            return round($fileSize / 1024, 2).' KB';
        } elseif ($fileSize < 1073741824) {
            return round($fileSize / 1048576, 2).' MB';
        } elseif ($fileSize < 1099511627776) {
            return round($fileSize / 1073741824, 2).' GB';
        }

        return round($fileSize / 1099511627776, 2).' TB';
    }

    /**
     * Take a formatted file size and returns the number of bytes.
     *
     * @deprecated. just let the client do it for you
     */
    public function getRealFileSize($fileSize)
    {
        //B goes at the end because it's always matched otherwise
        $validUnits = ['KB', 'MB', 'GB', 'TB'];
        $value = str_replace(' ', '', $fileSize);

        $match = [];
        $pattern = '/\d+/';
        preg_match($pattern, $value, $match);

        foreach ($validUnits as $unit) {
            if (strpos($fileSize, $unit)) {
                switch ($unit) {
                    case 'B':
                        return $match[0] * pow(1024, 0);
                    case 'KB':
                        return $match[0] * pow(1024, 1);
                    case 'MB':
                        return $match[0] * pow(1024, 2);
                    case 'GB':
                        return $match[0] * pow(1024, 3);
                    case 'TB':
                        return $match[0] * pow(1024, 4);
                }
            }
        }

        return $fileSize;
    }
}
