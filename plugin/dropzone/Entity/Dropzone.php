<?php
/**
 * Created by : Vincent SAISSET
 * Date: 21/08/13
 * Time: 15:18.
 */

namespace Icap\DropzoneBundle\Entity;

use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Doctrine\Common\Collections\ArrayCollection;
use Claroline\CoreBundle\Entity\Resource\AbstractResource;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="icap__dropzonebundle_dropzone")
 */
class Dropzone extends AbstractResource
{
    const MANUAL_STATE_NOT_STARTED = 'notStarted';
    const MANUAL_STATE_PEER_REVIEW = 'peerReview';
    const MANUAL_STATE_ALLOW_DROP = 'allowDrop';
    const MANUAL_STATE_ALLOW_DROP_AND_PEER_REVIEW = 'allowDropAndPeerReview';
    const MANUAL_STATE_FINISHED = 'finished';

    const AUTO_CLOSED_STATE_WAITING = 'waiting';
    const AUTO_CLOSED_STATE_CLOSED = 'autoClosed';

    /**
     * 1 = common
     * 2 = criteria
     * 3 = participant
     * 4 = finished.
     *
     * @ORM\Column(name="edition_state", type="smallint", nullable=false)
     */
    protected $editionState = 1;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    protected $instruction = null;

    /**
     * @ORM\Column(name="correction_instruction",type="text", nullable=true)
     */
    protected $correctionInstruction = null;

    /**
     * @ORM\Column(name="success_message",type="text", nullable=true)
     */
    protected $successMessage = null;

    /**
     * @ORM\Column(name="fail_message",type="text", nullable=true)
     */
    protected $failMessage = null;

    /**
     * @ORM\Column(name="allow_workspace_resource", type="boolean", nullable=false)
     */
    protected $allowWorkspaceResource = false;

    /**
     * @ORM\Column(name="allow_upload", type="boolean", nullable=false)
     */
    protected $allowUpload = true;

    /**
     * @ORM\Column(name="allow_url", type="boolean", nullable=false)
     */
    protected $allowUrl = false;

    /**
     * @ORM\Column(name="allow_rich_text", type="boolean", nullable=false)
     */
    protected $allowRichText = false;

    /**
     * @ORM\Column(name="peer_review", type="boolean", nullable=false)
     */
    protected $peerReview = false;

    /**
     * @ORM\Column(name="expected_total_correction", type="smallint", nullable=false)
     * @Assert\Range(
     *      min = 1,
     *      max = 10
     * )
     */
    protected $expectedTotalCorrection = 3;

    /**
     * @ORM\Column(name="display_notation_to_learners", type="boolean", nullable=false)
     */
    protected $displayNotationToLearners = true;

    /**
     * @ORM\Column(name="display_notation_message_to_learners", type="boolean", nullable=false)
     */
    protected $displayNotationMessageToLearners = false;

    /**
     * @ORM\Column(name="minimum_score_to_pass", type="float", nullable=false)
     * @Assert\Range(
     *      min = 0,
     *      max = 20
     * )
     */
    protected $minimumScoreToPass = 10;

    /**
     * @ORM\Column(name="manual_planning", type="boolean", nullable=false)
     */
    protected $manualPlanning = true;

    /**
     * @ORM\Column(name="manual_state", type="string", nullable=false)
     */
    protected $manualState = 'notStarted';

    /**
     * @ORM\Column(name="start_allow_drop", type="datetime", nullable=true)
     */
    protected $startAllowDrop = null;

    /**
     * @ORM\Column(name="end_allow_drop", type="datetime", nullable=true)
     */
    protected $endAllowDrop = null;

    /**
     * @ORM\Column(name="start_review", type="datetime", nullable=true)
     */
    protected $startReview = null;

    /**
     * @ORM\Column(name="end_review", type="datetime", nullable=true)
     */
    protected $endReview = null;

    /**
     * @ORM\Column(name="allow_comment_in_correction", type="boolean", nullable=false)
     */
    protected $allowCommentInCorrection = false;

    /**
     * Rendre le champ de commentaire dans la correction obligatoire.
     *
     * @var bool
     *
     * @ORM\Column(name="force_comment_in_correction",type="boolean",nullable=false)
     */
    protected $forceCommentInCorrection = false;

    /**
     * Defini si oui ou non les corrections faites par les pairs sont visibles par le possesseur de la copie corrigé.
     * les corrections devront cependant rester anonyme.
     *
     * @var bool
     * @ORM\Column(name="diplay_corrections_to_learners",type="boolean",nullable=false)
     */
    protected $diplayCorrectionsToLearners = false;

