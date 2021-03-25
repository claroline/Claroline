<?php

namespace Claroline\AnnouncementBundle\Crud;

use Claroline\AnnouncementBundle\Entity\AnnouncementSend;
use Claroline\AnnouncementBundle\Manager\AnnouncementManager;
use Claroline\AppBundle\Event\Crud\CreateEvent;
use Claroline\AppBundle\Event\Crud\DeleteEvent;
use Claroline\AppBundle\Persistence\ObjectManager;

class AnnouncementCrud
{
    /** @var AnnouncementManager */
    private $manager;
    /** @var ObjectManager */
    private $om;

    /**
     * AnnouncementCrud constructor.
     */
    public function __construct(
        ObjectManager $om,
        AnnouncementManager $manager
    ) {
        $this->manager = $manager;
        $this->om = $om;
    }

    public function preCreate(CreateEvent $event)
    {
        $announcement = $event->getObject();
        $options = $event->getOptions();
        $announcement->setAggregate($options['announcement_aggregate']);
    }

    public function preDelete(DeleteEvent $event)
    {
        $announcement = $event->getObject();
        $send = $this->om->getRepository(AnnouncementSend::class)->findBy(['announcement' => $announcement]);

        foreach ($send as $el) {
            $this->om->remove($el);
        }

        // delete scheduled task is any
        $this->manager->unscheduleMessage($announcement);
    }
}
