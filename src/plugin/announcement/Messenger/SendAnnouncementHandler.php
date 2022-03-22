<?php

namespace Claroline\AnnouncementBundle\Messenger;

use Claroline\AnnouncementBundle\Entity\Announcement;
use Claroline\AnnouncementBundle\Entity\AnnouncementSend;
use Claroline\AnnouncementBundle\Messenger\Message\SendAnnouncement;
use Claroline\AppBundle\Event\StrictDispatcher;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\User;
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
        if (empty($announcement)) {
            return;
        }

        $receivers = [];
        foreach ($sendAnnouncement->getReceiverIds() as $receiverId) {
            $receiver = $this->objectManager->getRepository(User::class)->find($receiverId);
            if (!empty($receiver)) {
                $receivers[] = $receiver;
            }
        }

        if (empty($receivers)) {
            return;
        }

        $sender = null;
        if (!empty($sendAnnouncement->getSenderId())) {
            $sender = $this->objectManager->getRepository(User::class)->find($sendAnnouncement->getSenderId());
        }

        $announcementSend = new AnnouncementSend();
        $announcementSend->setAnnouncement($announcement);
        $announcementSend->setData([
            'sender' => $sender,
            'receivers' => $receivers,
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
                $receivers,
                $sender,
            ]
        );
    }
}
