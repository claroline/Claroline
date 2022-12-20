<?php

namespace Claroline\AgendaBundle\Subscriber\Crud;

use Claroline\AgendaBundle\Entity\Event;
use Claroline\AppBundle\Event\Crud\CreateEvent;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Subscriber\Crud\Planning\AbstractPlannedSubscriber;

class EventSubscriber extends AbstractPlannedSubscriber
{
    public static function getPlannedClass(): string
    {
        return Event::class;
    }

    public function preCreate(CreateEvent $event): void
    {
        parent::preCreate($event);

        /** @var Event $object */
        $object = $event->getObject();
        $user = $this->tokenStorage->getToken()->getUser();

        if (!empty($object->getWorkspace())) {
            // add event to workspace planning
            $this->planningManager->addToPlanning($object, $object->getWorkspace());
        } elseif ($user instanceof User) {
            // add event to user planning
            $this->planningManager->addToPlanning($object, $user);
        }
    }
}
