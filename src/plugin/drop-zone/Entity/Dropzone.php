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

use Claroline\CoreBundle\Entity\Resource\AbstractResource;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="claro_dropzonebundle_dropzone")
 */
class Dropzone extends AbstractResource
{
    const STATE_NOT_STARTED = 'not_started';
    const STATE_ALLOW_DROP = 'drop';
    const STATE_FINISHED = 'finished';
    const STATE_PEER_REVIEW = 'review';
    const STATE_ALLOW_DROP_AND_PEER_REVIEW = 'drop_review';
    const STATE_WAITING_FOR_PEER_REVIEW = 'review_standby';

    const AUTO_CLOSED_STATE_WAITING = 0;
    const AUTO_CLOSED_STATE_CLOSED = 1;

    const DROP_TYPE_USER = 'user';
    const DROP_TYPE_TEAM = 'team';

    /**
     * 1 = common
     * 2 = criteria
     * 3 = participant
     * 4 = finished.
     *
     * @ORM\Column(name="edition_state", type="smallint", nullable=false)
     *
     * @var int
     *
     * @todo remove me. it's not used anymore
     */
    protected $editionState = 1;

    /**
     * @ORM\Column(type="text", nullable=true)
     *
     * @var string
     */
    protected $instruction = null;

    /**
     * @ORM\Column(name="correction_instruction", type="text", nullable=true)
     *
     * @var string
     */
    protected $correctionInstruction = null;

    /**
     * @ORM\Column(name="success_message", type="text", nullable=true)
     *
     * @var string
     */
    protected $successMessage = null;

    /**
     * @ORM\Column(name="fail_message",type="text", nullable=true)
     *
     * @var string
     */
    protected $failMessage = null;

    /**
     * @ORM\Column(name="workspace_resource_enabled", type="boolean", nullable=false)
     *
     * @var bool
     */
    protected $workspaceResourceEnabled = false;

    /**
     * @ORM\Column(name="upload_enabled", type="boolean", nullable=false)
     *
     * @var bool
     */
    protected $uploadEnabled = true;

    /**
     * @ORM\Column(name="url_enabled", type="boolean", nullable=false)
     *
     * @var bool
     */
    protected $urlEnabled = false;

    /**
     * @ORM\Column(name="rich_text_enabled", type="boolean", nullable=false)
     *
     * @var bool
     */
    protected $richTextEnabled = false;

    /**
     * @ORM\Column(name="peer_review", type="boolean", nullable=false)
     *
     * @var bool
     */
    protected $peerReview = false;

    /**
     * @ORM\Column(name="expected_correction_total", type="smallint", nullable=false)
     *
     * @var int
     */
    protected $expectedCorrectionTotal = 3;

    /**
     * @ORM\Column(name="display_notation_to_learners", type="boolean", nullable=false)
     *
     * @var bool
     */
    protected $displayNotationToLearners = true;

    /**
     * @ORM\Column(name="display_notation_message_to_learners", type="boolean", nullable=false)
     *
     * @var bool
     */
    protected $displayNotationMessageToLearners = false;

    /**
     * @ORM\Column(name="score_to_pass", type="float", nullable=false)
     *
     * @var float
     */
    protected $scoreToPass = 50;

    /**
     * @ORM\Column(name="score_max", type="integer", nullable=false)
     *
     * @var int
     */
    protected $scoreMax = 100;

    /**
     * @ORM\Column(name="drop_type", type="text", nullable=false)
     *
     * @var string
     */
    protected $dropType = self::DROP_TYPE_USER;

    /**
     * @ORM\Column(name="manual_planning", type="boolean", nullable=false)
     *
     * @var bool
     */
    protected $manualPlanning = true;

    /**
     * @ORM\Column(name="manual_state", type="text", nullable=false)
     *
     * @var string
     */
    protected $manualState = self::STATE_NOT_STARTED;

    /**
     * @ORM\Column(name="drop_start_date", type="datetime", nullable=true)
     *
     * @var \DateTime
     */
    protected $dropStartDate = null;

    /**
     * @ORM\Column(name="drop_end_date", type="datetime", nullable=true)
     *
     * @var \DateTime
     */
    protected $dropEndDate = null;

