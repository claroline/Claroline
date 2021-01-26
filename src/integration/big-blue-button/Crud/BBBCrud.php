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

    public function postUpdate(UpdateEvent $event)
    {
        /** @var BBB */
        $bbb = $event->getObject();

        // close the room to recreate it with new params
        $this->bbbManager->endMeeting($bbb);
    }

    public function postDelete(DeleteEvent $event)
    {
        /** @var BBB */
        $bbb = $event->getObject();

        // close the room
        $this->bbbManager->endMeeting($bbb);
    }
}
