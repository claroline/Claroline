<?php

namespace Claroline\LogBundle\Entity\Archive;

use Claroline\LogBundle\Entity\AbstractLog;
use Claroline\LogBundle\Entity\MessageLog;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(null)
 */
class MessageLogArchive extends AbstractLog
{
    public const ARCHIVE_TABLE_PREFIX = 'claro_log_message_archive_';

    /**
     * @var int|null
     * @ORM\Column(type="integer", nullable=true)
     */
    private $senderId;

    /**
     * @var string|null
     * @ORM\Column(type="string", nullable=true)
     */
    private $senderUuid;

    /**
     * @var string|null
     * @ORM\Column(type="string", nullable=true)
     */
    private $senderUsername;

    /**
     * @var int|null
     * @ORM\Column(type="integer", nullable=true)
     */
    private $receiverId;

    /**
     * @var string|null
     * @ORM\Column(type="string", nullable=true)
     */
    private $receiverUuid;

    /**
     * @var string|null
     * @ORM\Column(type="string")
     */
    private $receiverUsername;

    public static function fromMessageLog(MessageLog $messageLog)
    {
        $sender = $messageLog->getSender();
        $receiver = $messageLog->getReceiver();

        $archive = new self();
        $archive->setDate($messageLog->getDate());
        $archive->setDetails($messageLog->getDetails());
        $archive->setEvent($messageLog->getEvent());
        $archive->setSenderId($sender->getId());
        $archive->setSenderUuid($sender->getUuid());
        $archive->setSenderUsername($sender->getUsername());
        $archive->setReceiverId($receiver->getId());
        $archive->setReceiverUuid($receiver->getUuid());
        $archive->setReceiverUsername($receiver->getUsername());

        return $archive;
    }

    public function getSenderId(): ?int
    {
        return $this->senderId;
    }

    public function setSenderId(?int $senderId): void
    {
        $this->senderId = $senderId;
    }

    public function getSenderUuid(): ?string
    {
        return $this->senderUuid;
    }

    public function setSenderUuid(?string $senderUuid): void
    {
        $this->senderUuid = $senderUuid;
    }

    public function getSenderUsername(): ?string
    {
        return $this->senderUsername;
    }

    public function setSenderUsername(?string $senderUsername): void
    {
        $this->senderUsername = $senderUsername;
    }

    public function getReceiverId(): ?int
    {
        return $this->receiverId;
    }

    public function setReceiverId(?int $receiverId): void
    {
        $this->receiverId = $receiverId;
    }

    public function getReceiverUuid(): ?string
    {
        return $this->receiverUuid;
    }

    public function setReceiverUuid(?string $receiverUuid): void
    {
        $this->receiverUuid = $receiverUuid;
    }

    public function getReceiverUsername(): ?string
    {
        return $this->receiverUsername;
    }

    public function setReceiverUsername(?string $receiverUsername): void
    {
        $this->receiverUsername = $receiverUsername;
    }
}
