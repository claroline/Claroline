<?php

namespace Claroline\TransferBundle\Subscriber\Crud;

use Claroline\AppBundle\API\Crud;
use Claroline\AppBundle\Event\Crud\CreateEvent;
use Claroline\AppBundle\Event\Crud\DeleteEvent;
use Claroline\CoreBundle\Entity\User;
use Claroline\TransferBundle\Entity\ExportFile;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class ExportFileSubscriber implements EventSubscriberInterface
{
    /** @var TokenStorageInterface */
    private $tokenStorage;
    /** @var Crud */
    private $crud;
    /** @var string */
    private $filesDir;
    /** @var string */
    private $logDir;

    public function __construct(
        TokenStorageInterface $tokenStorage,
        Crud $crud,
        string $filesDir,
        string $logDir
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->crud = $crud;
        $this->filesDir = $filesDir;
        $this->logDir = $logDir;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            Crud::getEventName('create', 'pre', ExportFile::class) => 'preCreate',
            Crud::getEventName('delete', 'post', ExportFile::class) => 'postDelete',
        ];
    }

    public function preCreate(CreateEvent $event)
    {
        /** @var ExportFile $object */
        $object = $event->getObject();

        $user = $this->tokenStorage->getToken()->getUser();
        if ($user instanceof User && empty($object->getCreator())) {
            $object->setCreator($user);
        }

        $object->setCreatedAt(new \DateTime());
    }

    public function postDelete(DeleteEvent $event)
    {
        /** @var ExportFile $object */
        $object = $event->getObject();

        $fs = new FileSystem();

        // delete log file
        $fs->remove($this->logDir.DIRECTORY_SEPARATOR.$object->getUuid());

        // delete exported file
        if ($object->getUrl()) {
            $fs = new FileSystem();
            $fs->remove($this->filesDir.DIRECTORY_SEPARATOR.$object->getUrl());
        }
    }
}
