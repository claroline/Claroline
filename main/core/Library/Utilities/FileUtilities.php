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

use Claroline\CoreBundle\Entity\File\PublicFile;
use Claroline\CoreBundle\Entity\File\PublicFileUse;
use Claroline\CoreBundle\Persistence\ObjectManager;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Filesystem\Filesystem as SymfonyFileSystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * @DI\Service("claroline.utilities.file")
 */
class FileUtilities
{
    const MAX_FILES = 1000;

    private $claroUtils;
    private $filesDir;
    private $fileSystem;
    private $om;
    private $publicFilesDir;
    private $tokenStorage;

    /**
     * @DI\InjectParams({
     *     "claroUtils"     = @DI\Inject("claroline.utilities.misc"),
     *     "filesDir"       = @DI\Inject("%claroline.param.files_directory%"),
     *     "fileSystem"     = @DI\Inject("filesystem"),
     *     "om"             = @DI\Inject("claroline.persistence.object_manager"),
     *     "publicFilesDir" = @DI\Inject("%claroline.param.public_files_directory%"),
     *     "tokenStorage"   = @DI\Inject("security.token_storage")
     * })
     */
    public function __construct(
        ClaroUtilities $claroUtils,
        $filesDir,
        SymfonyFileSystem $fileSystem,
        ObjectManager $om,
        $publicFilesDir,
        TokenStorageInterface $tokenStorage
    ) {
        $this->claroUtils = $claroUtils;
        $this->filesDir = $filesDir;
        $this->fileSystem = $fileSystem;
        $this->om = $om;
        $this->publicFilesDir = $publicFilesDir;
        $this->tokenStorage = $tokenStorage;
    }

    public function getFilesDir()
    {
        return $this->filesDir;
    }

    /**
     * Creates a file into public files directory.
     * Then creates a <PublicFileUse> for created public file if $objectClass and $objectUuid are specified.
     *
     * @param File   $tmpFile
     * @param string $name
     * @param string $objectClass
     * @param string $objectUuid
     * @param string $objectName
     * @param string $sourceType
     *
     * @return PublicFile
     */
    public function createFile(
        File $tmpFile,
        $name = null,
        $objectClass = null,
        $objectUuid = null,
        $objectName = null,
        $sourceType = null
    ) {
        $fileName = $name ? $name : $tmpFile->getFilename();
        $directoryName = $this->getActiveDirectoryName();
        $size = filesize($tmpFile);
        $mimeType = $tmpFile->getMimeType();
        $extension = pathinfo($fileName, PATHINFO_EXTENSION);
        $hashName = $this->claroUtils->generateGuid().'.'.$extension;
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

        if ($this->tokenStorage->getToken() && $user = 'anon.' !== $this->tokenStorage->getToken()->getUser()) {
            $user = $this->tokenStorage->getToken()->getUser();
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
     *
     * @param string $data
     * @param string $fileName
     * @param string $objectClass
     * @param string $objectUuid
     * @param string $objectName
     * @param string $sourceType
     *
     * @return PublicFile
     */
    public function createFileFromData(
        $data,
        $fileName,
        $objectClass = null,
        $objectUuid = null,
        $objectName = null,
        $sourceType = null
    ) {
        $user = $this->tokenStorage->getToken()->getUser();
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
        $hashName = $this->claroUtils->generateGuid();
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

        if ($this->tokenStorage->getToken() && $user = 'anon.' !== $this->tokenStorage->getToken()->getUser()) {
            $user = $this->tokenStorage->getToken()->getUser();
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
        $publicFileUse = new PublicFileUse();
        $publicFileUse->setPublicFile($publicFile);
        $publicFileUse->setObjectClass($cleanClass);
        $publicFileUse->setObjectUuid($uuid);
        $publicFileUse->setObjectName($name);
        $this->om->persist($publicFileUse);
        $this->om->flush();

        return $publicFileUse;
    }

    public function deletePublicFile(PublicFile $publicFile)
    {
        $uploadedFile = $this->filesDir.DIRECTORY_SEPARATOR.$publicFile->getUrl();
        $this->om->remove($publicFile);
        $this->om->flush();

        if ($this->fileSystem->exists($uploadedFile)) {
            $this->fileSystem->remove($uploadedFile);
        }
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

    public function getOneBy($filters)
    {
        return $this->om->getRepository('ClarolineCoreBundle:File\PublicFile')->findOneBy($filters);
    }

    public function getPublicFileByType($type)
    {
        return $this->om->getRepository('ClarolineCoreBundle:File\PublicFile')->findBySourceType($type);
    }

    public function getContents(PublicFile $file)
    {
        return file_get_contents($this->filesDir.DIRECTORY_SEPARATOR.$file->getUrl());
    }
}