    /**
     * Depend on diplayCorrectionsToLearners, need diplayCorrectionsToLearners to be true in order to work.
     * Allow users to flag that they are not agree with the correction.
     *
     * @var bool
     * @ORM\Column(name="allow_correction_deny",type="boolean",nullable=false)
     */
    protected $allowCorrectionDeny = false;

    /**
     * @ORM\Column(name="total_criteria_column", type="smallint", nullable=false)
     * @Assert\LessThanOrEqual(value=10)
     * @Assert\GreaterThanOrEqual(value=3)
     */
    protected $totalCriteriaColumn = 4;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(
     *     targetEntity="Icap\DropzoneBundle\Entity\Drop",
     *     mappedBy="dropzone",
     *     cascade={"all"},
     *     orphanRemoval=true
     * )
     */
    protected $drops;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(
     *     targetEntity="Icap\DropzoneBundle\Entity\Criterion",
     *     mappedBy="dropzone",
     *     cascade={"all"},
     *     orphanRemoval=true
     * )
     */
    protected $peerReviewCriteria;

    /**
     * if true,
     * when time is up, all drop not already closed will be closed and flaged as uncompletedDrop.
     * That will allow them to access the next step ( correction by users or admins ).
     *
     * @var bool
     * @ORM\Column(name="auto_close_opened_drops_when_time_is_up",type="boolean",nullable=false,options={"default" = 0})
     */
    protected $autoCloseOpenedDropsWhenTimeIsUp = 0;

    /**
     * @var string
     * @ORM\Column(name="auto_close_state",type="string",nullable=false,options={"default" = "waiting"})
     */
    protected $autoCloseState = self::AUTO_CLOSED_STATE_WAITING;

    /**
     * @ORM\OneToOne(
     *      targetEntity="Claroline\CoreBundle\Entity\Resource\ResourceNode",
     *      cascade={"all"}
     * )
     * @ORM\JoinColumn(name="hidden_directory_id", referencedColumnName="id", nullable=true)
     */
    protected $hiddenDirectory;

    /**
     * @var bool
     *           Notify Evaluation admins when a someone made a drop
     *
     * @ORM\Column(name="notify_on_drop",type="boolean",nullable=false,options={"default" = false})
     */
    protected $notifyOnDrop = false;

    /**
     * @var Event
     *            Event for Workspace Agenda linked to DROP phase
     *
     * @ORM\OneToOne(
     *    targetEntity="Claroline\AgendaBundle\Entity\Event",cascade={"remove"})
     * @ORM\JoinColumn(name="event_agenda_drop",onDelete="SET NULL")
     * )
     */
    protected $eventDrop = null;

    /**
     * @var Event
     *            Event for Workspace Agenda linked to Correction phase.
     *
     * @ORM\OneToOne(
     *    targetEntity="Claroline\AgendaBundle\Entity\Event",cascade={"remove"})
     * @ORM\JoinColumn(name="event_agenda_correction",onDelete="SET NULL")
     */
    protected $eventCorrection = null;

    public function __construct()
    {
        $this->drops = new ArrayCollection();
        $this->peerReviewCriteria = new ArrayCollection();
    }

    /**
     * @param \Claroline\AgendaBundle\Entity\Event; $eventCorrection
     */
    public function setEventCorrection($eventCorrection)
    {
        $this->eventCorrection = $eventCorrection;
    }

    /**
     * @return \Claroline\AgendaBundle\Entity\Event;
     */
    public function getEventCorrection()
    {
        return $this->eventCorrection;
    }

    /**
     * @param Claroline\AgendaBundle\Entity\Event; $eventDrop
     */
    public function setEventDrop($eventDrop)
    {
        $this->eventDrop = $eventDrop;
    }

    /**
     * @return \Claroline\AgendaBundle\Entity\Event;
     */
    public function getEventDrop()
    {
        return $this->eventDrop;
    }

    /**
     * @param mixed $editionState
     */
    public function setEditionState($editionState)
    {
        $this->editionState = $editionState;
    }

    /**
     * @return mixed
     */
    public function getEditionState()
    {
        return $this->editionState;
    }

    /**
     * @return mixed
     */
    public function getTotalCriteriaColumn()
    {
        return $this->totalCriteriaColumn;
    }

    /**
     * @param mixed $totalCriteriaColumn
     */
    public function setTotalCriteriaColumn($totalCriteriaColumn)
    {
        $this->totalCriteriaColumn = $totalCriteriaColumn;
    }

