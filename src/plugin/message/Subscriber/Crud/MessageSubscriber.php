<?php

namespace Claroline\MessageBundle\Subscriber\Crud;

use Claroline\AppBundle\API\Crud;
use Claroline\AppBundle\Event\Crud\CreateEvent;
use Claroline\CoreBundle\Entity\User;
use Claroline\MessageBundle\Entity\Message;
use Claroline\MessageBundle\Manager\MessageManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class MessageSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly TokenStorageInterface $tokenStorage,
        private readonly MessageManager $manager
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            Crud::getEventName('create', 'pre', Message::class) => 'preCreate',
            Crud::getEventName('create', 'post', Message::class) => 'postCreate',
        ];
    }

    public function preCreate(CreateEvent $event): void
    {
        /** @var Message $message */
        $message = $event->getObject();
        $currentUser = $this->tokenStorage->getToken()->getUser();

        if ($currentUser instanceof User) {
            $message->setSender($currentUser);
        }
    }

    public function postCreate(CreateEvent $event): void
    {
        $this->manager->send($event->getObject());
    }
}
