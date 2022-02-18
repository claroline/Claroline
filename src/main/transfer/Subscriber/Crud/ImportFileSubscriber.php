<?php

namespace Claroline\TransferBundle\Subscriber\Crud;

use Claroline\AppBundle\API\Crud;
use Claroline\AppBundle\Event\Crud\CreateEvent;
use Claroline\AppBundle\Event\Crud\DeleteEvent;
use Claroline\CoreBundle\Entity\User;
use Claroline\TransferBundle\Entity\ImportFile;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class ImportFileSubscriber implements EventSubscriberInterface
{
    /** @var TokenStorageInterface */
    private $tokenStorage;
    /** @var Crud */
    private $crud;
    /** @var string */
    private $logDir;

    public function __construct(
        TokenStorageInterface $tokenStorage,
        Crud $crud,
        string $logDir
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->crud = $crud;
        $this->logDir = $logDir;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            Crud::getEventName('create', 'pre', ImportFile::class) => 'preCreate',
            Crud::getEventName('delete', 'post', ImportFile::class) => 'postDelete',
        ];
    }

    public function preCreate(CreateEvent $event)
    {
        /** @var ImportFile $object */
        $object = $event->getObject();

        $user = $this->tokenStorage->getToken()->getUser();
        if ($user instanceof User && empty($object->getCreator())) {
            $object->setCreator($user);
        }

        $object->setCreatedAt(new \DateTime());
    }

    public function postDelete(DeleteEvent $event)
    {
        /** @var ImportFile $object */
        $object = $event->getObject();

        // delete imported file
        if ($object->getFile()) {
            $this->crud->delete($object->getFile(), [Crud::NO_PERMISSIONS]);
        }

        // delete log file
        if ($object->getLog()) {
            $fs = new FileSystem();
            $fs->remove($this->logDir.DIRECTORY_SEPARATOR.$object->getLog());
        }
    }
}