    /**
     * @return mixed
     */
    public function getAllowCommentInCorrection()
    {
        return $this->allowCommentInCorrection;
    }

    /**
     * @param mixed $allowCommentInCorrection
     */
    public function setAllowCommentInCorrection($allowCommentInCorrection)
    {
        $this->allowCommentInCorrection = $allowCommentInCorrection;

        // when there is no comment allowed, the comment can't be mandatory
        if ($this->getForceCommentInCorrection()) {
            $this->setForceCommentInCorrection(false);
        }
    }

    /**
     * when there is no comment allowed, the comment can't be mandatory.
     *
     * @param bool $forceCommentInCorrection
     */
    public function setForceCommentInCorrection($forceCommentInCorrection)
    {
        if ($this->getAllowCommentInCorrection() == true) {
            $this->forceCommentInCorrection = $forceCommentInCorrection;
        } else {
            $this->forceCommentInCorrection = false;
        }
    }

    /**
     * @return bool
     */
    public function getForceCommentInCorrection()
    {
        return $this->forceCommentInCorrection;
    }

    /**
     * @return mixed
     */
    public function getAllowUpload()
    {
        return $this->allowUpload;
    }

    /**
     * @param mixed $allowUpload
     */
    public function setAllowUpload($allowUpload)
    {
        $this->allowUpload = $allowUpload;
    }

    /**
     * @return mixed
     */
    public function getAllowUrl()
    {
        return $this->allowUrl;
    }

    /**
     * @param mixed $allowUrl
     */
    public function setAllowUrl($allowUrl)
    {
        $this->allowUrl = $allowUrl;
    }

    /**
     * @return mixed
     */
    public function getAllowWorkspaceResource()
    {
        return $this->allowWorkspaceResource;
    }

    /**
     * @param mixed $allowWorkspaceResource
     */
    public function setAllowWorkspaceResource($allowWorkspaceResource)
    {
        $this->allowWorkspaceResource = $allowWorkspaceResource;
    }

    /**
     * @return mixed
     */
    public function getEndAllowDrop()
    {
        return $this->endAllowDrop;
    }

    /**
     * @param mixed $endAllowDrop
     */
    public function setEndAllowDrop($endAllowDrop)
    {
        $this->endAllowDrop = $endAllowDrop;
    }

    /**
     * @return mixed
     */
    public function getEndReview()
    {
        return $this->endReview;
    }

    /**
     * @param mixed $endReview
     */
    public function setEndReview($endReview)
    {
        $this->endReview = $endReview;
    }

    /**
     * @return mixed
     */
    public function getExpectedTotalCorrection()
    {
        return $this->expectedTotalCorrection;
    }

    /**
     * @param mixed $expectedTotalCorrection
     */
    public function setExpectedTotalCorrection($expectedTotalCorrection)
    {
        $this->expectedTotalCorrection = $expectedTotalCorrection;
    }

    /**
     * @return mixed
     */
    public function getInstruction()
    {
        return $this->instruction;
    }

    /**
     * @param mixed $instruction
     */
    public function setInstruction($instruction)
    {
        $this->instruction = $instruction;
    }

    /**
     * @return text
     */
    public function getCorrectionInstruction()
    {
        return $this->correctionInstruction;
    }

    /**
     * @param text $instruction
     */
    public function setCorrectionInstruction($instruction)
    {
        $this->correctionInstruction = $instruction;
    }

    /**
     * @return text
     */
    public function getSuccessMessage()
    {
        return $this->successMessage;
    }

    /**
     * @param text $message
     */
    public function setSuccessMessage($message)
    {
        $this->successMessage = $message;
    }

    /**
     * @return text
     */
    public function getFailMessage()
    {
        return $this->failMessage;
    }

    /**
     * @param text $message
     */
    public function setFailMessage($message)
    {
        $this->failMessage = $message;
    }

    /**
     * @return mixed
     */
    public function getManualPlanning()
    {
        return $this->manualPlanning;
    }

    /**
     * @param mixed $manualPlanning
     */
    public function setManualPlanning($manualPlanning)
    {
        $this->manualPlanning = $manualPlanning;
    }

    /**
     * @return mixed
     */
    public function getManualState()
    {
        return $this->manualState;
    }

    /**
     * @param mixed $manualState
     */
    public function setManualState($manualState)
    {
        $ms_tab_values = array(
            self::MANUAL_STATE_NOT_STARTED,
            self::MANUAL_STATE_PEER_REVIEW,
            self::MANUAL_STATE_ALLOW_DROP,
            self::MANUAL_STATE_ALLOW_DROP_AND_PEER_REVIEW,
            self::MANUAL_STATE_FINISHED, );
        if (array_search($manualState, $ms_tab_values) !== false) {
            $this->manualState = $manualState;
        }
    }

