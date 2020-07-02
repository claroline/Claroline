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
use Claroline\CursusBundle\Event\Log\LogCourseCreateEvent;
use Claroline\CursusBundle\Event\Log\LogCourseDeleteEvent;
use Claroline\CursusBundle\Event\Log\LogCourseEditEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class CourseCrud
{
    private $eventDispatcher;

    /**
     * CourseCrud constructor.
     *
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @param CreateEvent $event
     */
    public function postCreate(CreateEvent $event)
    {
        $event = new LogCourseCreateEvent($event->getObject());
        $this->eventDispatcher->dispatch('log', $event);
    }

    /**
     * @param UpdateEvent $event
     */
    public function postUpdate(UpdateEvent $event)
    {
        $event = new LogCourseEditEvent($event->getObject());
        $this->eventDispatcher->dispatch('log', $event);
    }

    /**
     * @param DeleteEvent $event
     */
    public function preDelete(DeleteEvent $event)
    {
        $event = new LogCourseDeleteEvent($event->getObject());
        $this->eventDispatcher->dispatch('log', $event);
    }
}