    /**
     * @ORM\Column(name="review_start_date", type="datetime", nullable=true)
     */
    protected $reviewStartDate = null;

    /**
     * @ORM\Column(name="review_end_date", type="datetime", nullable=true)
     *
     * @var \DateTime
     */
    protected $reviewEndDate = null;

    /**
     * @ORM\Column(name="comment_in_correction_enabled", type="boolean", nullable=false)
     *
     * @var bool
     */
    protected $commentInCorrectionEnabled = false;

    /**
     * @ORM\Column(name="comment_in_correction_forced",type="boolean", nullable=false)
     *
     * @var bool
     */
    protected $commentInCorrectionForced = false;

    /**
     * @ORM\Column(name="display_corrections_to_learners", type="boolean", nullable=false)
     *
     * @var bool
     */
    protected $displayCorrectionsToLearners = false;

    /**
     * Depend on diplayCorrectionsToLearners, need diplayCorrectionsToLearners to be true in order to work.
     * Allow users to flag that they are not agree with the correction.
     *
     * @ORM\Column(name="correction_denial_enabled", type="boolean", nullable=false)
     *
     * @var bool
     */
    protected $correctionDenialEnabled = false;

    /**
     * @ORM\Column(name="criteria_enabled", type="boolean", nullable=false)
     *
     * @var bool
     */
    protected $criteriaEnabled = false;

    /**
     * @ORM\Column(name="criteria_total", type="smallint", nullable=false)
     *
     * @var int
     */
    protected $criteriaTotal = 4;

    /**
     * if true,
     * when time is up, all drop not already closed will be closed and flaged as uncompletedDrop.
     * That will allow them to access the next step ( correction by users or admins ).
     *
     * @ORM\Column(name="auto_close_drops_at_drop_end_date", type="boolean", nullable=false)
     *
     * @var bool
     */
    protected $autoCloseDropsAtDropEndDate = true;

    /**
     * @ORM\Column(name="auto_close_state", type="integer", nullable=false)
     *
     * @var int
     *
     * @todo remove me. it's not used anymore
     */
    protected $autoCloseState = self::AUTO_CLOSED_STATE_WAITING;

    /**
     * Becomes true when all the drops have been force closed at the end of the evaluation.
     * (Used when `autoCloseDropsAtDropEndDate` = true).
     *
     * @ORM\Column(name="drop_closed", type="boolean", nullable=false)
     *
     * @var bool
     */
    protected $dropClosed = false;

    /**
     * Notify Evaluation admins when a someone made a drop.
     *
     * @ORM\Column(name="notify_on_drop", type="boolean", nullable=false)
     *
     * @var bool
     */
    protected $notifyOnDrop = false;

    /**
     * @ORM\OneToMany(
     *     targetEntity="Claroline\DropZoneBundle\Entity\Criterion",
     *     mappedBy="dropzone",
     *     cascade={"persist", "remove"}
     * )
     *
     * @var ArrayCollection|Criterion[]
     */
    protected $criteria;

    /**
     * Display the name of the corrector.
     *
     * @ORM\Column(name="corrector_displayed", type="boolean", nullable=false)
     *
     * @var bool
     */
    protected $correctorDisplayed = false;

    /**
     * Allows to submit drop for a revision.
     *
     * @ORM\Column(name="revision_enabled", type="boolean", nullable=false)
     *
     * @var bool
     */
    protected $revisionEnabled = false;

    /**
     * If true, drops for the current dropzone can not be deleted.
     *
     * @ORM\Column(name="lock_drops", type="boolean", nullable=false)
     *
     * @var bool
     */
    protected $lockDrops = false;

