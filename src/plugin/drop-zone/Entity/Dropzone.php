<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\DropZoneBundle\Entity;

use Doctrine\DBAL\Types\Types;
use DateTimeInterface;
use DateTime;
use Claroline\CoreBundle\Entity\Resource\AbstractResource;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'claro_dropzonebundle_dropzone')]
#[ORM\Entity]
class Dropzone extends AbstractResource
{
    const STATE_NOT_STARTED = 'not_started';
    const STATE_ALLOW_DROP = 'drop';
    const STATE_FINISHED = 'finished';
    const STATE_PEER_REVIEW = 'review';
    const STATE_ALLOW_DROP_AND_PEER_REVIEW = 'drop_review';
    const STATE_WAITING_FOR_PEER_REVIEW = 'review_standby';

    const DROP_TYPE_USER = 'user';
    const DROP_TYPE_TEAM = 'team';

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $instruction = null;

    #[ORM\Column(name: 'correction_instruction', type: Types::TEXT, nullable: true)]
    private ?string $correctionInstruction = null;

    #[ORM\Column(name: 'success_message', type: Types::TEXT, nullable: true)]
    private ?string $successMessage = null;

    #[ORM\Column(name: 'fail_message', type: Types::TEXT, nullable: true)]
    private ?string $failMessage = null;

    #[ORM\Column(name: 'workspace_resource_enabled', type: Types::BOOLEAN, nullable: false)]
    private bool $workspaceResourceEnabled = false;

    #[ORM\Column(name: 'upload_enabled', type: Types::BOOLEAN, nullable: false)]
    private bool $uploadEnabled = true;

    #[ORM\Column(name: 'url_enabled', type: Types::BOOLEAN, nullable: false)]
    private bool $urlEnabled = false;

    #[ORM\Column(name: 'rich_text_enabled', type: Types::BOOLEAN, nullable: false)]
    private bool $richTextEnabled = false;

    #[ORM\Column(name: 'peer_review', type: Types::BOOLEAN, nullable: false)]
    private bool $peerReview = false;

    #[ORM\Column(name: 'expected_correction_total', type: 'smallint', nullable: false)]
    private int $expectedCorrectionTotal = 3;

    #[ORM\Column(name: 'display_notation_to_learners', type: Types::BOOLEAN, nullable: false)]
    private bool $displayNotationToLearners = true;

    #[ORM\Column(name: 'display_notation_message_to_learners', type: Types::BOOLEAN, nullable: false)]
    private bool $displayNotationMessageToLearners = false;

    #[ORM\Column(name: 'score_to_pass', type: Types::FLOAT, nullable: false)]
    private ?float $scoreToPass = 50;

    #[ORM\Column(name: 'score_max', type: Types::INTEGER, nullable: false)]
    private int $scoreMax = 100;

    #[ORM\Column(name: 'drop_type', type: Types::TEXT, nullable: false)]
    private string $dropType = self::DROP_TYPE_USER;

    #[ORM\Column(name: 'manual_planning', type: Types::BOOLEAN, nullable: false)]
    private bool $manualPlanning = true;

    #[ORM\Column(name: 'manual_state', type: Types::TEXT, nullable: false)]
    private string $manualState = self::STATE_NOT_STARTED;

    #[ORM\Column(name: 'drop_start_date', type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?DateTimeInterface $dropStartDate = null;

    #[ORM\Column(name: 'drop_end_date', type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?DateTimeInterface $dropEndDate = null;

    #[ORM\Column(name: 'review_start_date', type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?DateTimeInterface $reviewStartDate = null;

    #[ORM\Column(name: 'review_end_date', type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?DateTimeInterface $reviewEndDate = null;

    #[ORM\Column(name: 'comment_in_correction_enabled', type: Types::BOOLEAN, nullable: false)]
    private bool $commentInCorrectionEnabled = false;

    #[ORM\Column(name: 'comment_in_correction_forced', type: Types::BOOLEAN, nullable: false)]
    private bool $commentInCorrectionForced = false;

    #[ORM\Column(name: 'display_corrections_to_learners', type: Types::BOOLEAN, nullable: false)]
    private bool $displayCorrectionsToLearners = false;

    /**
     * Depend on displayCorrectionsToLearners, need displayCorrectionsToLearners to be true in order to work.
     * Allow users to flag that they are not agree with the correction.
     */
    #[ORM\Column(name: 'correction_denial_enabled', type: Types::BOOLEAN, nullable: false)]
    private bool $correctionDenialEnabled = false;

    #[ORM\Column(name: 'criteria_enabled', type: Types::BOOLEAN, nullable: false)]
    private bool $criteriaEnabled = false;

    #[ORM\Column(name: 'criteria_total', type: 'smallint', nullable: false)]
    private int $criteriaTotal = 4;

    /**
     * if true,
     * when time is up, all drop not already closed will be closed and flaged as uncompletedDrop.
     * That will allow them to access the next step ( correction by users or admins ).
     */
    #[ORM\Column(name: 'auto_close_drops_at_drop_end_date', type: Types::BOOLEAN, nullable: false)]
    private bool $autoCloseDropsAtDropEndDate = true;

