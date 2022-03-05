<?php

namespace Claroline\AnnouncementBundle\Subscriber\Crud;

use Claroline\AnnouncementBundle\Entity\AnnouncementAggregate;
use Claroline\AppBundle\API\Crud;
use Claroline\AppBundle\Event\Crud\DeleteEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class AnnouncementAggregateSubscriber implements EventSubscriberInterface
{
    /** @var Crud */
    private $crud;

    public function __construct(Crud $crud)
    {
        $this->crud = $crud;
    }

    public static function getSubscribedEvents()
    {
        return [
            Crud::getEventName('delete', 'pre', AnnouncementAggregate::class) => 'preDelete',
        ];
    }

    public function preDelete(DeleteEvent $event)
    {
        /** @var AnnouncementAggregate $aggregate */
        $aggregate = $event->getObject();

        foreach ($aggregate->getAnnouncements() as $announcement) {
            $this->crud->delete($announcement, $event->getOptions());
        }
    }
}
