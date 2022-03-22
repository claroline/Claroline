<?php

namespace Claroline\AnnouncementBundle\Messenger\Message;

use Claroline\AppBundle\Messenger\Message\AsyncMessageInterface;

class SendAnnouncement implements AsyncMessageInterface
{
    /** @var string */
    private $content;
    /** @var string */
    private $object;
    /** @var int[] */
    private $receiverIds;
    /** @var int */
    private $announcementId;
    /** @var int|null */
    private $senderId;

    public function __construct(
        string $content,
        string $object,
        array $receiverIds,
        int $announcementId,
        ?int $senderId = null
    ) {
        $this->content = $content;
        $this->object = $object;
        $this->receiverIds = $receiverIds;
        $this->announcementId = $announcementId;
        $this->senderId = $senderId;
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
