<?php

namespace Claroline\AnnouncementBundle\Messenger;

use Claroline\AnnouncementBundle\Entity\Announcement;
use Claroline\AnnouncementBundle\Entity\AnnouncementSend;
use Claroline\AnnouncementBundle\Messenger\Message\SendAnnouncement;
use Claroline\AppBundle\Event\Crud\CreateEvent;
use Claroline\AppBundle\Event\StrictDispatcher;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\User;
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
        $data = [
            'sender' => $sendAnnouncement->getSender(),
            'receivers' => $sendAnnouncement->getReceivers(),
            'object' => $sendAnnouncement->getObject(),
            'content' => $sendAnnouncement->getContent(),
        ];
        $announcement = $this->objectManager->getRepository(Announcement::class)->find($sendAnnouncement->getAnnouncementId());

        $announcementSend = new AnnouncementSend();

        $data['receivers'] = array_map(function (User $receiver) {
            return $receiver->getUsername();
        }, $sendAnnouncement->getReceivers());
        $data['sender'] = $sendAnnouncement->getSender()->getUsername();
        $announcementSend->setAnnouncement($announcement);
        $announcementSend->setData($data);
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
