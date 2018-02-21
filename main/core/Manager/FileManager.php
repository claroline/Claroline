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
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Library\Utilities\ClaroUtilities;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\HttpFoundation\File\File as SfFile;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * @DI\Service("claroline.manager.file_manager")
 */
class FileManager
{
    private $om;
    private $fileDir;
    private $ut;
    private $resManager;
    private $dispatcher;
    private $tokenStorage;

    /**
     * @DI\InjectParams({
     *      "om"               = @DI\Inject("claroline.persistence.object_manager"),
     *      "fileDir"          = @DI\Inject("%claroline.param.files_directory%"),
     *      "uploadDir"        = @DI\Inject("%claroline.param.uploads_directory%"),
     *      "ut"               = @DI\Inject("claroline.utilities.misc"),
     *      "rm"               = @DI\Inject("claroline.manager.resource_manager"),
     *      "dispatcher"       = @DI\Inject("claroline.event.event_dispatcher"),
     *      "tokenStorage"     = @DI\Inject("security.token_storage"),
     *      "workspaceManager" = @DI\Inject("claroline.manager.workspace_manager")
     * })
     */
    public function __construct(
        ObjectManager $om,
        $fileDir,
        ClaroUtilities $ut,
        ResourceManager $rm,
        StrictDispatcher $dispatcher,
        $uploadDir,
        TokenStorageInterface $tokenStorage,
        WorkspaceManager $workspaceManager
    ) {
        $this->om = $om;
        $this->fileDir = $fileDir;
        $this->ut = $ut;
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
                $this->ut->generateGuid().
                '.'.
                $extension;
            $tmpFile->move(
                $this->workspaceManager->getStorageDirectory($workspace).'/',
                $hashName
            );
        } else {
            $hashName =
                $this->tokenStorage->getToken()->getUsername().DIRECTORY_SEPARATOR.$this->ut->generateGuid().
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
        $this->resManager->resetIcon($file->getResourceNode());
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
        $size = @filesize($upload);
        $extension = pathinfo($fileName, PATHINFO_EXTENSION);
        $mimeType = $upload->getClientMimeType();
        $hashName = 'WORKSPACE_'.$workspaceId.
            $ds.
            $this->ut->generateGuid().
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

    public function getDirectoryChildren(ResourceNode $parent)
    {
        return $this->om->getRepository('Claroline\CoreBundle\Entity\Resource\File')
            ->findDirectoryChildren($parent);
    }
}
