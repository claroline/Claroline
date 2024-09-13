<?php

namespace Claroline\AnnouncementBundle\Messenger\Message;

use Claroline\AppBundle\Messenger\Message\AsyncHighMessageInterface;

final class SendAnnouncement implements AsyncHighMessageInterface
{
    public function __construct(
        private readonly string $content,
        private readonly string $object,
        private readonly array $receiverIds,
        private readonly int $announcementId,
        private readonly ?int $senderId = null
    ) {
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function getObject(): string
    {
        return $this->object;
    }

    public function getReceiverIds(): array
    {
        return $this->receiverIds;
    }

    public function getSenderId(): ?int
    {
        return $this->senderId;
    }

    public function getAnnouncementId(): int
    {
        return $this->announcementId;
    }
}
