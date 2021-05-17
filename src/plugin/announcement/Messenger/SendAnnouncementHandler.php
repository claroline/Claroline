<?php

namespace Claroline\AnnouncementBundle\Messenger;

use Claroline\AnnouncementBundle\Entity\AnnouncementSend;
use Claroline\AnnouncementBundle\Messenger\Message\SendAnnouncement;
use Claroline\AppBundle\Event\StrictDispatcher;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Event\CatalogEvents\MessageEvents;
use Claroline\CoreBundle\Event\SendMessageEvent;

class SendAnnouncementHandler
{
    private $objectManager;
    private $eventDispatcher;

    public function __construct(ObjectManager $objectManager, StrictDispatcher $eventDispatcher)
    {
        $this->objectManager = $objectManager;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function __invoke(SendAnnouncement $sendAnnouncement)
    {
        $this->eventDispatcher->dispatch(
            MessageEvents::MESSAGE_SENDING,
            SendMessageEvent::class,
            [
                $sendAnnouncement->getContent(),
                $sendAnnouncement->getObject(),
                $sendAnnouncement->getReceivers(),
                $sendAnnouncement->getSender(),
            ]
        );

        $announcementSend = $this->objectManager->getRepository(AnnouncementSend::class)->find($sendAnnouncement->getAnnouncementId());

        //it's kind of a hack because this is not using the crud... but wathever.
        $this->eventDispatcher->dispatch('crud.post.create.announcement_send', 'Claroline\\AppBundle\\Event\\Crud\\CreateEvent', [
            $announcementSend, [], [],
        ]);
    }
}
