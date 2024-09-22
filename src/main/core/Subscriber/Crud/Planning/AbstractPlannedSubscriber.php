<?php

namespace Claroline\CoreBundle\Subscriber\Crud\Planning;

use Claroline\AppBundle\Event\Crud\CopyEvent;
use Claroline\AppBundle\Event\Crud\CreateEvent;
use Claroline\AppBundle\Event\Crud\DeleteEvent;
use Claroline\AppBundle\Event\Crud\UpdateEvent;
use Claroline\AppBundle\Event\CrudEvents;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Location;
use Claroline\CoreBundle\Entity\Planning\AbstractPlanned;
use Claroline\CoreBundle\Entity\Planning\PlannedObject;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Manager\FileManager;
use Claroline\CoreBundle\Manager\PlanningManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

abstract class AbstractPlannedSubscriber implements EventSubscriberInterface
{
    protected TokenStorageInterface $tokenStorage;
    protected ObjectManager $om;
    protected FileManager $fileManager;
    protected PlanningManager $planningManager;

    public function setTokenStorage(TokenStorageInterface $tokenStorage): void
    {
        $this->tokenStorage = $tokenStorage;
    }

    public function setObjectManager(ObjectManager $om): void
    {
        $this->om = $om;
    }

    public function setFileManager(FileManager $fileManager): void
    {
        $this->fileManager = $fileManager;
    }

    public function setPlanningManager(PlanningManager $planningManager): void
    {
        $this->planningManager = $planningManager;
    }

    /**
     * Return the FQCN of the planned object (aka the name of the Entity extending AbstractPlanned).
     */
    abstract public static function getPlannedClass(): string;

    public static function getSubscribedEvents(): array
    {
        return [
            CrudEvents::getEventName(CrudEvents::PRE_CREATE, static::getPlannedClass()) => 'preCreate',
            CrudEvents::getEventName(CrudEvents::POST_CREATE, static::getPlannedClass()) => 'postCreate',
            CrudEvents::getEventName(CrudEvents::PRE_UPDATE, static::getPlannedClass()) => 'preUpdate',
            CrudEvents::getEventName(CrudEvents::POST_DELETE, static::getPlannedClass()) => 'postDelete',
            CrudEvents::getEventName(CrudEvents::PRE_COPY, static::getPlannedClass()) => 'preCopy',
        ];
    }

    public function preCreate(CreateEvent $event): void
    {
        /** @var AbstractPlanned $object */
        $object = $event->getObject();

        $user = $this->tokenStorage->getToken()?->getUser();
        if ($user instanceof User && empty($object->getCreator())) {
            $object->setCreator($user);
        }

        $object->setCreatedAt(new \DateTime());
        $object->setUpdatedAt(new \DateTime());

        if (!empty($object->getLocation())) {
            $this->planningManager->addToPlanning($object, $object->getLocation());
        }
    }

    public function postCreate(CreateEvent $event): void
    {
        /** @var AbstractPlanned $object */
        $object = $event->getObject();

        if ($object->getPoster()) {
            $this->fileManager->linkFile(PlannedObject::class, $object->getUuid(), $object->getPoster());
        }

        if ($object->getThumbnail()) {
            $this->fileManager->linkFile(PlannedObject::class, $object->getUuid(), $object->getThumbnail());
        }
    }

    public function preUpdate(UpdateEvent $event): void
    {
        /** @var AbstractPlanned $object */
        $object = $event->getObject();
        $oldData = $event->getOldData();

        $object->setUpdatedAt(new \DateTime());

        $oldLocation = !empty($oldData['location']) ? $oldData['location']['id'] : null;
        $newLocation = !empty($object->getLocation()) ? $object->getLocation()->getUuid() : null;
        if ($oldLocation !== $newLocation) {
            // add to new location
            if ($newLocation) {
                $this->planningManager->addToPlanning($object, $object->getLocation());
            }

            // remove from old location
            if ($oldLocation) {
                /** @var Location $old */
                $old = $this->om->getObject($oldData['location'], Location::class);
                if ($old) {
                    $this->planningManager->removeFromPlanning($object, $old);
                }
            }
        }
    }

    public function postUpdate(UpdateEvent $event): void
    {
        /** @var AbstractPlanned $object */
        $object = $event->getObject();
        $oldData = $event->getOldData();

        $this->fileManager->updateFile(
            PlannedObject::class,
            $object->getUuid(),
            $object->getPoster(),
            !empty($oldData['poster']) ? $oldData['poster'] : null
        );

        $this->fileManager->updateFile(
            PlannedObject::class,
            $object->getUuid(),
            $object->getThumbnail(),
            !empty($oldData['thumbnail']) ? $oldData['thumbnail'] : null
        );
    }

    public function postDelete(DeleteEvent $event): void
    {
        /** @var AbstractPlanned $object */
        $object = $event->getObject();

        if ($object->getPoster()) {
            $this->fileManager->unlinkFile(PlannedObject::class, $object->getUuid(), $object->getPoster());
        }

        if ($object->getThumbnail()) {
            $this->fileManager->unlinkFile(PlannedObject::class, $object->getUuid(), $object->getThumbnail());
        }
    }

    public function preCopy(CopyEvent $event): void
    {
        /** @var AbstractPlanned $original */
        $original = $event->getObject();

        /** @var AbstractPlanned $copy */
        $copy = $event->getCopy();

        $copy->setCreatedAt(new \DateTime());
        $copy->setUpdatedAt(new \DateTime());

        $plannedObjectRepo = $this->om->getRepository(PlannedObject::class);

        $copyName = $plannedObjectRepo->findNextUnique('name', $original->getPlannedObject()->getName());
        $copy->getPlannedObject()->setName($copyName);
    }
}
