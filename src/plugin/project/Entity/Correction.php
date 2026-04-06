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
use Claroline\CoreBundle\Entity\User;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * A correction represents a trainer's evaluation of a learner's work.
 *
 * In "direct grade" mode, the correction is created without a linked submission.
 * In "iterative" mode, there can be one correction per milestone (for feedback)
 * plus a final correction (milestone=null) that carries the overall grade.
 *
 * The correction status controls visibility:
 *   - "in_progress" : Hidden from the learner (draft).
 *   - "submitted"   : Visible to the learner (published).
 */
#[ORM\Table(name: 'claro_project_correction')]
#[ORM\Entity]
class Correction
{
    use Id;
    use Uuid;

    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_SUBMITTED = 'submitted';

    /**
     * Publication status of the correction.
     * Controls whether the learner can see the grade and feedback.
     */
    #[ORM\Column(type: Types::STRING)]
    private string $status = self::STATUS_IN_PROGRESS;

    /**
     * Raw score given by the trainer (used when gradingMode is "raw_score").
     * In rubric mode, this is computed as the sum of criteria scores.
     */
    #[ORM\Column(type: Types::FLOAT, nullable: true)]
    private ?float $score = null;

    /**
     * General comment/feedback from the trainer.
     */
    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $comment = null;

    /**
     * Date when the trainer started this correction.
     */
    #[ORM\Column(name: 'start_date', type: Types::DATETIME_MUTABLE)]
    private \DateTimeInterface $startDate;

    /**
     * Date of the last modification to this correction.
     */
    #[ORM\Column(name: 'last_edition_date', type: Types::DATETIME_MUTABLE)]
    private \DateTimeInterface $lastEditionDate;

    /**
     * Date when the correction was published (status changed to "submitted").
     */
    #[ORM\Column(name: 'submission_date', type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $submissionDate = null;

    /**
     * The project this correction belongs to.
     */
    #[ORM\JoinColumn(name: 'project_id', nullable: false, onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: Project::class)]
    private ?Project $project = null;

    /**
     * The submission being corrected.
     * Null in "direct grade" mode where no submission is expected.
     */
    #[ORM\JoinColumn(name: 'submission_id', nullable: true, onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: Submission::class)]
    private ?Submission $submission = null;

    /**
     * The milestone this correction is associated with (for iterative mode feedback).
     * Null for the final correction that carries the overall grade.
     */
    #[ORM\JoinColumn(name: 'milestone_id', nullable: true, onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: Milestone::class)]
    private ?Milestone $milestone = null;

    /**
     * The learner being evaluated.
     */
    #[ORM\JoinColumn(name: 'user_id', nullable: true, onDelete: 'SET NULL')]
    #[ORM\ManyToOne(targetEntity: User::class)]
    private ?User $user = null;

    /**
     * The trainer/evaluator who performed this correction.
     */
    #[ORM\JoinColumn(name: 'corrector_id', nullable: true, onDelete: 'SET NULL')]
    #[ORM\ManyToOne(targetEntity: User::class)]
    private ?User $corrector = null;

    /**
     * Individual scores per criterion (used in rubric grading mode).
     *
     * @var Collection<int, CriteriaScore>
     */
    #[ORM\OneToMany(targetEntity: CriteriaScore::class, mappedBy: 'correction', cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $criteriaScores;

    public function __construct()
    {
        $this->refreshUuid();

        $currentDate = new \DateTime();
        $this->startDate = $currentDate;
        $this->lastEditionDate = $currentDate;
        $this->criteriaScores = new ArrayCollection();
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): void
    {
        $this->status = $status;
    }

    public function isSubmitted(): bool
    {
        return self::STATUS_SUBMITTED === $this->status;
    }

    public function getScore(): ?float
    {
        return $this->score;
    }

    public function setScore(?float $score): void
    {
        $this->score = $score;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(?string $comment): void
    {
        $this->comment = $comment;
    }

    public function getStartDate(): \DateTimeInterface
    {
        return $this->startDate;
    }

    public function setStartDate(\DateTimeInterface $startDate): void
    {
        $this->startDate = $startDate;
    }

    public function getLastEditionDate(): \DateTimeInterface
    {
        return $this->lastEditionDate;
    }

    public function setLastEditionDate(\DateTimeInterface $lastEditionDate): void
    {
        $this->lastEditionDate = $lastEditionDate;
    }

    public function getSubmissionDate(): ?\DateTimeInterface
    {
        return $this->submissionDate;
    }

    public function setSubmissionDate(?\DateTimeInterface $submissionDate): void
    {
        $this->submissionDate = $submissionDate;
    }

    public function getProject(): ?Project
    {
        return $this->project;
    }

    public function setProject(?Project $project): void
    {
        $this->project = $project;
    }

    public function getSubmission(): ?Submission
    {
        return $this->submission;
    }

    public function setSubmission(?Submission $submission): void
    {
        $this->submission = $submission;
    }

    public function getMilestone(): ?Milestone
    {
        return $this->milestone;
    }

    public function setMilestone(?Milestone $milestone): void
    {
        $this->milestone = $milestone;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): void
    {
        $this->user = $user;
    }

    public function getCorrector(): ?User
    {
        return $this->corrector;
    }

    public function setCorrector(?User $corrector): void
    {
        $this->corrector = $corrector;
    }

    /**
     * @return CriteriaScore[]
     */
    public function getCriteriaScores(): array
    {
        return $this->criteriaScores->toArray();
    }

    public function addCriteriaScore(CriteriaScore $criteriaScore): void
    {
        if (!$this->criteriaScores->contains($criteriaScore)) {
            $this->criteriaScores->add($criteriaScore);
            $criteriaScore->setCorrection($this);
        }
    }

    public function removeCriteriaScore(CriteriaScore $criteriaScore): void
    {
        if ($this->criteriaScores->contains($criteriaScore)) {
            $this->criteriaScores->removeElement($criteriaScore);
        }
    }

    public function emptyCriteriaScores(): void
    {
        $this->criteriaScores->clear();
    }
}
