<?php

namespace Claroline\AnnouncementBundle\Messenger\Message;

use Claroline\CoreBundle\Entity\User;

class SendAnnouncement
{
    private $content;
    private $object;
    private $receivers;
    private $announcementId;
    private $sender;

    public function __construct(
        string $content,
        string $object,
        array $receivers,
        int $announcementId,
        ?User $sender = null
    ) {
        $this->content = $content;
        $this->object = $object;
        $this->receivers = $receivers;
        $this->announcementId = $announcementId;
        $this->sender = $sender;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function getObject(): string
    {
        return $this->object;
    }

    public function getReceivers(): array
    {
        return $this->receivers;
    }

    public function getSender(): ?User
    {
        return $this->sender;
    }

    public function getAnnouncementId(): int
    {
        return $this->announcementId;
    }
}
