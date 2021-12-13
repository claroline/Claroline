<?php

namespace Claroline\DropZoneBundle\Crud;

use Claroline\AppBundle\Event\Crud\UpdateEvent;
use Claroline\DropZoneBundle\Manager\DropzoneManager;

class Dropzone
{
    /** @var DropzoneManager */
    private $dropzoneManager;

    public function __construct(DropzoneManager $dropzoneManager)
    {
        $this->dropzoneManager = $dropzoneManager;
    }

    public function endUpdate(UpdateEvent $event)
    {
        $dropzone = $event->getObject();
        $oldData = $event->getOldData();

        if ($oldData['parameters']['scoreMax'] !== $dropzone->getScoreMax()) {
            $this->dropzoneManager->updateScoreByScoreMax($dropzone, $oldData['parameters']['scoreMax'], $dropzone->getScoreMax());
        }
    }
}
