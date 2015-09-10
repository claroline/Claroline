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

use JMS\DiExtraBundle\Annotation as DI;
use Claroline\CoreBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Resource\File;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Library\Utilities\ClaroUtilities;
use Claroline\CoreBundle\Manager\ResourceManager;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Claroline\CoreBundle\Event\StrictDispatcher;

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

    /**
     * @DI\InjectParams({
     *      "om"         = @DI\Inject("claroline.persistence.object_manager"),
     *      "fileDir"    = @DI\Inject("%claroline.param.files_directory%"),
     *      "ut"         = @DI\Inject("claroline.utilities.misc"),
     *      "rm"         = @DI\Inject("claroline.manager.resource_manager"),
     *      "dispatcher" = @DI\Inject("claroline.event.event_dispatcher")
     * })
     */
    public function __construct(
        ObjectManager $om,
        $fileDir,
        ClaroUtilities $ut,
        ResourceManager $rm,
        StrictDispatcher $dispatcher
    )
    {
        $this->om = $om;
        $this->fileDir = $fileDir;
        $this->ut = $ut;
        $this->resManager = $rm;
        $this->dispatcher = $dispatcher;
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
            array($file->getResourceNode(), 'update_file')
        );
    }

    public function deleteContent(File $file)
    {
        $ds = DIRECTORY_SEPARATOR;
        $uploadFile = $this->fileDir . $ds . $file->getHashName();
        @unlink($uploadFile);
    }

    public function uploadContent(File $file,  UploadedFile $upload)
    {
        $ds = DIRECTORY_SEPARATOR;
        $node = $file->getResourceNode();
        $workspaceId = $node->getWorkspace()->getId();

        //edit file
        $fileName = $upload->getClientOriginalName();
        $size = @filesize($upload);
        $extension = pathinfo($fileName, PATHINFO_EXTENSION);
        $mimeType = $upload->getClientMimeType();
        $hashName = 'WORKSPACE_' . $workspaceId .
            $ds .
            $this->ut->generateGuid() .
            "." .
            $extension;
        $upload->move($this->fileDir . $ds .  'WORKSPACE_' . $workspaceId, $hashName);
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
