<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\AnnouncementBundle\Listener;

use Claroline\AnnouncementBundle\Entity\Announcement;
use Claroline\AnnouncementBundle\Event\Log\LogAnnouncementEvent;
use Claroline\AppBundle\Event\Crud\CreateEvent;
use Claroline\AppBundle\Event\Crud\DeleteEvent;
use Claroline\AppBundle\Event\Crud\UpdateEvent;
use Claroline\AppBundle\Event\StrictDispatcher;

class CrudListener
{
    public function __construct(StrictDispatcher $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    public function onAnnouncementCreate(CreateEvent $event)
    {
        $announcement = $event->getObject();

        $this->dispatchAnnouncementEvent($announcement, 'announcement-create');
    }

    public function onAnnouncementSend(CreateEvent $event)
    {
        $announcement = $event->getObject()->getAnnouncement();

        $this->dispatchAnnouncementEvent($announcement, 'announcement-send');
    }

    public function onAnnouncementUpdate(UpdateEvent $event)
    {
        $announcement = $event->getObject();

        $this->dispatchAnnouncementEvent($announcement, 'announcement-update');
    }

    public function onAnnouncementDelete(DeleteEvent $event)
    {
        $announcement = $event->getObject();

        $this->dispatchAnnouncementEvent($announcement, 'announcement-delete');
    }

    private function dispatchAnnouncementEvent(Announcement $announcement, $action)
    {
        $this->dispatcher->dispatch('log', LogAnnouncementEvent::class, [$announcement, $action]);
    }
}
