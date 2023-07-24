<?php

namespace Claroline\ExampleBundle\Subscriber\Crud;

use Claroline\AppBundle\API\Crud;
use Claroline\AppBundle\Event\Crud\CreateEvent;
use Claroline\AppBundle\Event\Crud\DeleteEvent;
use Claroline\AppBundle\Event\Crud\UpdateEvent;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Manager\FileManager;
use Claroline\ExampleBundle\Entity\Example;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class ExampleSubscriber implements EventSubscriberInterface
{
    private TokenStorageInterface $tokenStorage;
    private FileManager $fileManager;

    public function __construct(
        TokenStorageInterface $tokenStorage,
        FileManager $fileManager
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->fileManager = $fileManager;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            Crud::getEventName('create', 'pre', Example::class) => 'preCreate',
            Crud::getEventName('create', 'post', Example::class) => 'postCreate',
            Crud::getEventName('update', 'pre', Example::class) => 'preUpdate',
            Crud::getEventName('update', 'post', Example::class) => 'postUpdate',
            Crud::getEventName('delete', 'post', Example::class) => 'postDelete',
        ];
    }

    public function preCreate(CreateEvent $event): void
    {
        /** @var Example $example */
        $example = $event->getObject();
        $user = $this->tokenStorage->getToken()->getUser();

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
