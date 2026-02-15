<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CursusBundle\Entity;

use Claroline\AppBundle\API\Attribute\CrudEntity;
use Claroline\AppBundle\Entity\IdentifiableInterface;
use Claroline\CoreBundle\Entity\Location;
use Claroline\CursusBundle\Finder\SessionType;
use Claroline\CursusBundle\Repository\SessionRepository;
use Claroline\TemplateBundle\Entity\Template;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'claro_cursusbundle_course_session')]
#[ORM\Entity(repositoryClass: SessionRepository::class)]
#[CrudEntity(finderClass: SessionType::class)]
class Session extends AbstractTraining implements IdentifiableInterface
{
    public const REGISTRATION_AUTO = 0;
    public const REGISTRATION_MANUAL = 1;
    public const REGISTRATION_PUBLIC = 2;

    #[ORM\JoinColumn(name: 'course_id', nullable: false, onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: Course::class, inversedBy: 'sessions')]
    private ?Course $course = null;

    #[ORM\Column(name: 'default_session', type: Types::BOOLEAN)]
    private bool $defaultSession = false;

    #[ORM\Column(name: 'max_users', type: Types::INTEGER, nullable: true)]
    private ?int $maxUsers = null;

    #[ORM\Column(name: 'start_date', type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $startDate = null;

    #[ORM\Column(name: 'end_date', type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $endDate = null;

    #[ORM\ManyToOne(targetEntity: Location::class)]
    #[ORM\JoinColumn(name: 'location_id', nullable: true, onDelete: 'SET NULL')]
    private ?Location $location = null;

    /**
     * @var Collection<int, Event>
     */
    #[ORM\OneToMany(targetEntity: Event::class, mappedBy: 'session')]
    private Collection $events;

    #[ORM\Column(name: 'event_registration_type', type: Types::INTEGER, nullable: false, options: ['default' => 0])]
    private int $eventRegistrationType = self::REGISTRATION_AUTO;

    #[ORM\ManyToOne(targetEntity: Template::class)]
    #[ORM\JoinColumn(name: 'invitation_template_id', nullable: true, onDelete: 'SET NULL')]
    private ?Template $invitationTemplate = null;

    #[ORM\Column(type: Types::BOOLEAN)]
    private bool $canceled = false;

    #[ORM\Column(name: 'cancel_reason', type: Types::TEXT, nullable: true)]
    private ?string $cancelReason = null;

    #[ORM\ManyToOne(targetEntity: Template::class)]
    #[ORM\JoinColumn(name: 'canceled_template_id', nullable: true, onDelete: 'SET NULL')]
    private ?Template $canceledTemplate = null;

    public function __construct()
    {
        $this->refreshUuid();

        $this->events = new ArrayCollection();
    }

    public function getName(): ?string
    {
        return $this->course?->getName();
    }

    public function getPoster(): ?string
    {
        return $this->course?->getPoster();
    }

    public function getPlainDescription(): ?string
    {
        return $this->course?->getPlainDescription();
    }

    public function getCourse(): ?Course
    {
        return $this->course;
    }

    public function setCourse(Course $course): void
    {
        $this->course = $course;
    }

    public function isDefaultSession(): bool
    {
        return $this->defaultSession;
    }

    public function setDefaultSession(bool $defaultSession): void
    {
        $this->defaultSession = $defaultSession;
    }

    public function getMaxUsers(): ?int
    {
        return $this->maxUsers;
    }

    public function setMaxUsers(?int $maxUsers): void
    {
        $this->maxUsers = $maxUsers;
    }

    public function getStartDate(): ?\DateTimeInterface
    {
        return $this->startDate;
    }

    public function setStartDate(?\DateTimeInterface $startDate = null): void
    {
        $this->startDate = $startDate;
    }

    public function getEndDate(): ?\DateTimeInterface
    {
        return $this->endDate;
    }

    public function setEndDate(?\DateTimeInterface $endDate = null): void
    {
        $this->endDate = $endDate;
    }

    public function isTerminated(): bool
    {
        $now = new \DateTime();

        return $this->endDate && $now > $this->endDate;
    }

    public function getLocation(): ?Location
    {
        return $this->location;
    }

    public function setLocation(?Location $location = null): void
    {
        $this->location = $location;
    }

    /**
     * @return Event[]|Collection
     */
    public function getEvents(): Collection
    {
        return $this->events;
    }

    public function getEventRegistrationType(): int
    {
        return $this->eventRegistrationType;
    }

    public function setEventRegistrationType(int $eventRegistrationType): void
    {
        $this->eventRegistrationType = $eventRegistrationType;
    }

    public function getInvitationTemplate(): ?Template
    {
        return $this->invitationTemplate;
    }

    public function setInvitationTemplate(?Template $template = null): void
    {
        $this->invitationTemplate = $template;
    }

    public function isCanceled(): bool
    {
        return $this->canceled;
    }

    public function setCanceled(bool $canceled): void
    {
        $this->canceled = $canceled;
    }

    public function getCancelReason(): ?string
    {
        return $this->cancelReason;
    }

    public function setCancelReason(?string $cancelReason): self
    {
        $this->cancelReason = $cancelReason;

        return $this;
    }

    public function getCanceledTemplate(): ?Template
    {
        return $this->canceledTemplate;
    }

    public function setCanceledTemplate(?Template $template = null): void
    {
        $this->canceledTemplate = $template;
    }

    public function getPublicRegistration(): bool
    {
        return $this->course?->getPublicRegistration();
    }

    public function getAutoRegistration(): bool
    {
        return $this->course?->getAutoRegistration();
    }

    public function getPublicUnregistration(): bool
    {
        return $this->course?->getPublicUnregistration();
    }

    public function hasValidation(): bool
    {
        return $this->course?->hasValidation();
    }

    public function hasConfirmation(): bool
    {
        return $this->course?->hasConfirmation();
    }

    public function getRegistrationMail(): bool
    {
        return $this->course?->getRegistrationMail();
    }

    public function getPendingRegistrations(): bool
    {
        return $this->course?->getPendingRegistrations();
    }
}
