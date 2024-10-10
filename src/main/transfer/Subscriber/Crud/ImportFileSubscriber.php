<?php

namespace Claroline\TransferBundle\Subscriber\Crud;

use Claroline\AppBundle\API\Crud;
use Claroline\AppBundle\Event\Crud\CreateEvent;
use Claroline\AppBundle\Event\Crud\DeleteEvent;
use Claroline\AppBundle\Event\Crud\UpdateEvent;
use Claroline\AppBundle\Event\CrudEvents;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\User;
use Claroline\SchedulerBundle\Entity\ScheduledTask;
use Claroline\TransferBundle\Entity\ImportFile;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

final class ImportFileSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly TokenStorageInterface $tokenStorage,
        private readonly ObjectManager $om,
        private readonly Crud $crud,
        private readonly string $logDir
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            CrudEvents::getEventName(CrudEvents::PRE_CREATE, ImportFile::class) => 'preCreate',
            CrudEvents::getEventName(CrudEvents::POST_CREATE, ImportFile::class) => 'postCreate',
            CrudEvents::getEventName(CrudEvents::POST_UPDATE, ImportFile::class) => 'postUpdate',
            CrudEvents::getEventName(CrudEvents::POST_DELETE, ImportFile::class) => 'postDelete',
        ];
    }

    public function preCreate(CreateEvent $event): void
    {
        /** @var ImportFile $object */
        $object = $event->getObject();

        $user = $this->tokenStorage->getToken()?->getUser();
        if ($user instanceof User && empty($object->getCreator())) {
            $object->setCreator($user);
        }

        $object->setCreatedAt(new \DateTime());
    }

    public function postCreate(CreateEvent $event): void
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
        ]));
    }

    public function postUpdate(UpdateEvent $event): void
    {
        /** @var ImportFile $object */
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
                $this->crud->update($scheduler, $data['scheduler'], [Crud::NO_PERMISSIONS]);
            } else {
                $this->crud->create(ScheduledTask::class, array_merge($data['scheduler'], [
                    'name' => $object->getAction(),
                    'action' => 'import',
                    'parentId' => $object->getUuid(),
                ]), [Crud::NO_PERMISSIONS]);
            }
        }
    }

    public function postDelete(DeleteEvent $event): void
    {
        /** @var ImportFile $object */
        $object = $event->getObject();

        // delete imported file
        if ($object->getFile()) {
            $this->crud->delete($object->getFile(), [Crud::NO_PERMISSIONS]);
        }

        // delete log file
        if ($object->getLog()) {
            $fs = new Filesystem();
            $fs->remove($this->logDir.DIRECTORY_SEPARATOR.$object->getLog());
        }

        // delete scheduled tasks if any
        $tasks = $this->om->getRepository(ScheduledTask::class)->findBy(['parentId' => $object->getUuid()]);
        if (!empty($tasks)) {
            $this->crud->deleteBulk($tasks);
        }
    }
}
