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

#[ORM\Table(name: 'claro_project_milestone')]
#[ORM\Entity]
class Milestone
{
    use Id;
    use Uuid;

    /**
     * The project resource this milestone belongs to.
     */
    #[ORM\ManyToOne(targetEntity: Project::class, inversedBy: 'milestones')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Project $project = null;

    /**
     * Display order of the milestone within the project.
     * Reflects the chronological sequence (e.g. first draft, revision, final submission).
     */
    #[ORM\Column(type: Types::INTEGER)]
    private int $position = 0;

    /**
     * Specific instructions for this milestone, displayed to the learner.
     * Complements the global project instruction with milestone-specific guidance.
     */
    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $instruction = null;

    /**
     * Deadline for this milestone. Each milestone can have its own deadline
     * independent from the global project deadline.
     */
    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $deadline = null;

    /**
     * Themed annotation categories defined by the trainer for this milestone.
     * Allows the trainer to organize feedback by topic (e.g. "Structure", "References", "Style").
     */
    #[ORM\Column(name: 'annotation_categories', type: Types::JSON)]
    private array $annotationCategories = [];

    public function __construct()
    {
        $this->refreshUuid();
    }

    public function getProject(): ?Project
    {
        return $this->project;
    }

    public function setProject(?Project $project): void
    {
        $this->project = $project;
    }

    public function getPosition(): int
    {
        return $this->position;
    }

    public function setPosition(int $position): void
    {
        $this->position = $position;
    }

    public function getInstruction(): ?string
    {
        return $this->instruction;
    }

    public function setInstruction(?string $instruction): void
    {
        $this->instruction = $instruction;
    }

    public function getDeadline(): ?\DateTimeInterface
    {
        return $this->deadline;
    }

    public function setDeadline(?\DateTimeInterface $deadline): void
    {
        $this->deadline = $deadline;
    }

    public function getAnnotationCategories(): array
    {
        return $this->annotationCategories;
    }

    public function setAnnotationCategories(array $annotationCategories): void
    {
        $this->annotationCategories = $annotationCategories;
    }
}
