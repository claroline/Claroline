<?php

namespace Claroline\MessageBundle\Crud;

use Claroline\AppBundle\Event\Crud\CreateEvent;
use Claroline\CoreBundle\Entity\User;
use Claroline\MessageBundle\Entity\Message;
use Claroline\MessageBundle\Manager\MessageManager;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class MessageCrud
{
    /** @var TokenStorageInterface */
    private $tokenStorage;

    /**
     * @param TokenStorageInterface $tokenStorage
     * @param MessageManager        $manager
     */
    public function __construct(
        TokenStorageInterface $tokenStorage,
        MessageManager $manager
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->manager = $manager;
    }

    public function preCreate(CreateEvent $event)
    {
        /** @var Message $message */
        $message = $event->getObject();
        $currentUser = $this->tokenStorage->getToken()->getUser();

        if ($currentUser instanceof User) {
            $message->setSender($currentUser);
        }
    }

    /**
     * @param CreateEvent $event
     */
    public function postCreate(CreateEvent $event)
    {
        $this->manager->send($event->getObject());
    }
}
