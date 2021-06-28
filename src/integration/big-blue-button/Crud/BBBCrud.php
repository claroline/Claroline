<?php

namespace Claroline\BigBlueButtonBundle\Crud;

use Claroline\AppBundle\Event\Crud\DeleteEvent;
use Claroline\AppBundle\Event\Crud\UpdateEvent;
use Claroline\BigBlueButtonBundle\Entity\BBB;
use Claroline\BigBlueButtonBundle\Manager\BBBManager;

class BBBCrud
{
    /** @var BBBManager */
    private $bbbManager;

    public function __construct(BBBManager $bbbManager)
    {
        $this->bbbManager = $bbbManager;
    }

    public function preUpdate(UpdateEvent $event)
    {
        /** @var BBB $bbb */
        $bbb = $event->getObject();
        if ($bbb->getRunningOn() && $bbb->getServer() && ($bbb->getRunningOn() !== $bbb->getServer())) {
            // we want to force a server for this room, we reinitialize attributed server to move the room
            $bbb->setRunningOn(null);
        }
    }

    public function postUpdate(UpdateEvent $event)
    {
        /** @var BBB $bbb */
        $bbb = $event->getObject();

        $oldData = $event->getOldData();

        if (!empty($oldData['runningOn'])) {
            // room has already been created
            // close the room to recreate it with new params
            $this->bbbManager->endMeeting($bbb, $oldData['runningOn']);
        }
    }

    public function postDelete(DeleteEvent $event)
    {
        /** @var BBB */
        $bbb = $event->getObject();

        // close the room
        $this->bbbManager->endMeeting($bbb);
    }
}
