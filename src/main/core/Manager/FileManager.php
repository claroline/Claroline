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

use Claroline\AppBundle\Event\StrictDispatcher;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Resource\File;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Manager\Workspace\WorkspaceManager;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\File\File as SfFile;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class FileManager
{
    private $om;
    private $fileDir;
    private $resManager;
    private $dispatcher;
    private $tokenStorage;

    public function __construct(
        ObjectManager $om,
        $fileDir,
        ResourceManager $rm,
        StrictDispatcher $dispatcher,
        $uploadDir,
        TokenStorageInterface $tokenStorage,
        WorkspaceManager $workspaceManager
    ) {
        $this->om = $om;
        $this->fileDir = $fileDir;
        $this->resManager = $rm;
        $this->dispatcher = $dispatcher;
        $this->uploadDir = $uploadDir;
        $this->tokenStorage = $tokenStorage;
        $this->workspaceManager = $workspaceManager;
    }

    public function create(
        File $file,
        SfFile $tmpFile,
        $fileName,
        $mimeType,
        Workspace $workspace = null
    ) {
        $extension = pathinfo($fileName, PATHINFO_EXTENSION);
        $size = filesize($tmpFile);

        if (!is_null($workspace)) {
            $hashName = 'WORKSPACE_'.$workspace->getId().
                DIRECTORY_SEPARATOR.
                Uuid::uuid4()->toString().
                '.'.
                $extension;
            $tmpFile->move(
                $this->workspaceManager->getStorageDirectory($workspace).'/',
                $hashName
            );
        } else {
            $hashName =
                $this->tokenStorage->getToken()->getUsername().DIRECTORY_SEPARATOR.Uuid::uuid4()->toString().
                '.'.$extension;
            $tmpFile->move(
                $this->fileDir.DIRECTORY_SEPARATOR.$this->tokenStorage->getToken()->getUsername(),
                $hashName
            );
        }
        $file->setSize($size);
        $file->setName($fileName);
        $file->setHashName($hashName);
        $file->setMimeType($mimeType);
        $this->om->persist($file);

        return $file;
    }

    public function changeFile(File $file, UploadedFile $upload)
    {
        $this->om->startFlushSuite();
        $this->deleteContent($file);
        $this->uploadContent($file, $upload);
        $this->om->endFlushSuite();

        $this->dispatcher->dispatch(
            'log',
            'Log\LogResourceCustom',
            [$file->getResourceNode(), 'update_file']
        );
    }

    public function deleteContent(File $file)
    {
        $ds = DIRECTORY_SEPARATOR;
        $uploadFile = $this->fileDir.$ds.$file->getHashName();
        @unlink($uploadFile);
    }

    public function uploadContent(File $file, UploadedFile $upload)
    {
        $ds = DIRECTORY_SEPARATOR;
        $node = $file->getResourceNode();
        $workspaceId = $node->getWorkspace()->getId();

        //edit file
        $fileName = $upload->getClientOriginalName();
        $size = filesize($upload) ?: 0;
        $extension = pathinfo($fileName, PATHINFO_EXTENSION);
        $mimeType = $upload->getClientMimeType();
        $hashName = 'WORKSPACE_'.$workspaceId.
            $ds.
            Uuid::uuid4()->toString().
            '.'.
            $extension;
        $upload->move($this->fileDir.$ds.'WORKSPACE_'.$workspaceId, $hashName);
        $file->setSize($size);
        $file->setHashName($hashName);
        $file->setMimeType($mimeType);

        //edit node
        $node->setMimeType($mimeType);
        $node->setName($fileName);

        //edit icon

        $this->om->persist($file);
        $this->om->persist($node);
        $this->om->flush();
    }

    public function computeUsedStorage()
    {
        $filesDirSize = 0;

        foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($this->fileDir)) as $file) {
            if ('..' !== $file->getFilename()) {
                $filesDirSize += $file->getSize();
            }
        }

        return $filesDirSize;
    }
}