    /**
     * Becomes true when all the drops have been force closed at the end of the evaluation.
     * (Used when `autoCloseDropsAtDropEndDate` = true).
     */
    #[ORM\Column(name: 'drop_closed', type: Types::BOOLEAN, nullable: false)]
    private bool $dropClosed = false;

    #[ORM\OneToMany(targetEntity: Criterion::class, mappedBy: 'dropzone', cascade: ['persist', 'remove'])]
    private Collection $criteria;

    /**
     * Display the name of the corrector.
     */
    #[ORM\Column(name: 'corrector_displayed', type: Types::BOOLEAN, nullable: false)]
    private bool $correctorDisplayed = false;

    /**
     * Allows to submit drop for a revision.
     */
    #[ORM\Column(name: 'revision_enabled', type: Types::BOOLEAN, nullable: false)]
    private bool $revisionEnabled = false;

    /**
     * If true, drops for the current dropzone can not be deleted.
     */
    #[ORM\Column(name: 'lock_drops', type: Types::BOOLEAN, nullable: false)]
    private bool $lockDrops = false;

    public function __construct()
    {
        parent::__construct();

        $this->criteria = new ArrayCollection();
    }

    public function getInstruction()
    {
        return $this->instruction;
    }

    public function setInstruction($instruction): void
    {
        $this->instruction = $instruction;
    }

    public function getCorrectionInstruction()
    {
        return $this->correctionInstruction;
    }

    public function setCorrectionInstruction($correctionInstruction): void
    {
        $this->correctionInstruction = $correctionInstruction;
    }

    public function getSuccessMessage()
    {
        return $this->successMessage;
    }

    public function setSuccessMessage($successMessage): void
    {
        $this->successMessage = $successMessage;
    }

    public function getFailMessage()
    {
        return $this->failMessage;
    }

    public function setFailMessage($failMessage): void
    {
        $this->failMessage = $failMessage;
    }

    public function isPeerReview(): bool
    {
        return $this->peerReview;
    }

    public function setPeerReview($peerReview): void
    {
        $this->peerReview = $peerReview;
    }

    public function getExpectedCorrectionTotal()
    {
        return $this->expectedCorrectionTotal;
    }

    public function setExpectedCorrectionTotal($expectedCorrectionTotal): void
    {
        $this->expectedCorrectionTotal = $expectedCorrectionTotal;
    }

    public function getDisplayNotationToLearners()
    {
        return $this->displayNotationToLearners;
    }

    public function setDisplayNotationToLearners($displayNotationToLearners): void
    {
        $this->displayNotationToLearners = $displayNotationToLearners;
    }

    public function getDisplayNotationMessageToLearners()
    {
        return $this->displayNotationMessageToLearners;
    }

    public function setDisplayNotationMessageToLearners($displayNotationMessageToLearners): void
    {
        $this->displayNotationMessageToLearners = $displayNotationMessageToLearners;
    }

    public function getScoreMax(): ?int
    {
        return $this->scoreMax;
    }

    public function setScoreMax($scoreMax): void
    {
        $this->scoreMax = $scoreMax;
    }

    public function getScoreToPass(): ?float
    {
        return $this->scoreToPass;
    }

    public function setScoreToPass($scoreToPass): void
    {
        $this->scoreToPass = $scoreToPass;
    }

    public function getDropType(): string
    {
        return $this->dropType;
    }

    public function setDropType($dropType): void
    {
        $this->dropType = $dropType;
    }

    public function getManualPlanning()
    {
        return $this->manualPlanning;
    }

    public function setManualPlanning($manualPlanning): void
    {
        $this->manualPlanning = $manualPlanning;
    }

    public function getManualState()
    {
        return $this->manualState;
    }

    public function setManualState($manualState): void
    {
        $this->manualState = $manualState;
    }

    public function getDropStartDate(): ?DateTimeInterface
    {
        return $this->dropStartDate;
    }

    public function setDropStartDate(DateTimeInterface $dropStartDate = null): void
    {
        $this->dropStartDate = $dropStartDate;
    }

    public function getDropEndDate(): ?DateTimeInterface
    {
        return $this->dropEndDate;
    }

    public function setDropEndDate(DateTimeInterface $dropEndDate = null): void
    {
        $this->dropEndDate = $dropEndDate;
    }

    public function getReviewStartDate(): ?DateTimeInterface
    {
        return $this->reviewStartDate;
    }

    public function setReviewStartDate(DateTimeInterface $reviewStartDate = null): void
    {
        $this->reviewStartDate = $reviewStartDate;
    }

    public function getReviewEndDate(): ?DateTimeInterface
    {
        return $this->reviewEndDate;
    }

    public function setReviewEndDate(DateTimeInterface $reviewEndDate = null): void
    {
        $this->reviewEndDate = $reviewEndDate;
    }

    public function isCommentInCorrectionEnabled(): bool
    {
        return $this->commentInCorrectionEnabled;
    }

