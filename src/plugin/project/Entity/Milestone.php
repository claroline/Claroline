<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\ProjectBundle\Entity;

use Claroline\AppBundle\Entity\Identifier\Id;
use Claroline\AppBundle\Entity\Identifier\Uuid;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * A milestone represents a checkpoint in an iterative/longitudinal project.
 * Each milestone defines a step where learners must submit their work,
 * with its own instructions and deadline.
 */
#[ORM\Table(name: 'claro_project_milestone')]
#[ORM\Entity]
class Milestone
{
    use Id;
    use Uuid;

    /**
     * Title of the milestone displayed to learners.
     */
    #[ORM\Column(type: Types::STRING)]
    private string $title = '';

    /**
     * Specific instructions for this milestone (rich text HTML).
     */
    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $instructions = null;

    /**
     * Display order of the milestone within the project.
     */
    #[ORM\Column(type: Types::INTEGER)]
    private int $position = 0;

    /**
     * How the deadline is determined for this milestone.
     *   - fixed    : A specific date.
     *   - relative : Computed from the previous milestone deadline + deadlineDays.
     *   - none     : No deadline for this milestone.
     */
    #[ORM\Column(name: 'deadline_type', type: Types::STRING)]
    private string $deadlineType = Project::DEADLINE_TYPE_NONE;

    /**
     * Fixed deadline date for this milestone.
     */
    #[ORM\Column(name: 'deadline_date', type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $deadlineDate = null;

    /**
     * Number of days from the previous milestone deadline (or first access for the first milestone).
     */
    #[ORM\Column(name: 'deadline_days', type: Types::INTEGER, nullable: true)]
    private ?int $deadlineDays = null;

    /**
     * The project this milestone belongs to.
     */
    #[ORM\JoinColumn(name: 'project_id', nullable: false, onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: Project::class, inversedBy: 'milestones')]
    private ?Project $project = null;

    public function __construct()
    {
        $this->refreshUuid();
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    public function getInstructions(): ?string
    {
        return $this->instructions;
    }

    public function setInstructions(?string $instructions): void
    {
        $this->instructions = $instructions;
    }

    public function getPosition(): int
    {
        return $this->position;
    }

    public function setPosition(int $position): void
    {
        $this->position = $position;
    }

    public function getDeadlineType(): string
    {
        return $this->deadlineType;
    }

    public function setDeadlineType(string $deadlineType): void
    {
        $this->deadlineType = $deadlineType;
    }

    public function getDeadlineDate(): ?\DateTimeInterface
    {
        return $this->deadlineDate;
    }

    public function setDeadlineDate(?\DateTimeInterface $deadlineDate): void
    {
        $this->deadlineDate = $deadlineDate;
    }

    public function getDeadlineDays(): ?int
    {
        return $this->deadlineDays;
    }

    public function setDeadlineDays(?int $deadlineDays): void
    {
        $this->deadlineDays = $deadlineDays;
    }

    public function getProject(): ?Project
    {
        return $this->project;
    }

    public function setProject(?Project $project): void
    {
        $this->project = $project;
    }
}
