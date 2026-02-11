<?php

namespace Claroline\CursusBundle\Entity;

use Claroline\AppBundle\API\Attribute\CrudEntity;
use Claroline\AppBundle\Entity\Identifier\Id;
use Claroline\AppBundle\Entity\Identifier\Uuid;
use Claroline\AppBundle\Entity\Meta\UpdatedAt;
use Claroline\CoreBundle\Entity\User;
use Claroline\CursusBundle\Finder\EventPresenceType;
use Claroline\CursusBundle\Repository\EventPresenceRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'claro_cursusbundle_presence_status')]
#[ORM\Entity(repositoryClass: EventPresenceRepository::class)]
#[CrudEntity(finderClass: EventPresenceType::class)]
class EventPresence
{
    use Id;
    use Uuid;
    use UpdatedAt;

    public const UNKNOWN = 'unknown';
    public const PRESENT = 'present';
    public const ABSENT_JUSTIFIED = 'absent_justified';
    public const ABSENT_UNJUSTIFIED = 'absent_unjustified';

    #[ORM\ManyToOne(targetEntity: Event::class)]
    #[ORM\JoinColumn(name: 'event_id', nullable: false, onDelete: 'CASCADE')]
    private ?Event $event = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'user_id', nullable: false, onDelete: 'CASCADE')]
    private ?User $user = null;

    #[ORM\Column(name: 'presence_status', nullable: false)]
    private string $status = self::UNKNOWN;

    #[ORM\Column(name: 'presence_signature', nullable: true)]
    private ?string $signature = null;

    #[ORM\Column(name: 'presence_validation_date', type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $validationDate = null;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $evidence = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'updatedBy', nullable: true, onDelete: 'SET NULL')]
    private ?User $updatedBy = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'evidence_added_by', nullable: true, onDelete: 'SET NULL')]
    private ?User $evidenceAddedBy = null;

    #[ORM\Column(name: 'evidence_added_at', type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $evidenceAddedAt = null;

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

    public function getEvidence(): ?array
    {
        return $this->evidence;
    }

    public function setEvidence(?array $evidence): void
    {
        $this->evidence = $evidence;
    }

    public function getUpdatedBy(): ?User
    {
        return $this->updatedBy;
    }

    public function setUpdatedBy(?User $user): self
    {
        $this->updatedBy = $user;

        return $this;
    }

    public function getEvidenceAddedBy(): ?User
    {
        return $this->evidenceAddedBy;
    }

    public function setEvidenceAddedBy(?User $user): self
    {
        $this->evidenceAddedBy = $user;

        return $this;
    }

    public function getEvidenceAddedAt(): ?\DateTimeInterface
    {
        return $this->evidenceAddedAt;
    }

    public function setEvidenceAddedAt(?\DateTimeInterface $date): self
    {
        $this->evidenceAddedAt = $date;

        return $this;
    }
}
