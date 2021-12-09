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
     */
    public function __construct(Crud $crud)
    {
        $this->crud = $crud;
    }

    public function preDelete(DeleteEvent $event)
    {
        $aggregate = $event->getObject();

        foreach ($aggregate->getAnnouncements() as $announcement) {
            $this->crud->delete($announcement, $event->getOptions());
        }
    }
}