    /**
     * @return mixed
     */
    public function getPeerReview()
    {
        return $this->peerReview;
    }

    /**
     * @param mixed $peerReview
     */
    public function setPeerReview($peerReview)
    {
        $this->peerReview = $peerReview;
    }

    /**
     * @return mixed
     */
    public function getStartAllowDrop()
    {
        return $this->startAllowDrop;
    }

    /**
     * @param mixed $startAllowDrop
     */
    public function setStartAllowDrop($startAllowDrop)
    {
        $this->startAllowDrop = $startAllowDrop;
    }

    /**
     * @return ArrayCollection
     */
    public function getDrops()
    {
        return $this->drops;
    }

    /**
     * @param mixed $drops
     */
    public function setDrops($drops)
    {
        $this->drops = $drops;
    }

    /**
     * @return ArrayCollection
     */
    public function getPeerReviewCriteria()
    {
        return $this->peerReviewCriteria;
    }

    /**
     * @param mixed $peerReviewCriteria
     */
    public function setPeerReviewCriteria($peerReviewCriteria)
    {
        $this->peerReviewCriteria = $peerReviewCriteria;
    }

    /**
     * @return mixed
     */
    public function getDisplayNotationMessageToLearners()
    {
        return $this->displayNotationMessageToLearners;
    }

    /**
     * @param mixed $displayNotationMessageToLearners
     */
    public function setDisplayNotationMessageToLearners($displayNotationMessageToLearners)
    {
        $this->displayNotationMessageToLearners = $displayNotationMessageToLearners;
    }

    /**
     * @return mixed
     */
    public function getDisplayNotationToLearners()
    {
        return $this->displayNotationToLearners;
    }

    /**
     * @param mixed $displayNotationToLearners
     */
    public function setDisplayNotationToLearners($displayNotationToLearners)
    {
        $this->displayNotationToLearners = $displayNotationToLearners;
    }

    /**
     * @return mixed
     */
    public function getDiplayCorrectionsToLearners()
    {
        return $this->diplayCorrectionsToLearners;
    }

    /**
     * @param $diplayCorrectionsToLearners
     *
     * @internal param bool $displayNotationToLearners
     */
    public function setDiplayCorrectionsToLearners($diplayCorrectionsToLearners)
    {
        $this->diplayCorrectionsToLearners = $diplayCorrectionsToLearners;
    }

    /**
     * @return bool
     **/
    public function getAllowCorrectionDeny()
    {
        return $this->allowCorrectionDeny;
    }

    public function setAllowCorrectionDeny($allowCorrectionDeny)
    {
        $this->allowCorrectionDeny = $allowCorrectionDeny;
    }

    /**
     * @return mixed
     */
    public function getMinimumScoreToPass()
    {
        return $this->minimumScoreToPass;
    }

    /**
     * @param mixed $minimumScoreToPass
     */
    public function setMinimumScoreToPass($minimumScoreToPass)
    {
        $this->minimumScoreToPass = $minimumScoreToPass;
    }

    /**
     * @param mixed $startReview
     */
    public function setStartReview($startReview)
    {
        $this->startReview = $startReview;
    }

    /**
     * @return mixed
     */
    public function getStartReview()
    {
        return $this->startReview;
    }

    /**
     * @param mixed $allowRichText
     */
    public function setAllowRichText($allowRichText)
    {
        $this->allowRichText = $allowRichText;
    }

    /**
     * @return mixed
     */
    public function getAllowRichText()
    {
        return $this->allowRichText;
    }

    /**
     * @param ResourceNode $hiddenDirectory
     */
    public function setHiddenDirectory($hiddenDirectory)
    {
        $this->hiddenDirectory = $hiddenDirectory;
    }

    /**
     * @return ResourceNode
     */
    public function getHiddenDirectory()
    {
        return $this->hiddenDirectory;
    }

    public function isNotStarted()
    {
        if ($this->manualPlanning) {
            return $this->manualState == $this::MANUAL_STATE_NOT_STARTED;
        } else {
            $now = new \DateTime();

            return $now->getTimestamp() < $this->getStartAllowDrop()->getTimestamp() && ($this->getStartReview() == null || $now->getTimestamp() < $this->getStartReview()->getTimestamp());
        }
    }

