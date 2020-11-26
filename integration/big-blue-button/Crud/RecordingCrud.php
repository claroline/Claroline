<?php

namespace Claroline\BigBlueButtonBundle\Crud;

use Claroline\AppBundle\Event\Crud\DeleteEvent;
use Claroline\BigBlueButtonBundle\Entity\Recording;
use Claroline\BigBlueButtonBundle\Manager\BBBManager;

class RecordingCrud
{
    private $bbbManager;

    public function __construct(BBBManager $bbbManager)
    {
        $this->bbbManager = $bbbManager;
    }

    public function preDelete(DeleteEvent $event)
    {
        /** @var Recording */
        $recording = $event->getObject();

        // delete recording from bbb server
        $this->bbbManager->deleteRecording($recording);
    }
}
