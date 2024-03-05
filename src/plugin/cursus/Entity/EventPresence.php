<?php

namespace Claroline\CursusBundle\Entity;

use Claroline\AppBundle\Entity\Identifier\Id;
use Claroline\AppBundle\Entity\Identifier\Uuid;
use Claroline\CoreBundle\Entity\User;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="claro_cursusbundle_presence_status")
 *
 * @ORM\Entity
 */
class EventPresence
{
    use Id;
    use Uuid;

    public const UNKNOWN = 'unknown';
    public const PRESENT = 'present';
    public const ABSENT_JUSTIFIED = 'absent_justified';
    public const ABSENT_UNJUSTIFIED = 'absent_unjustified';

    /**
     * @ORM\ManyToOne(targetEntity="Claroline\CursusBundle\Entity\Event")
     *
     * @ORM\JoinColumn(name="event_id", nullable=false, onDelete="CASCADE")
     *
     * @var Event
     */
    private $event;

    /**
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\User")
     *
     * @ORM\JoinColumn(name="user_id", nullable=false, onDelete="CASCADE")
     *
     * @var User
     */
    private $user;

    /**
     * @ORM\Column(name="presence_status", nullable=false)
     *
     * @var string
     */
    private $status = self::UNKNOWN;

    /**
     * @ORM\Column(name="presence_signature", nullable=true)
     *
     * @var string
     */
    private $signature;

    /**
     * @ORM\Column(name="presence_validation_date", type="datetime", nullable=true)
     *
     * @var \DateTimeInterface
     */
    private $validationDate;

    public function __construct()
    {
        $this->refreshUuid();
    }

    public function getEvent(): Event
    {
        return $this->event;
    }

    public function setEvent(Event $event): void
    {
        $this->event = $event;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): void
    {
        $this->user = $user;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): void
    {
        $this->status = $status;
    }

    public function getSignature(): ?string
    {
        return $this->signature;
    }

    public function setSignature(?string $signature): void
    {
        $this->signature = $signature;
    }

    public function getValidationDate(): ?\DateTimeInterface
    {
        return $this->validationDate;
    }

    public function setValidationDate(?\DateTimeInterface $validationDate): void
    {
        $this->validationDate = $validationDate;
    }
}
