<?php

namespace Claroline\DropZoneBundle\Subscriber\Crud;

use Claroline\AppBundle\API\Crud;
use Claroline\AppBundle\Event\Crud\UpdateEvent;
use Claroline\DropZoneBundle\Entity\Dropzone;
use Claroline\DropZoneBundle\Manager\DropzoneManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class DropzoneSubscriber implements EventSubscriberInterface
{
    /** @var DropzoneManager */
    private $dropzoneManager;

    public function __construct(DropzoneManager $dropzoneManager)
    {
        $this->dropzoneManager = $dropzoneManager;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            Crud::getEventName('update', 'post', Dropzone::class) => 'postUpdate',
        ];
    }

    public function postUpdate(UpdateEvent $event)
    {
        $dropzone = $event->getObject();
        $oldData = $event->getOldData();

        if ($oldData['parameters']['scoreMax'] !== $dropzone->getScoreMax()) {
            $this->dropzoneManager->updateScoreByScoreMax($dropzone, $oldData['parameters']['scoreMax'], $dropzone->getScoreMax());
        }
    }
}
