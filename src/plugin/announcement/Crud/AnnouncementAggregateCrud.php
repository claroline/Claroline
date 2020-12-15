<?php

namespace Claroline\AnnouncementBundle\Crud;

use Claroline\AppBundle\API\Crud;
use Claroline\AppBundle\Event\Crud\DeleteEvent;

class AnnouncementAggregateCrud
{
    /** @var Crud */
    private $crud;

    /**
     * AnnouncementAggregateCrud constructor.
     *
     * @param Crud $crud
     */
    public function __construct(Crud $crud)
    {
        $this->crud = $crud;
    }

    /**
     * @param DeleteEvent $event
     */
    public function preDelete(DeleteEvent $event)
    {
        $aggregate = $event->getObject();

        foreach ($aggregate->getAnnouncements() as $announcement) {
            $this->crud->delete($announcement);
        }
    }
}
