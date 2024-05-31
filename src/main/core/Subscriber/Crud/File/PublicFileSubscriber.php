<?php

namespace Claroline\CoreBundle\Subscriber\Crud\File;

use Claroline\AppBundle\Event\CrudEvents;
use Claroline\AppBundle\Event\Crud\CreateEvent;
use Claroline\AppBundle\Event\Crud\DeleteEvent;
use Claroline\CoreBundle\Entity\File\PublicFile;
use Claroline\CoreBundle\Manager\FileManager;
use Ramsey\Uuid\Uuid;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Filesystem\Filesystem;

class PublicFileSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly FileManager $fileManager
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            CrudEvents::getEventName(CrudEvents::PRE_CREATE, PublicFile::class) => 'preCreate',
            CrudEvents::getEventName(CrudEvents::POST_DELETE, PublicFile::class) => 'postDelete',
        ];
    }

    public function preCreate(CreateEvent $event): void
    {
        /** @var PublicFile $publicFile */
        $publicFile = $event->getObject();
        $options = $event->getOptions();
        $tmpFile = $options['file'];

        $hashName = Uuid::uuid4()->toString();
        $extension = $tmpFile->guessExtension();
        if ($extension) {
            $hashName .= '.'.$extension;
        }

        $destinationDir = 'data'.DIRECTORY_SEPARATOR.$this->fileManager->getActiveDirectoryName();
        $url = $destinationDir.DIRECTORY_SEPARATOR.$hashName;

        $publicFile->setFilename(method_exists($tmpFile, 'getClientOriginalName') ? $tmpFile->getClientOriginalName() : $tmpFile->getFileName());
        $publicFile->setSize(filesize($tmpFile));
        $publicFile->setMimeType($tmpFile->getMimeType());
        $publicFile->setUrl($url);

        $tmpFile->move($this->fileManager->getDirectory().DIRECTORY_SEPARATOR.$destinationDir, $hashName);
    }

    public function postDelete(DeleteEvent $event): void
    {
        /** @var PublicFile $object */
        $object = $event->getObject();
        if ($object->getUrl()) {
            $fs = new Filesystem();
            $fs->remove($this->fileManager->getDirectory().DIRECTORY_SEPARATOR.$object->getUrl());
        }
    }
}
