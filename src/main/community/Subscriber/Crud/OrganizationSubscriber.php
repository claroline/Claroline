<?php

namespace Claroline\CommunityBundle\Subscriber\Crud;

use Claroline\AppBundle\Event\Crud\CreateEvent;
use Claroline\AppBundle\Event\Crud\DeleteEvent;
use Claroline\AppBundle\Event\Crud\UpdateEvent;
use Claroline\AppBundle\Event\CrudEvents;
use Claroline\CoreBundle\Entity\Organization\Organization;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Manager\FileManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class OrganizationSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly TokenStorageInterface $tokenStorage,
        private readonly FileManager $fileManager
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            CrudEvents::getEventName(CrudEvents::PRE_CREATE, Organization::class) => 'preCreate',
            CrudEvents::getEventName(CrudEvents::POST_CREATE, Organization::class) => 'postCreate',
            CrudEvents::getEventName(CrudEvents::POST_UPDATE, Organization::class) => 'postUpdate',
            CrudEvents::getEventName(CrudEvents::PRE_DELETE, Organization::class) => 'preDelete',
            CrudEvents::getEventName(CrudEvents::POST_DELETE, Organization::class) => 'postDelete',
        ];
    }

    public function preCreate(CreateEvent $event): void
    {
        $organization = $event->getObject();
        $user = $this->tokenStorage->getToken()->getUser();

        if ($user instanceof User) {
            $organization->addManager($user);
        }
    }

    public function postCreate(CreateEvent $event): void
    {
        /** @var Organization $organization */
        $organization = $event->getObject();

        if ($organization->getPoster()) {
            $this->fileManager->linkFile(Organization::class, $organization->getUuid(), $organization->getPoster());
        }

        if ($organization->getThumbnail()) {
            $this->fileManager->linkFile(Organization::class, $organization->getUuid(), $organization->getThumbnail());
        }
    }

    public function postUpdate(UpdateEvent $event): void
    {
        /** @var Organization $organization */
        $organization = $event->getObject();
        $oldData = $event->getOldData();

        $this->fileManager->updateFile(
            Organization::class,
            $organization->getUuid(),
            $organization->getPoster(),
            !empty($oldData['poster']) ? $oldData['poster'] : null
        );

        $this->fileManager->updateFile(
            Organization::class,
            $organization->getUuid(),
            $organization->getThumbnail(),
            !empty($oldData['thumbnail']) ? $oldData['thumbnail'] : null
        );
    }

    public function preDelete(DeleteEvent $event): void
    {
        /** @var Organization $organization */
        $organization = $event->getObject();
        if ($organization->isDefault()) {
            $event->block();
        }
    }

    public function postDelete(DeleteEvent $event): void
    {
        /** @var Organization $organization */
        $organization = $event->getObject();

        if ($organization->getPoster()) {
            $this->fileManager->unlinkFile(Organization::class, $organization->getUuid(), $organization->getPoster());
        }

        if ($organization->getThumbnail()) {
            $this->fileManager->unlinkFile(Organization::class, $organization->getUuid(), $organization->getThumbnail());
        }
    }
}
