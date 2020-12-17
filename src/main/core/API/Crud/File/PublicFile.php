<?php

namespace Claroline\CoreBundle\API\Crud\File;

use Claroline\AppBundle\Event\Crud\CreateEvent;
use Claroline\CoreBundle\Library\Utilities\FileUtilities;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class PublicFile
{
    /** @var string */
    private $filesDir;
    /** @var TokenStorageInterface */
    private $tokenStorage;
    /** @var FileUtilities */
    private $fileUtils;

    public function __construct(
        $filesDir,
        TokenStorageInterface $tokenStorage,
        FileUtilities $fileUtils
    ) {
        $this->filesDir = $filesDir;
        $this->tokenStorage = $tokenStorage;
        $this->fileUtils = $fileUtils;
    }

    public function preCreate(CreateEvent $event)
    {
        $publicFile = $event->getObject();
        $options = $event->getOptions();
        $tmpFile = $options['file'];

        $fileName = !method_exists($tmpFile, 'getClientOriginalName') || !$tmpFile->getClientOriginalName() ?
            $tmpFile->getFileName() :
            $tmpFile->getClientOriginalName();
        $directoryName = $this->fileUtils->getActiveDirectoryName();
        $size = filesize($tmpFile);
        $mimeType = !$tmpFile->getMimeType() && method_exists($tmpFile, 'getClientMimeType') ?
            $tmpFile->getClientMimeType() :
            $tmpFile->getMimeType();
        $extension = !method_exists($tmpFile, 'getClientOriginalExtension') || !$tmpFile->getClientOriginalExtension() ?
            $tmpFile->guessExtension() :
            $tmpFile->getClientOriginalExtension();
        $hashName = Uuid::uuid4()->toString().'.'.$extension;
        $prefix = 'data'.DIRECTORY_SEPARATOR.$directoryName;
        $url = $prefix.DIRECTORY_SEPARATOR.$hashName;

        $publicFile->setDirectoryName($directoryName);
        $publicFile->setFilename($fileName);
        $publicFile->setSize($size);
        $publicFile->setMimeType($mimeType);
        $publicFile->setCreationDate(new \DateTime());
        $publicFile->setUrl($url);

        if (empty($publicFile->getCreator()) && $this->tokenStorage->getToken() && 'anon.' !== $this->tokenStorage->getToken()->getUser()) {
            $publicFile->setCreator($this->tokenStorage->getToken()->getUser());
        }

        $tmpFile->move($this->filesDir.DIRECTORY_SEPARATOR.$prefix, $hashName);
    }
}
