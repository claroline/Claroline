<?php

namespace Claroline\AnnouncementBundle\Messenger;

use Claroline\AnnouncementBundle\Entity\Announcement;
use Claroline\AnnouncementBundle\Entity\AnnouncementSend;
use Claroline\AnnouncementBundle\Messenger\Message\SendAnnouncement;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Event\CatalogEvents\MessageEvents;
use Claroline\CoreBundle\Event\SendMessageEvent;
use Claroline\CoreBundle\Library\RoutingHelper;
use Claroline\CoreBundle\Manager\Template\TemplateManager;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class SendAnnouncementHandler implements MessageHandlerInterface
{
    public function __construct(
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly RoutingHelper $routing,
        private readonly ObjectManager $objectManager,
        private readonly TemplateManager $templateManager
    ) {
    }

    public function __invoke(SendAnnouncement $sendAnnouncement): void
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

        $workspace = $announcement->getAggregate()->getResourceNode()->getWorkspace();
        $publicationDate = $announcement->getPublicationDate() ?? $announcement->getCreationDate();

        $placeholders = array_merge([
            'title' => $announcement->getTitle(),
            'content' => $announcement->getContent(),
            'author' => $announcement->getAnnouncer() ?: $announcement->getCreator()->getFullName(),
            'workspace_name' => $workspace->getName(),
            'workspace_code' => $workspace->getCode(),
            'workspace_url' => $this->routing->workspaceUrl($workspace),
            ], $this->templateManager->formatDatePlaceholder('publication', $publicationDate)
        );

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

        foreach ($receivers as $receiver) {
            if ($announcement->getAggregate()->getTemplateEmail()) {
                $title = $this->templateManager->getTemplateContent($announcement->getAggregate()->getTemplateEmail(), $placeholders, $receiver->getLocale(), 'title');
                $content = $this->templateManager->getTemplateContent($announcement->getAggregate()->getTemplateEmail(), $placeholders, $receiver->getLocale());
            } else {
                $title = $this->templateManager->getTemplate('email_announcement', $placeholders, $receiver->getLocale(), 'title');
                $content = $this->templateManager->getTemplate('email_announcement', $placeholders, $receiver->getLocale());
            }

            $event = new SendMessageEvent($content, $title, [$receiver], $sender);
            $this->eventDispatcher->dispatch($event, MessageEvents::MESSAGE_SENDING);
        }
    }
}
