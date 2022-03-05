<?php

namespace Claroline\CoreBundle\Subscriber\Crud\File;

use Claroline\AppBundle\API\Crud;
use Claroline\AppBundle\Event\Crud\CreateEvent;
use Claroline\AppBundle\Event\Crud\DeleteEvent;
use Claroline\CoreBundle\Entity\File\PublicFile;
use Claroline\CoreBundle\Manager\FileManager;
use Ramsey\Uuid\Uuid;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Filesystem\Filesystem;

class PublicFileSubscriber implements EventSubscriberInterface
{
    /** @var FileManager */
    private $fileManager;

    public function __construct(
        FileManager $fileManager
    ) {
        $this->fileManager = $fileManager;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            Crud::getEventName('create', 'pre', PublicFile::class) => 'preCreate',
            Crud::getEventName('delete', 'post', PublicFile::class) => 'postDelete',
        ];
    }

    public function preCreate(CreateEvent $event)
    {
        /** @var PublicFile $publicFile */
        $publicFile = $event->getObject();
        $options = $event->getOptions();
        $tmpFile = $options['file'];

        $fileName = !method_exists($tmpFile, 'getClientOriginalName') || !$tmpFile->getClientOriginalName() ?
            $tmpFile->getFileName() :
            $tmpFile->getClientOriginalName();
        $directoryName = $this->fileManager->getActiveDirectoryName();
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

        $tmpFile->move($this->fileManager->getDirectory().DIRECTORY_SEPARATOR.$prefix, $hashName);
    }

    public function postDelete(DeleteEvent $event)
    {
        /** @var PublicFile $object */
        $object = $event->getObject();
        if ($object->getUrl()) {
            $fs = new FileSystem();
            $fs->remove($this->fileManager->getDirectory().DIRECTORY_SEPARATOR.$object->getUrl());
        }
    }
}
