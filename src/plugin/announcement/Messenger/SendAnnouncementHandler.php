<?php

namespace Claroline\AnnouncementBundle\Messenger;

use Claroline\AnnouncementBundle\Entity\Announcement;
use Claroline\AnnouncementBundle\Entity\AnnouncementSend;
use Claroline\AnnouncementBundle\Messenger\Message\SendAnnouncement;
use Claroline\AppBundle\Event\Crud\CreateEvent;
use Claroline\AppBundle\Event\StrictDispatcher;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Event\CatalogEvents\MessageEvents;
use Claroline\CoreBundle\Event\SendMessageEvent;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class SendAnnouncementHandler implements MessageHandlerInterface
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
        $announcement = $this->objectManager->getRepository(Announcement::class)->find($sendAnnouncement->getAnnouncementId());
        if ($announcement) {
            $announcementSend = new AnnouncementSend();
            $announcementSend->setAnnouncement($announcement);
            $announcementSend->setData([
                'sender' => $sendAnnouncement->getSender(),
                'receivers' => $sendAnnouncement->getReceivers(),
                'object' => $sendAnnouncement->getObject(),
                'content' => $sendAnnouncement->getContent(),
            ]);

            $this->objectManager->persist($announcementSend);
            $this->objectManager->flush();

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

            //it's kind of a hack because this is not using the crud... but wathever.
            $this->eventDispatcher->dispatch('crud.post.create.announcement_send', CreateEvent::class, [
                $announcementSend, [], [],
            ]);
        }
    }
}
