<?php

namespace Claroline\AnnouncementBundle\Crud;

use Claroline\AppBundle\API\Crud;
use Claroline\AppBundle\Event\Crud\DeleteEvent;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.crud.announcement_aggregate")
 * @DI\Tag("claroline.crud")
 */
class AnnouncementAggregateCrud
{
    /**
     * @DI\InjectParams({
     *     "crud"  = @DI\Inject("claroline.api.crud")
     * })
     */
    public function __construct(Crud $crud)
    {
        $this->crud = $crud;
    }

    /**
     * @DI\Observe("crud_pre_delete_object_claroline_announcementbundle_entity_announcementaggregate")
     *
     * @param CreateEvent $event
     */
    public function preDelete(DeleteEvent $event)
    {
        $aggregate = $event->getObject();

        foreach ($aggregate->getAnnouncements() as $announcement) {
            $this->crud->delete($announcement);
        }
    }
}
