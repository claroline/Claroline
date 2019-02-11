<?php

namespace Claroline\AnnouncementBundle\Crud;

use Claroline\AnnouncementBundle\Entity\AnnouncementSend;
use Claroline\AnnouncementBundle\Manager\AnnouncementManager;
use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\Event\Crud\CreateEvent;
use Claroline\AppBundle\Event\Crud\DeleteEvent;
use Claroline\AppBundle\Event\Crud\UpdateEvent;
use Claroline\AppBundle\Event\StrictDispatcher;
use Claroline\AppBundle\Persistence\ObjectManager;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.crud.announcement")
 * @DI\Tag("claroline.crud")
 */
class AnnouncementCrud
{
    /**
     * AnnouncementManager constructor.
     *
     * @DI\InjectParams({
     *     "om"              = @DI\Inject("claroline.persistence.object_manager"),
     *     "eventDispatcher" = @DI\Inject("claroline.event.event_dispatcher"),
     *     "manager"         = @DI\Inject("claroline.manager.announcement_manager")
     * })
     *
     * @param StrictDispatcher $eventDispatcher
     */
    public function __construct(
        ObjectManager $om,
        StrictDispatcher $eventDispatcher,
        AnnouncementManager $manager
    ) {
        $this->eventDispatcher = $eventDispatcher;
        $this->manager = $manager;
        $this->om = $om;
    }

    /**
     * @DI\Observe("crud_pre_create_object_claroline_announcementbundle_entity_announcement")
     *
     * @param CreateEvent $event
     */
    public function preCreate(CreateEvent $event)
    {
        $announcement = $event->getObject();
        $options = $event->getOptions();
        $announcement->setAggregate($options['announcement_aggregate']);

        if (!in_array(Options::NO_LOG, $options)) {
            $this->eventDispatcher->dispatch(
                'log',
                'Claroline\\AnnouncementBundle\\Event\\Log\\LogAnnouncementCreateEvent',
                [$announcement->getAggregate(), $announcement]
            );
        }
    }

    /**
     * @DI\Observe("crud_post_update_object_claroline_announcementbundle_entity_announcement")
     *
     * @param CreateEvent $event
     */
    public function postUpdate(UpdateEvent $event)
    {
        $announcement = $event->getObject();

        $this->eventDispatcher->dispatch(
            'log',
            'Claroline\\AnnouncementBundle\\Event\\Log\\LogAnnouncementEditEvent',
            [$announcement->getAggregate(), $announcement]
        );
    }

    /**
     * @DI\Observe("crud_pre_delete_object_claroline_announcementbundle_entity_announcement")
     *
     * @param CreateEvent $event
     */
    public function preDelete(DeleteEvent $event)
    {
        $announcement = $event->getObject();
        $send = $this->om->getRepository(AnnouncementSend::class)->findBy(['announcement' => $announcement]);

        foreach ($send as $el) {
            $this->om->remove($el);
        }

        // delete scheduled task is any
        $this->manager->unscheduleMessage($announcement);

        $this->eventDispatcher->dispatch(
            'log',
            'Claroline\\AnnouncementBundle\\Event\\Log\\LogAnnouncementDeleteEvent',
            [$announcement->getAggregate(), $announcement]
        );
    }
}
