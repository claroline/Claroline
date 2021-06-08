<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CursusBundle\Subscriber\Crud;

use Claroline\AppBundle\Event\Crud\CreateEvent;
use Claroline\AppBundle\Event\Crud\DeleteEvent;
use Claroline\AppBundle\Event\Crud\UpdateEvent;
use Claroline\CoreBundle\Subscriber\Crud\Planning\AbstractPlannedSubscriber;
use Claroline\CursusBundle\Entity\Event;
use Claroline\CursusBundle\Event\Log\LogSessionEventCreateEvent;
use Claroline\CursusBundle\Event\Log\LogSessionEventDeleteEvent;
use Claroline\CursusBundle\Event\Log\LogSessionEventEditEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class EventSubscriber extends AbstractPlannedSubscriber
{
    /** @var EventDispatcherInterface */
    private $eventDispatcher;

    public function __construct(
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->eventDispatcher = $eventDispatcher;
    }

    public static function getPlannedClass(): string
    {
        return Event::class;
    }

    public function preCreate(CreateEvent $event)
    {
        parent::preCreate($event);

        /** @var Event $object */
        $object = $event->getObject();

        // add event to session and workspace planning
        if ($object->getSession()) {
            $this->planningManager->addToPlanning($object, $object->getSession());

            if ($object->getSession()->getWorkspace()) {
                $this->planningManager->addToPlanning($object, $object->getSession()->getWorkspace());
            }
        }
    }

    public function postCreate(CreateEvent $event)
    {
        $this->eventDispatcher->dispatch(new LogSessionEventCreateEvent($event->getObject()), 'log');
    }

    public function postUpdate(UpdateEvent $event)
    {
        $this->eventDispatcher->dispatch(new LogSessionEventEditEvent($event->getObject()), 'log');
    }

    public function preDelete(DeleteEvent $event)
    {
        $this->eventDispatcher->dispatch(new LogSessionEventDeleteEvent($event->getObject()), 'log');
    }
}
