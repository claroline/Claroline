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

use Doctrine\DBAL\Types\Types;
use Claroline\CursusBundle\Repository\SessionRepository;
use DateTimeInterface;
use DateTime;
use Claroline\AppBundle\Entity\IdentifiableInterface;
use Claroline\CoreBundle\Entity\Location;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\Template\Template;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;


#[ORM\Table(name: 'claro_cursusbundle_course_session')]
#[ORM\Entity(repositoryClass: SessionRepository::class)]
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

    #[ORM\Column(name: 'start_date', type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?DateTimeInterface $startDate = null;

    #[ORM\Column(name: 'end_date', type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?DateTimeInterface $endDate = null;

    /**
     *
     *
     * @var Collection<int, ResourceNode>
     */
    #[ORM\JoinTable(name: 'claro_cursusbundle_course_session_resources')]
    #[ORM\JoinColumn(name: 'resource_id', referencedColumnName: 'id')]
    #[ORM\InverseJoinColumn(name: 'session_id', referencedColumnName: 'id', unique: true)]
    #[ORM\ManyToMany(targetEntity: ResourceNode::class, orphanRemoval: true)]
    private Collection $resources;

    
    #[ORM\JoinColumn(name: 'location_id', nullable: true, onDelete: 'SET NULL')]
    #[ORM\ManyToOne(targetEntity: Location::class)]
    private ?Location $location = null;

    /**
     * @var Collection<int, Event>
     */
    #[ORM\OneToMany(targetEntity: Event::class, mappedBy: 'session')]
    private Collection $events;

    #[ORM\Column(name: 'event_registration_type', type: Types::INTEGER, nullable: false, options: ['default' => 0])]
    private int $eventRegistrationType = self::REGISTRATION_AUTO;

    
    #[ORM\JoinColumn(name: 'invitation_template_id', nullable: true, onDelete: 'SET NULL')]
    #[ORM\ManyToOne(targetEntity: Template::class)]
    private ?Template $invitationTemplate = null;

    /**
     * @ORM\Column(name="canceled", type="boolean")
     */
    private bool $canceled = false;

    /**
     * @ORM\Column(name="cancel_reason", type="text", nullable=true)
     */
    private ?string $cancelReason = null;

    /**
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\Template\Template")
     *
     * @ORM\JoinColumn(name="canceled_template_id", nullable=true, onDelete="SET NULL")
     */
    private ?Template $canceledTemplate = null;

    public function __construct()
    {
        $this->refreshUuid();

        $this->resources = new ArrayCollection();
        $this->events = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->name;
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

    public function getStartDate(): ?DateTimeInterface
    {
        return $this->startDate;
    }

    public function setStartDate(DateTimeInterface $startDate = null): void
    {
        $this->startDate = $startDate;
    }

    public function getEndDate(): ?DateTimeInterface
    {
        return $this->endDate;
    }

    public function setEndDate(DateTimeInterface $endDate = null): void
    {
        $this->endDate = $endDate;
    }

    public function isTerminated(): bool
    {
        $now = new DateTime();

        return $this->endDate && $now > $this->endDate;
    }

    public function getResources(): Collection
    {
        return $this->resources;
    }

    public function setResources(array $resources): void
    {
        $this->resources = new ArrayCollection($resources);
    }

    public function addResource(ResourceNode $resource): void
    {
        if (!$this->resources->contains($resource)) {
            $this->resources->add($resource);
        }
    }

    public function removeResource(ResourceNode $resource): void
    {
        if ($this->resources->contains($resource)) {
            $this->resources->removeElement($resource);
        }
    }

    public function getLocation(): ?Location
    {
        return $this->location;
    }

    public function setLocation(Location $location = null): void
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

    public function setInvitationTemplate(Template $template = null): void
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

    public function setCanceledTemplate(Template $template = null): void
    {
        $this->canceledTemplate = $template;
    }
}
