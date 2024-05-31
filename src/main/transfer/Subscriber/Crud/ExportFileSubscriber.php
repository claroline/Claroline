<?php

namespace Claroline\TransferBundle\Subscriber\Crud;

use Claroline\AppBundle\API\Crud;
use Claroline\AppBundle\Event\Crud\CreateEvent;
use Claroline\AppBundle\Event\Crud\DeleteEvent;
use Claroline\AppBundle\Event\Crud\UpdateEvent;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\User;
use Claroline\AppBundle\Event\CrudEvents;
use Claroline\SchedulerBundle\Entity\ScheduledTask;
use Claroline\TransferBundle\Entity\ExportFile;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class ExportFileSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly TokenStorageInterface $tokenStorage,
        private readonly ObjectManager $om,
        private readonly Crud $crud,
        private readonly string $filesDir,
        private readonly string $logDir
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            CrudEvents::getEventName(CrudEvents::PRE_CREATE, ExportFile::class) => 'preCreate',
            CrudEvents::getEventName(CrudEvents::POST_CREATE, ExportFile::class) => 'postCreate',
            CrudEvents::getEventName(CrudEvents::POST_UPDATE, ExportFile::class) => 'postUpdate',
            CrudEvents::getEventName(CrudEvents::POST_DELETE, ExportFile::class) => 'postDelete',
        ];
    }

    public function preCreate(CreateEvent $event): void
    {
        /** @var ExportFile $object */
        $object = $event->getObject();

        $user = $this->tokenStorage->getToken()->getUser();
        if ($user instanceof User && empty($object->getCreator())) {
            $object->setCreator($user);
        }

        $object->setCreatedAt(new \DateTime());
    }

    public function postCreate(CreateEvent $event): void
    {
        /** @var ExportFile $object */
        $object = $event->getObject();
        $data = $event->getData();

        if (empty($data['scheduler'])) {
            return;
        }

        $this->crud->create(ScheduledTask::class, array_merge($data['scheduler'], [
            'name' => $object->getAction(),
            'action' => 'export',
            'parentId' => $object->getUuid(),
        ]), [Crud::THROW_EXCEPTION]);
    }

    public function postUpdate(UpdateEvent $event): void
    {
        /** @var ExportFile $object */
        $object = $event->getObject();
        $data = $event->getData();

        $scheduler = $this->om->getRepository(ScheduledTask::class)->findOneBy(['parentId' => $object->getUuid()]);
        if (empty($data['scheduler']) && empty($scheduler)) {
            // no scheduled task
            return;
        }

        if (empty($data['scheduler'])) {
            if (!empty($scheduler)) {
                $this->crud->delete($scheduler);
            }
        } else {
            if (!empty($scheduler)) {
                $this->crud->update($scheduler, $data['scheduler'], [Crud::NO_PERMISSIONS, Crud::THROW_EXCEPTION]);
            } else {
                $this->crud->create(ScheduledTask::class, array_merge($data['scheduler'], [
                    'name' => $object->getAction(),
                    'action' => 'export',
                    'parentId' => $object->getUuid(),
                ]), [Crud::NO_PERMISSIONS, Crud::THROW_EXCEPTION]);
            }
        }
    }

    public function postDelete(DeleteEvent $event): void
    {
        /** @var ExportFile $object */
        $object = $event->getObject();

        $fs = new FileSystem();

        // delete log file
        if (file_exists($this->logDir.DIRECTORY_SEPARATOR.$object->getUuid())) {
            $fs->remove($this->logDir.DIRECTORY_SEPARATOR.$object->getUuid());
        }

        // delete exported file
        if (file_exists($this->filesDir.DIRECTORY_SEPARATOR.$object->getUuid())) {
            $fs->remove($this->filesDir.DIRECTORY_SEPARATOR.$object->getUuid());
        }

        // delete scheduled tasks if any
        $tasks = $this->om->getRepository(ScheduledTask::class)->findBy(['parentId' => $object->getUuid()]);
        if (!empty($tasks)) {
            $this->crud->deleteBulk($tasks);
        }
    }
}
