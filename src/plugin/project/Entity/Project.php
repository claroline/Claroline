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
use Claroline\CoreBundle\Entity\User;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'claro_project')]
#[ORM\Entity]
class Project extends AbstractResource
{
    /**
     * Instructions displayed to the learner describing the expected work.
     */
    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $instruction = null;

    /**
     * Expected submission format for learners (file, text, url, media, none).
     * "none" is used for field observation where no digital submission is required.
     */
    #[ORM\Column(name: 'expected_format', type: Types::STRING, length: 255)]
    private string $expectedFormat = 'file';

    /**
     * Evaluation mode used by the trainer to grade submissions.
     *   - raw_score : direct score entry on a configurable total
     *   - simple_grid : criteria-based rubric with free scoring per criterion
     */
    #[ORM\Column(name: 'evaluation_type', type: Types::STRING, length: 255)]
    private string $evaluationType = 'raw_score';

    /**
     * Date from which learners can start submitting their work.
     */
    #[ORM\Column(name: 'drop_start_date', type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $dropStartDate = null;

    /**
     * Deadline for submission. Used to compute the countdown displayed to learners.
     */
    #[ORM\Column(name: 'drop_end_date', type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $dropEndDate = null;

    /**
     * Estimated duration (in minutes) the learner should spend on the assignment.
     * Displayed on the learner home page as an indication.
     */
    #[ORM\Column(name: 'estimated_duration', type: Types::INTEGER, nullable: true)]
    private ?int $estimatedDuration = null;

    /**
     * Allow learners to upload files (PDF, Word, image, video, audio).
     */
    #[ORM\Column(name: 'allow_file_upload', type: Types::BOOLEAN)]
    private bool $allowFileUpload = true;

    /**
     * Allow learners to submit rich text content via the platform WYSIWYG editor.
     */
    #[ORM\Column(name: 'allow_rich_text', type: Types::BOOLEAN)]
    private bool $allowRichText = false;

    /**
     * Allow learners to submit external URLs.
     */
    #[ORM\Column(name: 'allow_url', type: Types::BOOLEAN)]
    private bool $allowUrl = false;

    /**
     * Allow learners to upload media files directly.
     */
    #[ORM\Column(name: 'allow_media', type: Types::BOOLEAN)]
    private bool $allowMedia = false;

    /**
     * Enable team mode. When active, submissions are per Team and
     * the grade is propagated to all team members (with optional individual adjustment).
     */
    #[ORM\Column(name: 'group_mode', type: Types::BOOLEAN)]
    private bool $groupMode = false;

    /**
     * Enable iterative mode (multi-milestone tracking).
     * When active, the trainer can define successive milestones with their own
     * instructions, deadlines and themed annotations.
     */
    #[ORM\Column(name: 'iterative_mode', type: Types::BOOLEAN)]
    private bool $iterativeMode = false;

    /**
     * Metadata of the template file provided by the trainer for learners to download
     * and fill in (e.g. a report template, a structured form).
     */
    #[ORM\Column(name: 'template_file', type: Types::JSON, nullable: true)]
    private ?array $templateFile = null;

    /**
     * Submissions made by learners or teams for this project.
     *
     * @var Collection<int, Drop>
     */
    #[ORM\OneToMany(targetEntity: Drop::class, mappedBy: 'project', cascade: ['persist', 'remove'])]
    private Collection $drops;

    /**
     * Milestones for iterative tracking (only used when iterativeMode is enabled).
     * Ordered by position to reflect the chronological sequence of milestones.
     *
     * @var Collection<int, Milestone>
     */
    #[ORM\OneToMany(targetEntity: Milestone::class, mappedBy: 'project', cascade: ['persist', 'remove'])]
    #[ORM\OrderBy(['position' => 'ASC'])]
    private Collection $milestones;

    /**
     * Evaluation criteria defined by the trainer (only used when evaluationType is "simple_grid").
     * Ordered by position to reflect the display order in the rubric.
     *
     * @var Collection<int, Criterion>
     */
    #[ORM\OneToMany(targetEntity: Criterion::class, mappedBy: 'project', cascade: ['persist', 'remove'])]
    #[ORM\OrderBy(['position' => 'ASC'])]
    private Collection $criteria;

    /**
     * All corrections (grades/evaluations) made by trainers for this project.
     *
     * @var Collection<int, Correction>
     */
    #[ORM\OneToMany(targetEntity: Correction::class, mappedBy: 'project', cascade: ['persist', 'remove'])]
    private Collection $corrections;