    /**
     * Dropzone constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->criteria = new ArrayCollection();
    }

    public function getEditionState()
    {
        return $this->editionState;
    }

    public function setEditionState($editionState)
    {
        $this->editionState = $editionState;
    }

    public function getInstruction()
    {
        return $this->instruction;
    }

    public function setInstruction($instruction)
    {
        $this->instruction = $instruction;
    }

    public function getCorrectionInstruction()
    {
        return $this->correctionInstruction;
    }

    public function setCorrectionInstruction($correctionInstruction)
    {
        $this->correctionInstruction = $correctionInstruction;
    }

    public function getSuccessMessage()
    {
        return $this->successMessage;
    }

    public function setSuccessMessage($successMessage)
    {
        $this->successMessage = $successMessage;
    }

    public function getFailMessage()
    {
        return $this->failMessage;
    }

    public function setFailMessage($failMessage)
    {
        $this->failMessage = $failMessage;
    }

    public function isPeerReview()
    {
        return $this->peerReview;
    }

    public function setPeerReview($peerReview)
    {
        $this->peerReview = $peerReview;
    }

    public function getExpectedCorrectionTotal()
    {
        return $this->expectedCorrectionTotal;
    }

    public function setExpectedCorrectionTotal($expectedCorrectionTotal)
    {
        $this->expectedCorrectionTotal = $expectedCorrectionTotal;
    }

    public function getDisplayNotationToLearners()
    {
        return $this->displayNotationToLearners;
    }

    public function setDisplayNotationToLearners($displayNotationToLearners)
    {
        $this->displayNotationToLearners = $displayNotationToLearners;
    }

    public function getDisplayNotationMessageToLearners()
    {
        return $this->displayNotationMessageToLearners;
    }

    public function setDisplayNotationMessageToLearners($displayNotationMessageToLearners)
    {
        $this->displayNotationMessageToLearners = $displayNotationMessageToLearners;
    }

    public function getScoreMax()
    {
        return $this->scoreMax;
    }

    public function setScoreMax($scoreMax)
    {
        $this->scoreMax = $scoreMax;
    }

    public function getScoreToPass()
    {
        return $this->scoreToPass;
    }

    public function setScoreToPass($scoreToPass)
    {
        $this->scoreToPass = $scoreToPass;
    }

    public function getDropType()
    {
        return $this->dropType;
    }

    public function setDropType($dropType)
    {
        $this->dropType = $dropType;
    }

    public function getManualPlanning()
    {
        return $this->manualPlanning;
    }

    public function setManualPlanning($manualPlanning)
    {
        $this->manualPlanning = $manualPlanning;
    }

    public function getManualState()
    {
        return $this->manualState;
    }

    public function setManualState($manualState)
    {
        $this->manualState = $manualState;
    }

    public function getDropStartDate()
    {
        return $this->dropStartDate;
    }

    public function setDropStartDate(\DateTime $dropStartDate = null)
    {
        $this->dropStartDate = $dropStartDate;
    }

    public function getDropEndDate()
    {
        return $this->dropEndDate;
    }

    public function setDropEndDate(\DateTime $dropEndDate = null)
    {
        $this->dropEndDate = $dropEndDate;
    }

    public function getReviewStartDate()
    {
        return $this->reviewStartDate;
    }

    public function setReviewStartDate(\DateTime $reviewStartDate = null)
    {
        $this->reviewStartDate = $reviewStartDate;
    }

    public function getReviewEndDate()
    {
        return $this->reviewEndDate;
    }

    public function setReviewEndDate(\DateTime $reviewEndDate = null)
    {
        $this->reviewEndDate = $reviewEndDate;
    }

    public function isCommentInCorrectionEnabled()
    {
        return $this->commentInCorrectionEnabled;
    }

    public function setCommentInCorrectionEnabled($commentInCorrectionEnabled)
    {
        $this->commentInCorrectionEnabled = $commentInCorrectionEnabled;
    }

    public function isCommentInCorrectionForced()
    {
        return $this->commentInCorrectionForced;
    }

    public function setCommentInCorrectionForced($commentInCorrectionForced)
    {
        $this->commentInCorrectionForced = $commentInCorrectionForced;
    }

    public function getDisplayCorrectionsToLearners()
    {
        return $this->displayCorrectionsToLearners;
    }

    public function setDisplayCorrectionsToLearners($displayCorrectionsToLearners)
    {
        $this->displayCorrectionsToLearners = $displayCorrectionsToLearners;
    }

    public function isCorrectionDenialEnabled()
    {
        return $this->correctionDenialEnabled;
    }

    public function setCorrectionDenialEnabled($correctionDenialEnabled)
    {
        $this->correctionDenialEnabled = $correctionDenialEnabled;
    }

    public function isCriteriaEnabled()
    {
        return $this->criteriaEnabled;
    }

    public function setCriteriaEnabled($criteriaEnabled)
    {
        $this->criteriaEnabled = $criteriaEnabled;
    }

    public function getCriteriaTotal()
    {
        return $this->criteriaTotal;
    }

    public function setCriteriaTotal($criteriaTotal)
    {
        $this->criteriaTotal = $criteriaTotal;
    }

    public function getAutoCloseDropsAtDropEndDate()
    {
        return $this->autoCloseDropsAtDropEndDate;
    }

    public function setAutoCloseDropsAtDropEndDate($autoCloseDropsAtDropEndDate)
    {
        $this->autoCloseDropsAtDropEndDate = $autoCloseDropsAtDropEndDate;
    }

    public function getAutoCloseState()
    {
        return $this->autoCloseState;
    }

    public function setAutoCloseState($autoCloseState)
    {
        $this->autoCloseState = $autoCloseState;
    }

    public function getDropClosed()
    {
        return $this->dropClosed;
    }

    public function setDropClosed($dropClosed)
    {
        $this->dropClosed = $dropClosed;
    }

    public function getNotifyOnDrop()
    {
        return $this->notifyOnDrop;
    }

    public function setNotifyOnDrop($notifyOnDrop)
    {
        $this->notifyOnDrop = $notifyOnDrop;
    }

    /**
     * @return Criterion[]
     */
    public function getCriteria()
    {
        return $this->criteria->toArray();
    }