    public function setCommentInCorrectionEnabled($commentInCorrectionEnabled): void
    {
        $this->commentInCorrectionEnabled = $commentInCorrectionEnabled;
    }

    public function isCommentInCorrectionForced(): bool
    {
        return $this->commentInCorrectionForced;
    }

    public function setCommentInCorrectionForced($commentInCorrectionForced): void
    {
        $this->commentInCorrectionForced = $commentInCorrectionForced;
    }

    public function getDisplayCorrectionsToLearners()
    {
        return $this->displayCorrectionsToLearners;
    }

    public function setDisplayCorrectionsToLearners($displayCorrectionsToLearners): void
    {
        $this->displayCorrectionsToLearners = $displayCorrectionsToLearners;
    }

    public function isCorrectionDenialEnabled(): bool
    {
        return $this->correctionDenialEnabled;
    }

    public function setCorrectionDenialEnabled($correctionDenialEnabled): void
    {
        $this->correctionDenialEnabled = $correctionDenialEnabled;
    }

    public function isCriteriaEnabled(): bool
    {
        return $this->criteriaEnabled;
    }

    public function setCriteriaEnabled($criteriaEnabled): void
    {
        $this->criteriaEnabled = $criteriaEnabled;
    }

    public function getCriteriaTotal()
    {
        return $this->criteriaTotal;
    }

    public function setCriteriaTotal($criteriaTotal): void
    {
        $this->criteriaTotal = $criteriaTotal;
    }

    public function getAutoCloseDropsAtDropEndDate()
    {
        return $this->autoCloseDropsAtDropEndDate;
    }

    public function setAutoCloseDropsAtDropEndDate($autoCloseDropsAtDropEndDate): void
    {
        $this->autoCloseDropsAtDropEndDate = $autoCloseDropsAtDropEndDate;
    }

    public function getDropClosed(): bool
    {
        return $this->dropClosed;
    }

    public function setDropClosed($dropClosed): void
    {
        $this->dropClosed = $dropClosed;
    }

    /**
     * @return Criterion[]
     */
    public function getCriteria()
    {
        return $this->criteria->toArray();
    }

    public function emptyCriteria(): void
    {
        $this->criteria->clear();
    }

    public function addCriterion(Criterion $criterion): void
    {
        if (!$this->criteria->contains($criterion)) {
            $this->criteria->add($criterion);
            $criterion->setDropzone($this);
        }
    }

    public function removeCriterion(Criterion $criterion): void
    {
        if ($this->criteria->contains($criterion)) {
            $this->criteria->removeElement($criterion);
        }
    }

    public function getAllowedDocuments(): array
    {
        $allowed = [];
        if ($this->uploadEnabled) {
            $allowed[] = 'file';
        }
        if ($this->richTextEnabled) {
            $allowed[] = 'html';
        }
        if ($this->urlEnabled) {
            $allowed[] = 'url';
        }
        if ($this->workspaceResourceEnabled) {
            $allowed[] = 'resource';
        }

        return $allowed;
    }

    public function setAllowedDocuments(array $allowedDocuments): void
    {
        $this->uploadEnabled = in_array('file', $allowedDocuments);
        $this->richTextEnabled = in_array('html', $allowedDocuments);
        $this->urlEnabled = in_array('url', $allowedDocuments);
        $this->workspaceResourceEnabled = in_array('resource', $allowedDocuments);
    }

    public function isDropEnabled(): bool
    {
        $currentDate = new DateTime();

        return (
            $this->manualPlanning &&
            in_array($this->manualState, [self::STATE_ALLOW_DROP, self::STATE_ALLOW_DROP_AND_PEER_REVIEW])
        ) || (
            !$this->manualPlanning &&
            (!empty($this->dropStartDate) && $currentDate >= $this->dropStartDate) &&
            (!empty($this->dropEndDate) && $currentDate <= $this->dropEndDate)
        );
    }

    public function isReviewEnabled(): bool
    {
        $currentDate = new DateTime();

        return $this->peerReview && ((
            $this->manualPlanning &&
            in_array($this->manualState, [self::STATE_PEER_REVIEW, self::STATE_ALLOW_DROP_AND_PEER_REVIEW])
        ) || (
            !$this->manualPlanning &&
            (!empty($this->reviewStartDate) && $currentDate >= $this->reviewStartDate) &&
            (!empty($this->reviewEndDate) && $currentDate <= $this->reviewEndDate)
        ));
    }

    public function isCorrectorDisplayed(): bool
    {
        return $this->correctorDisplayed;
    }

    public function setCorrectorDisplayed($correctorDisplayed): void
    {
        $this->correctorDisplayed = $correctorDisplayed;
    }

    public function isRevisionEnabled(): bool
    {
        return $this->revisionEnabled;
    }

    public function setRevisionEnabled($revisionEnabled): void
    {
        $this->revisionEnabled = $revisionEnabled;
    }

    public function hasLockDrops(): bool
    {
        return $this->lockDrops;
    }

    public function setLockDrops(bool $locked): void
    {
        $this->lockDrops = $locked;
    }
}