    /**
     * Users designated as evaluators for this project.
     * Multiple evaluators can be assigned (e.g. several trainers following the same learners).
     *
     * @var Collection<int, User>
     */
    #[ORM\ManyToMany(targetEntity: User::class)]
    #[ORM\JoinTable(name: 'claro_project_evaluators')]
    private Collection $evaluators;

    public function __construct()
    {
        parent::__construct();

        $this->drops = new ArrayCollection();
        $this->milestones = new ArrayCollection();
        $this->criteria = new ArrayCollection();
        $this->corrections = new ArrayCollection();
        $this->evaluators = new ArrayCollection();
    }

    public function getInstruction(): ?string
    {
        return $this->instruction;
    }

    public function setInstruction(?string $instruction): void
    {
        $this->instruction = $instruction;
    }

    public function getExpectedFormat(): string
    {
        return $this->expectedFormat;
    }

    public function setExpectedFormat(string $expectedFormat): void
    {
        $this->expectedFormat = $expectedFormat;
    }

    public function getEvaluationType(): string
    {
        return $this->evaluationType;
    }

    public function setEvaluationType(string $evaluationType): void
    {
        $this->evaluationType = $evaluationType;
    }

    public function getDropStartDate(): ?\DateTimeInterface
    {
        return $this->dropStartDate;
    }

    public function setDropStartDate(?\DateTimeInterface $dropStartDate): void
    {
        $this->dropStartDate = $dropStartDate;
    }

    public function getDropEndDate(): ?\DateTimeInterface
    {
        return $this->dropEndDate;
    }

    public function setDropEndDate(?\DateTimeInterface $dropEndDate): void
    {
        $this->dropEndDate = $dropEndDate;
    }

    public function getEstimatedDuration(): ?int
    {
        return $this->estimatedDuration;
    }

    public function setEstimatedDuration(?int $estimatedDuration): void
    {
        $this->estimatedDuration = $estimatedDuration;
    }

    public function isAllowFileUpload(): bool
    {
        return $this->allowFileUpload;
    }

    public function setAllowFileUpload(bool $allowFileUpload): void
    {
        $this->allowFileUpload = $allowFileUpload;
    }

    public function isAllowRichText(): bool
    {
        return $this->allowRichText;
    }

    public function setAllowRichText(bool $allowRichText): void
    {
        $this->allowRichText = $allowRichText;
    }

    public function isAllowUrl(): bool
    {
        return $this->allowUrl;
    }

    public function setAllowUrl(bool $allowUrl): void
    {
        $this->allowUrl = $allowUrl;
    }

    public function isAllowMedia(): bool
    {
        return $this->allowMedia;
    }

    public function setAllowMedia(bool $allowMedia): void
    {
        $this->allowMedia = $allowMedia;
    }

    public function isGroupMode(): bool
    {
        return $this->groupMode;
    }

    public function setGroupMode(bool $groupMode): void
    {
        $this->groupMode = $groupMode;
    }

    public function isIterativeMode(): bool
    {
        return $this->iterativeMode;
    }

    public function setIterativeMode(bool $iterativeMode): void
    {
        $this->iterativeMode = $iterativeMode;
    }

    public function getTemplateFile(): ?array
    {
        return $this->templateFile;
    }

    public function setTemplateFile(?array $templateFile): void
    {
        $this->templateFile = $templateFile;
    }

    public function getDrops(): Collection
    {
        return $this->drops;
    }

    public function getMilestones(): Collection
    {
        return $this->milestones;
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

    public function getCriteria(): Collection
    {
        return $this->criteria;
    }

    public function addCriterion(Criterion $criterion): void
    {
        if (!$this->criteria->contains($criterion)) {
            $this->criteria->add($criterion);
            $criterion->setProject($this);
        }
    }

    public function removeCriterion(Criterion $criterion): void
    {
        if ($this->criteria->contains($criterion)) {
            $this->criteria->removeElement($criterion);
        }
    }

    public function getCorrections(): Collection
    {
        return $this->corrections;
    }

    public function getEvaluators(): Collection
    {
        return $this->evaluators;
    }

    public function addEvaluator(User $user): void
    {
        if (!$this->evaluators->contains($user)) {
            $this->evaluators->add($user);
        }
    }

    public function removeEvaluator(User $user): void
    {
        if ($this->evaluators->contains($user)) {
            $this->evaluators->removeElement($user);
        }
    }
}