    public function emptyCriteria()
    {
        return $this->criteria->clear();
    }

    public function addCriterion(Criterion $criterion)
    {
        if (!$this->criteria->contains($criterion)) {
            $this->criteria->add($criterion);
            $criterion->setDropzone($this);
        }
    }

    public function removeCriterion(Criterion $criterion)
    {
        if ($this->criteria->contains($criterion)) {
            $this->criteria->removeElement($criterion);
        }
    }

    public function getAllowedDocuments()
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

    public function setAllowedDocuments(array $allowedDocuments)
    {
        $this->uploadEnabled = in_array('file', $allowedDocuments);
        $this->richTextEnabled = in_array('html', $allowedDocuments);
        $this->urlEnabled = in_array('url', $allowedDocuments);
        $this->workspaceResourceEnabled = in_array('resource', $allowedDocuments);
    }

    public function isDropEnabled()
    {
        $currentDate = new \DateTime();

        return (
            $this->manualPlanning &&
            in_array($this->manualState, [self::STATE_ALLOW_DROP, self::STATE_ALLOW_DROP_AND_PEER_REVIEW])
        ) || (
            !$this->manualPlanning &&
            (!empty($this->dropStartDate) && $currentDate >= $this->dropStartDate) &&
            (!empty($this->dropEndDate) && $currentDate <= $this->dropEndDate)
        );
    }

    public function isReviewEnabled()
    {
        $currentDate = new \DateTime();

        return $this->peerReview && ((
            $this->manualPlanning &&
            in_array($this->manualState, [self::STATE_PEER_REVIEW, self::STATE_ALLOW_DROP_AND_PEER_REVIEW])
        ) || (
            !$this->manualPlanning &&
            (!empty($this->reviewStartDate) && $currentDate >= $this->reviewStartDate) &&
            (!empty($this->reviewEndDate) && $currentDate <= $this->reviewEndDate)
        ));
    }

    public function isCorrectorDisplayed()
    {
        return $this->correctorDisplayed;
    }

    public function setCorrectorDisplayed($correctorDisplayed)
    {
        $this->correctorDisplayed = $correctorDisplayed;
    }

    public function isRevisionEnabled()
    {
        return $this->revisionEnabled;
    }

    public function setRevisionEnabled($revisionEnabled)
    {
        $this->revisionEnabled = $revisionEnabled;
    }

    public function hasLockDrops(): bool
    {
        return $this->lockDrops;
    }

    public function setLockDrops(bool $locked)
    {
        $this->lockDrops = $locked;
    }
}
