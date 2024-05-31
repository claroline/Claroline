<?php

namespace Claroline\DropZoneBundle\Subscriber\Crud;

use Claroline\AppBundle\Event\Crud\UpdateEvent;
use Claroline\AppBundle\Event\CrudEvents;
use Claroline\DropZoneBundle\Entity\Dropzone;
use Claroline\DropZoneBundle\Manager\DropzoneManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class DropzoneSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly DropzoneManager $dropzoneManager
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            CrudEvents::getEventName(CrudEvents::POST_UPDATE, Dropzone::class) => 'postUpdate',
        ];
    }

    public function postUpdate(UpdateEvent $event): void
    {
        $dropzone = $event->getObject();
        $oldData = $event->getOldData();

        if ($oldData['parameters']['scoreMax'] !== $dropzone->getScoreMax()) {
            $this->dropzoneManager->updateScoreByScoreMax($dropzone, $oldData['parameters']['scoreMax'], $dropzone->getScoreMax());
        }
    }
}