    public function isAllowDrop()
    {
        if ($this->manualPlanning) {
            return $this->manualState == $this::MANUAL_STATE_ALLOW_DROP || $this->manualState == $this::MANUAL_STATE_ALLOW_DROP_AND_PEER_REVIEW;
        } else {
            $now = new \DateTime();

            return $now->getTimestamp() >= $this->startAllowDrop->getTimestamp() && $now->getTimestamp() < $this->endAllowDrop->getTimestamp();
        }
    }

    /**
     * Only return if we are in a peerReview phase, if you want to get the evaluation mode
     * do dropzone->peerReview().
     *
     * @return bool
     */
    public function isPeerReview()
    {
        if ($this->peerReview) {
            if ($this->manualPlanning) {
                return $this->manualState == $this::MANUAL_STATE_PEER_REVIEW || $this->manualState == $this::MANUAL_STATE_ALLOW_DROP_AND_PEER_REVIEW;
            } else {
                $now = new \DateTime();

                return $this->startReview != null && $this->endReview != null && $now->getTimestamp() >= $this->startReview->getTimestamp() && $now->getTimestamp() < $this->endReview->getTimestamp();
            }
        } else {
            return false;
        }
    }

    public function isFinished()
    {
        if ($this->manualPlanning) {
            return $this->manualState == $this::MANUAL_STATE_FINISHED;
        } else {
            $now = new \DateTime();

            $finished = $allowDropEnd = $now->getTimestamp() > $this->endAllowDrop->getTimestamp();

            if ($this->isPeerReview()) {
                $finished = $allowDropEnd && $now->getTimestamp() > $this->endReview->getTimestamp();
            }

            return $finished;
        }
    }

    public function getTimeRemaining($reference)
    {
        if ($this->manualPlanning || $reference == null) {
            return -1;
        }
        $now = new \DateTime();
        if ($now->getTimestamp() < $reference->getTimestamp()) {
            return $reference->getTimestamp() - $now->getTimestamp();
        } else {
            return 0;
        }
    }

    public function getTimeRemainingBeforeStartAllowDrop()
    {
        return $this->getTimeRemaining($this->startAllowDrop);
    }

    public function getTimeRemainingBeforeEndAllowDrop()
    {
        return $this->getTimeRemaining($this->endAllowDrop);
    }

    public function getTimeRemainingBeforeStartReview()
    {
        if ($this->peerReview) {
            return $this->getTimeRemaining($this->startReview);
        } else {
            return -1;
        }
    }

    public function getTimeRemainingBeforeEndReview()
    {
        if ($this->peerReview) {
            return $this->getTimeRemaining($this->endReview);
        } else {
            return -1;
        }
    }

    public function hasCriteria()
    {
        return count($this->getPeerReviewCriteria()) > 0;
    }

    /**
     * Add criterion.
     *
     * @param \Icap\DropzoneBundle\Entity\Criterion $criterion
     *
     * @return Dropzone
     */
    public function addCriterion(Criterion $criterion)
    {
        $criterion->setDropzone($this);
        $this->peerReviewCriteria[] = $criterion;

        return $this;
    }

    /**
     * @param bool $autoCloseOpenedDropsWhenTimeIsUp
     */
    public function setAutoCloseOpenedDropsWhenTimeIsUp($autoCloseOpenedDropsWhenTimeIsUp)
    {
        $this->autoCloseOpenedDropsWhenTimeIsUp = $autoCloseOpenedDropsWhenTimeIsUp;
    }

    /**
     * @return bool
     */
    public function getAutoCloseOpenedDropsWhenTimeIsUp()
    {
        return $this->autoCloseOpenedDropsWhenTimeIsUp;
    }

    /**
     * Param that indicate if all drop have already been auto closed or not.
     *
     * @param string $autoCloseState
     */
    public function setAutoCloseState($autoCloseState)
    {
        $authorizedValues = array(self::AUTO_CLOSED_STATE_CLOSED, self::AUTO_CLOSED_STATE_WAITING);
        if (in_array($autoCloseState, $authorizedValues)) {
            $this->autoCloseState = $autoCloseState;
        }
    }

    /**
     * @return string
     */
    public function getAutoCloseState()
    {
        return $this->autoCloseState;
    }

    /**
     * @param bool $notifyOnDrop
     */
    public function setNotifyOnDrop($notifyOnDrop)
    {
        $this->notifyOnDrop = $notifyOnDrop;
    }

    /**
     * @return bool
     */
    public function getNotifyOnDrop()
    {
        return $this->notifyOnDrop;
    }
}
