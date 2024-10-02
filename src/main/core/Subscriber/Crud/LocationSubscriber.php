<?php

namespace Claroline\CoreBundle\Subscriber\Crud;

use Claroline\AppBundle\Event\Crud\CreateEvent;
use Claroline\AppBundle\Event\Crud\DeleteEvent;
use Claroline\AppBundle\Event\Crud\UpdateEvent;
use Claroline\AppBundle\Event\CrudEvents;
use Claroline\CoreBundle\Entity\Location;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Manager\FileManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class LocationSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly TokenStorageInterface $tokenStorage,
        private readonly FileManager $fileManager
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            CrudEvents::getEventName(CrudEvents::PRE_CREATE, Location::class) => 'preCreate',
            CrudEvents::getEventName(CrudEvents::POST_CREATE, Location::class) => 'postCreate',
            CrudEvents::getEventName(CrudEvents::POST_UPDATE, Location::class) => 'postUpdate',
            CrudEvents::getEventName(CrudEvents::POST_DELETE, Location::class) => 'postDelete',
        ];
    }

    public function preCreate(CreateEvent $event): void
    {
        /** @var Location $location */
        $location = $event->getObject();
        $user = $this->tokenStorage->getToken()?->getUser();

        if ($user instanceof User) {
            $location->addOrganization($user->getMainOrganization());
        }
    }

    public function postCreate(CreateEvent $event): void
    {
        /** @var Location $location */
        $location = $event->getObject();

        if ($location->getPoster()) {
            $this->fileManager->linkFile(Location::class, $location->getUuid(), $location->getPoster());
        }

        if ($location->getThumbnail()) {
            $this->fileManager->linkFile(Location::class, $location->getUuid(), $location->getThumbnail());
        }
    }

    public function postUpdate(UpdateEvent $event): void
    {
        /** @var Location $location */
        $location = $event->getObject();
        $oldData = $event->getOldData();

        $this->fileManager->updateFile(
            Location::class,
            $location->getUuid(),
            $location->getPoster(),
            !empty($oldData['poster']) ? $oldData['poster'] : null
        );

        $this->fileManager->updateFile(
            Location::class,
            $location->getUuid(),
            $location->getThumbnail(),
            !empty($oldData['thumbnail']) ? $oldData['thumbnail'] : null
        );
    }


    public function postDelete(DeleteEvent $event): void
    {
        /** @var Location $location */
        $location = $event->getObject();

        if ($location->getPoster()) {
            $this->fileManager->unlinkFile(Location::class, $location->getUuid(), $location->getPoster());
        }

        if ($location->getThumbnail()) {
            $this->fileManager->unlinkFile(Location::class, $location->getUuid(), $location->getThumbnail());
        }
    }
}
