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
use Claroline\CommunityBundle\Entity\Team;
use Claroline\CoreBundle\Entity\User;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'claro_project_correction')]
#[ORM\Entity]
class Correction
{
    use Id;
    use Uuid;

    /**
     * The project resource this correction belongs to.
     */
    #[ORM\ManyToOne(targetEntity: Project::class, inversedBy: 'corrections')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Project $project = null;

    /**
     * The drop being evaluated.
     * Null when the trainer grades directly without any submission (direct score mode).
     */
    #[ORM\ManyToOne(targetEntity: Drop::class)]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?Drop $drop = null;

    /**
     * The individual learner being evaluated.
     * Null when the correction targets a team (group mode).
     */
    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?User $user = null;

    /**
     * The team being evaluated (group mode only).
     * Null when the correction targets an individual learner.
     * The grade is then propagated to individual corrections for each team member.
     */
    #[ORM\ManyToOne(targetEntity: Team::class)]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?Team $team = null;

    /**
     * The trainer who performed this correction.
     */
    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'corrector_id', nullable: false, onDelete: 'CASCADE')]
    private ?User $corrector = null;

    /**
     * Overall score assigned by the trainer.
     * When using a simple grid, this is computed from the sum of criterion grades.
     */
    #[ORM\Column(type: Types::FLOAT, nullable: true)]
    private ?float $score = null;

    /**
     * General feedback comment from the trainer about the submission.
     */
    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $comment = null;

    /**
     * Visibility status of the correction.
     *   - draft : the correction is in progress, not visible to the learner
     *   - submitted : the correction is finalized, score and annotations are visible to the learner
     */
    #[ORM\Column(type: Types::STRING, length: 255)]
    private string $status = 'draft';

    /**
     * Date and time the trainer started this correction.
     */
    #[ORM\Column(name: 'start_date', type: Types::DATETIME_MUTABLE)]
    private \DateTimeInterface $startDate;

    /**
     * Date and time of the last modification to this correction.
     */
    #[ORM\Column(name: 'last_edition_date', type: Types::DATETIME_MUTABLE)]
    private \DateTimeInterface $lastEditionDate;

    /**
     * Date and time the correction was submitted (published to the learner).
     * Null while the correction is still in draft status.
     */
    #[ORM\Column(name: 'submission_date', type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $submissionDate = null;

    /**
     * Individual criterion grades (only used when evaluationType is "simple_grid").
     *
     * @var Collection<int, Grade>
     */
    #[ORM\OneToMany(targetEntity: Grade::class, mappedBy: 'correction', cascade: ['persist', 'remove'])]
    private Collection $grades;

    public function __construct()
    {
        $this->refreshUuid();

        $this->startDate = new \DateTime();
        $this->lastEditionDate = new \DateTime();
        $this->grades = new ArrayCollection();
    }

    public function getProject(): ?Project
    {
        return $this->project;
    }

    public function setProject(?Project $project): void
    {
        $this->project = $project;
    }

    public function getDrop(): ?Drop
    {
        return $this->drop;
    }

    public function setDrop(?Drop $drop): void
    {
        $this->drop = $drop;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): void
    {
        $this->user = $user;
    }

    public function getTeam(): ?Team
    {
        return $this->team;
    }

    public function setTeam(?Team $team): void
    {
        $this->team = $team;
    }

    public function getCorrector(): ?User
    {
        return $this->corrector;
    }

    public function setCorrector(?User $corrector): void
    {
        $this->corrector = $corrector;
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

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): void
    {
        $this->status = $status;
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

    public function getGrades(): Collection
    {
        return $this->grades;
    }

    public function addGrade(Grade $grade): void
    {
        if (!$this->grades->contains($grade)) {
            $this->grades->add($grade);
            $grade->setCorrection($this);
        }
    }

    public function removeGrade(Grade $grade): void
    {
        if ($this->grades->contains($grade)) {
            $this->grades->removeElement($grade);
        }
    }
}
