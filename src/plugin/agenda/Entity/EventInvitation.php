<?php

namespace Claroline\AgendaBundle\Entity;

use Claroline\AppBundle\Entity\Identifier\Id;
use Claroline\CoreBundle\Entity\User;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'claro_event_invitation')]
#[ORM\Entity]
class EventInvitation
{
    use Id;

    public const UNKNOWN = 'unknown';
    public const JOIN = 'join';
    public const MAYBE = 'maybe';
    public const RESIGN = 'resign';

    #[ORM\Column(type: Types::STRING)]
    private string $status = self::UNKNOWN;

    #[ORM\JoinColumn(name: 'event', nullable: false, onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: Event::class, inversedBy: 'eventInvitations')]
    private ?Event $event = null;

    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: User::class)]
    private ?User $user = null;

    public function __construct(Event $event, User $user)
    {
        $this->event = $event;
        $this->user = $user;
    }

    public function setEvent(Event $event): void
    {
        $this->event = $event;
    }

    public function getEvent(): Event
    {
        return $this->event;
    }

    public function setUser(User $user): void
    {
        $this->user = $user;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setStatus(string $status): void
    {
        $this->status = $status;
    }

    public function getStatus(): string
    {
        return $this->status;
    }
}
