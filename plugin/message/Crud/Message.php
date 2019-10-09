<?php

namespace Claroline\MessageBundle\Crud;

use Claroline\AppBundle\Event\Crud\CreateEvent;
use Claroline\MessageBundle\Manager\MessageManager;

class Message
{
    /**
     * @param MessageManager $manager
     */
    public function __construct(MessageManager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * @param CreateEvent $event
     */
    public function postCreate(CreateEvent $event)
    {
        $this->manager->send($event->getObject());
    }
}
