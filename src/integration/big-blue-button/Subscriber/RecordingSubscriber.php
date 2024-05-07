<?php

namespace Claroline\BigBlueButtonBundle\Subscriber;

use Claroline\AppBundle\API\Crud;
use Claroline\AppBundle\Event\Crud\DeleteEvent;
use Claroline\BigBlueButtonBundle\Entity\Recording;
use Claroline\BigBlueButtonBundle\Manager\BBBManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class RecordingSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly BBBManager $bbbManager
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            Crud::getEventName('delete', 'post', Recording::class) => 'postDelete',
        ];
    }

    public function postDelete(DeleteEvent $event): void
    {
        /** @var Recording $recording */
        $recording = $event->getObject();

        // delete recording from bbb server
        $this->bbbManager->deleteRecording($recording);
    }
}
