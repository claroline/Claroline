<?php

namespace Claroline\ExampleBundle\Subscriber\Crud;

use Claroline\AppBundle\Event\Crud\CreateEvent;
use Claroline\AppBundle\Event\Crud\DeleteEvent;
use Claroline\AppBundle\Event\Crud\UpdateEvent;
use Claroline\CoreBundle\Entity\User;
use Claroline\AppBundle\Event\CrudEvents;
use Claroline\CoreBundle\Manager\FileManager;
use Claroline\ExampleBundle\Entity\Example;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class ExampleSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly TokenStorageInterface $tokenStorage,
        private readonly FileManager $fileManager
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            CrudEvents::getEventName(CrudEvents::PRE_CREATE, Example::class) => 'preCreate',
            CrudEvents::getEventName(CrudEvents::POST_CREATE, Example::class) => 'postCreate',
            CrudEvents::getEventName(CrudEvents::PRE_UPDATE, Example::class) => 'preUpdate',
            CrudEvents::getEventName(CrudEvents::POST_UPDATE, Example::class) => 'postUpdate',
            CrudEvents::getEventName(CrudEvents::POST_DELETE, Example::class) => 'postDelete',
        ];
    }

    public function preCreate(CreateEvent $event): void
    {
        /** @var Example $example */
        $example = $event->getObject();
        $user = $this->tokenStorage->getToken()?->getUser();

        if ($user instanceof User) {
            $example->setCreator($user);
        }

        $example->setCreatedAt(new \DateTime());
        $example->setUpdatedAt(new \DateTime());
    }

    public function postCreate(CreateEvent $event): void
    {
        /** @var Example $example */
        $example = $event->getObject();

        if ($example->getPoster()) {
            $this->fileManager->linkFile(Example::class, $example->getUuid(), $example->getPoster());
        }

        if ($example->getThumbnail()) {
            $this->fileManager->linkFile(Example::class, $example->getUuid(), $example->getThumbnail());
        }
    }

    public function preUpdate(UpdateEvent $event): void
    {
        /** @var Example $example */
        $example = $event->getObject();

        $example->setUpdatedAt(new \DateTime());
    }

    public function postUpdate(UpdateEvent $event): void
    {
        /** @var Example $example */
        $example = $event->getObject();
        $oldData = $event->getOldData();

        // update poster if it has changed
        $this->fileManager->updateFile(
            Example::class,
            $example->getUuid(),
            $example->getPoster(),
            !empty($oldData['poster']) ? $oldData['poster'] : null
        );

        // update poster if it has changed
        $this->fileManager->updateFile(
            Example::class,
            $example->getUuid(),
            $example->getThumbnail(),
            !empty($oldData['thumbnail']) ? $oldData['thumbnail'] : null
        );
    }

    public function postDelete(DeleteEvent $event): void
    {
        /** @var Example $example */
        $example = $event->getObject();

        if ($example->getPoster()) {
            $this->fileManager->unlinkFile(Example::class, $example->getUuid(), $example->getPoster());
        }

        if ($example->getThumbnail()) {
            $this->fileManager->unlinkFile(Example::class, $example->getUuid(), $example->getThumbnail());
        }
    }
}
