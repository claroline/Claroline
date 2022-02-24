<?php

namespace Claroline\TransferBundle\Subscriber\Crud;

use Claroline\AppBundle\API\Crud;
use Claroline\AppBundle\Event\Crud\CreateEvent;
use Claroline\AppBundle\Event\Crud\DeleteEvent;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\User;
use Claroline\SchedulerBundle\Entity\ScheduledTask;
use Claroline\TransferBundle\Entity\ImportFile;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class ImportFileSubscriber implements EventSubscriberInterface
{
    /** @var TokenStorageInterface */
    private $tokenStorage;
    /** @var ObjectManager */
    private $om;
    /** @var Crud */
    private $crud;
    /** @var string */
    private $logDir;

    public function __construct(
        TokenStorageInterface $tokenStorage,
        ObjectManager $om,
        Crud $crud,
        string $logDir
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->om = $om;
        $this->crud = $crud;
        $this->logDir = $logDir;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            Crud::getEventName('create', 'pre', ImportFile::class) => 'preCreate',
            Crud::getEventName('create', 'post', ImportFile::class) => 'postCreate',
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

    public function postCreate(CreateEvent $event)
    {
        /** @var ImportFile $object */
        $object = $event->getObject();
        $data = $event->getData();

        if (empty($data['scheduler'])) {
            return;
        }

        $this->crud->create(ScheduledTask::class, array_merge($data['scheduler'], [
            'name' => $object->getAction(),
            'action' => 'import',
            'parentId' => $object->getUuid(),
        ]), [Crud::THROW_EXCEPTION]);
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

        // delete scheduled tasks if any
        $tasks = $this->om->getRepository(ScheduledTask::class)->findBy(['parentId' => $object->getUuid()]);
        if (!empty($tasks)) {
            $this->crud->deleteBulk($tasks);
        }
    }
}
