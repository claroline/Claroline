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

use Claroline\CoreBundle\Entity\Resource\AbstractResource;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'claro_project')]
#[ORM\Entity]
class Project extends AbstractResource
{
    public const SUBMISSION_TYPE_FILE = 'file';
    public const SUBMISSION_TYPE_RICH_TEXT = 'rich_text';
    public const SUBMISSION_TYPE_URL = 'url';
    public const SUBMISSION_TYPE_NONE = 'none';

    public const GRADING_MODE_RAW_SCORE = 'raw_score';
    public const GRADING_MODE_RUBRIC = 'rubric';

    public const DEADLINE_TYPE_FIXED = 'fixed';
    public const DEADLINE_TYPE_RELATIVE = 'relative';
    public const DEADLINE_TYPE_NONE = 'none';

    /**
     * Rich text instructions displayed to learners describing the expected work.
     */
    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $instruction = null;

    /**
     * The type of submission expected from the learner.
     *   - file      : Upload a file (optionally restricted by MIME type).
     *   - rich_text : Write rich text content in the platform (WYSIWYG).
     *   - url       : Provide an external URL.
     *   - none      : No submission expected (direct grading mode).
     */
    #[ORM\Column(name: 'submission_type', type: Types::STRING)]
    private string $submissionType = self::SUBMISSION_TYPE_FILE;

    /**
     * Allowed MIME types when submissionType is "file".
     * Null means all file types are accepted.
     */
    #[ORM\Column(name: 'allowed_file_types', type: Types::JSON, nullable: true)]
    private ?array $allowedFileTypes = null;

    /**
     * Metadata of the template file provided by the trainer for learners to download.
     * Stored as JSON: {url, name, mimeType}.
     */
    #[ORM\Column(name: 'template_file', type: Types::JSON, nullable: true)]
    private ?array $templateFile = null;

    /**
     * The grading mode used by the trainer to evaluate submissions.
     *   - raw_score : A single score out of scoreMax.
     *   - rubric    : A criteria-based rubric where the total is the sum of criteria scores.
     */
    #[ORM\Column(name: 'grading_mode', type: Types::STRING)]
    private string $gradingMode = self::GRADING_MODE_RAW_SCORE;

    /**
     * Maximum possible score.
     * In rubric mode, this is automatically computed as the sum of all criteria scoreMax values.
     */
    #[ORM\Column(name: 'score_max', type: Types::FLOAT)]
    private float $scoreMax = 20;

    /**
     * How the deadline is determined.
     *   - fixed    : A specific date set by the trainer.
     *   - relative : Computed from the learner's first access date + deadlineDays.
     *   - none     : No deadline.
     */
    #[ORM\Column(name: 'deadline_type', type: Types::STRING)]
    private string $deadlineType = self::DEADLINE_TYPE_NONE;

    /**
     * Fixed deadline date (used when deadlineType is "fixed").
     */
    #[ORM\Column(name: 'deadline_date', type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $deadlineDate = null;

    /**
     * Number of days from the learner's first access to compute the deadline
     * (used when deadlineType is "relative").
     */
    #[ORM\Column(name: 'deadline_days', type: Types::INTEGER, nullable: true)]
    private ?int $deadlineDays = null;

    /**
     * Estimated duration of the work to produce, in minutes.
     * Displayed to learners as an indication.
     */
    #[ORM\Column(name: 'estimated_duration', type: Types::INTEGER, nullable: true)]
    private ?int $estimatedDuration = null;

    /**
     * Ordered list of milestones for iterative/longitudinal submission mode.
     *
     * @var Collection<int, Milestone>
     */
    #[ORM\OneToMany(targetEntity: Milestone::class, mappedBy: 'project', cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[ORM\OrderBy(['position' => 'ASC'])]
    private Collection $milestones;

    /**
     * Criteria used for rubric-based grading.
     * Each criterion has its own scoreMax; the project scoreMax is their sum.
     *
     * @var Collection<int, EvaluationCriteria>
     */
    #[ORM\OneToMany(targetEntity: EvaluationCriteria::class, mappedBy: 'project', cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[ORM\OrderBy(['position' => 'ASC'])]
    private Collection $criteria;

    public function __construct()
    {
        parent::__construct();

        $this->milestones = new ArrayCollection();
        $this->criteria = new ArrayCollection();
    }

    public function getInstruction(): ?string
    {
        return $this->instruction;
    }

    public function setInstruction(?string $instruction): void
    {
        $this->instruction = $instruction;
    }

    public function getSubmissionType(): string
    {
        return $this->submissionType;
    }

    public function setSubmissionType(string $submissionType): void
    {
        $this->submissionType = $submissionType;
    }

    public function getAllowedFileTypes(): ?array
    {
        return $this->allowedFileTypes;
    }

    public function setAllowedFileTypes(?array $allowedFileTypes): void
    {
        $this->allowedFileTypes = $allowedFileTypes;
    }

    public function getTemplateFile(): ?array
    {
        return $this->templateFile;
    }

    public function setTemplateFile(?array $templateFile): void
    {
        $this->templateFile = $templateFile;
    }

    public function getGradingMode(): string
    {
        return $this->gradingMode;
    }

    public function setGradingMode(string $gradingMode): void
    {
        $this->gradingMode = $gradingMode;
    }

    public function getScoreMax(): float
    {
        return $this->scoreMax;
    }

    public function setScoreMax(float $scoreMax): void
    {
        $this->scoreMax = $scoreMax;
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

    public function getEstimatedDuration(): ?int
    {
        return $this->estimatedDuration;
    }

    public function setEstimatedDuration(?int $estimatedDuration): void
    {
        $this->estimatedDuration = $estimatedDuration;
    }

    /**
     * @return Milestone[]
     */
    public function getMilestones(): array
    {
        return $this->milestones->toArray();
    }

    public function addMilestone(Milestone $milestone): void
    {
        if (!$this->milestones->contains($milestone)) {
            $this->milestones->add($milestone);
            $milestone->setProject($this);
        }
    }

    public function removeMilestone(Milestone $milestone): void
    {
        if ($this->milestones->contains($milestone)) {
            $this->milestones->removeElement($milestone);
        }
    }

    public function emptyMilestones(): void
    {
        $this->milestones->clear();
    }

    /**
     * @return EvaluationCriteria[]
     */
    public function getCriteria(): array
    {
        return $this->criteria->toArray();
    }

    public function addCriterion(EvaluationCriteria $criterion): void
    {
        if (!$this->criteria->contains($criterion)) {
            $this->criteria->add($criterion);
            $criterion->setProject($this);
        }
    }

    public function removeCriterion(EvaluationCriteria $criterion): void
    {
        if ($this->criteria->contains($criterion)) {
            $this->criteria->removeElement($criterion);
        }
    }

    public function emptyCriteria(): void
    {
        $this->criteria->clear();
    }
}
