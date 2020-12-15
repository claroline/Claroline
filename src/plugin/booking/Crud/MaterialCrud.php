<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\BookingBundle\Crud;

use Claroline\AppBundle\Event\Crud\CreateEvent;
use Claroline\AppBundle\Event\Crud\DeleteEvent;
use Claroline\AppBundle\Event\Crud\UpdateEvent;
use Claroline\BookingBundle\Event\Log\LogMaterialCreateEvent;
use Claroline\BookingBundle\Event\Log\LogMaterialDeleteEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class MaterialCrud
{
    private $eventDispatcher;

    public function __construct(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    public function postCreate(CreateEvent $event)
    {
        $event = new LogMaterialCreateEvent($event->getObject());
        $this->eventDispatcher->dispatch($event, 'log');
    }

    public function postUpdate(UpdateEvent $event)
    {
        $event = new LogMaterialDeleteEvent($event->getObject());
        $this->eventDispatcher->dispatch($event, 'log');
    }

    public function preDelete(DeleteEvent $event)
    {
        $event = new LogMaterialDeleteEvent($event->getObject());
        $this->eventDispatcher->dispatch($event, 'log');
    }
}
