<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CursusBundle\Crud;

use Claroline\AppBundle\Event\Crud\CreateEvent;
use Claroline\AppBundle\Event\Crud\DeleteEvent;
use Claroline\AppBundle\Event\Crud\UpdateEvent;
use Claroline\CursusBundle\Event\Log\LogSessionEventCreateEvent;
use Claroline\CursusBundle\Event\Log\LogSessionEventDeleteEvent;
use Claroline\CursusBundle\Event\Log\LogSessionEventEditEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class EventCrud
{
    private $eventDispatcher;

    public function __construct(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
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
