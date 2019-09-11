<?php

namespace Claroline\CoreBundle\API\Crud\File;

use Claroline\AppBundle\Event\Crud\CreateEvent;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Library\Utilities\FileUtilities;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Filesystem\Filesystem as SymfonyFileSystem;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class PublicFile
{
    /** @var ObjectManager */
    private $om;

    /** @var FileUtilities */
    private $utils;

    public function __construct(
        $filesDir,
        SymfonyFileSystem $fileSystem,
        ObjectManager $om,
        $publicFilesDir,
        TokenStorageInterface $tokenStorage,
        FileUtilities $fileUtils
    ) {
        $this->filesDir = $filesDir;
        $this->fileSystem = $fileSystem;
        $this->om = $om;
        $this->publicFilesDir = $publicFilesDir;
        $this->tokenStorage = $tokenStorage;
        $this->fileUtils = $fileUtils;
    }

    /**
     * @param CreateEvent $event
     */
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
        $mimeType = !method_exists($tmpFile, 'getClientMimeType') || !$tmpFile->getClientMimeType() ?
            $tmpFile->getMimeType() :
            $tmpFile->getClientMimeType();
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

        if ($this->tokenStorage->getToken() && $user = 'anon.' !== $this->tokenStorage->getToken()->getUser()) {
            $user = $this->tokenStorage->getToken()->getUser();
            $publicFile->setCreator($user);
        }

        $tmpFile->move($this->filesDir.DIRECTORY_SEPARATOR.$prefix, $hashName);
    }
}
