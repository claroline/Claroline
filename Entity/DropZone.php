<?php
/**
 * Created by : Vincent SAISSET
 * Date: 21/08/13
 * Time: 15:18
 */

namespace Icap\DropzoneBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Claroline\CoreBundle\Entity\Resource\AbstractResource;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints as DoctrineAssert;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="icap__dropzonebundle_dropzone")
 */
class Dropzone extends AbstractResource {

    /**
     * 1 = common
     * 2 = criteria
     * 3 = participant
     * 4 = finished
     *
     * @ORM\Column(name="edition_state", type="smallint", nullable=false)
     */
    protected $editionState = 1;
    /**
     * @ORM\Column(type="text", nullable=true)
     */
    protected $instruction = null;
    /**
     * @ORM\Column(name="allow_workspace_resource", type="boolean", nullable=false)
     */
    protected $allowWorkspaceResource = false;
    /**
     * @ORM\Column(name="allow_upload", type="boolean", nullable=false)
     */
    protected $allowUpload = false;
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
     * @Assert\LessThanOrEqual(value=10)
     * @Assert\GreaterThanOrEqual(value=1)
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
     * @ORM\Column(name="minimum_score_to_pass", type="smallint", nullable=false)
     * @Assert\LessThanOrEqual(value=20)
     * @Assert\GreaterThanOrEqual(value=0)
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
     * @ORM\Column(name="total_criteria_column", type="smallint", nullable=false)
     * @Assert\LessThanOrEqual(value=10)
     * @Assert\GreaterThanOrEqual(value=3)
     */
    protected $totalCriteriaColumn = 5;
    /**
     * @ORM\OneToMany(
     *     targetEntity="Icap\DropzoneBundle\Entity\Drop",
     *     mappedBy="dropzone",
     *     cascade={"all"},
     *     orphanRemoval=true
     * )
     */
    protected $drops;
    /**
     * @ORM\OneToMany(
     *     targetEntity="Icap\DropzoneBundle\Entity\Criterion",
     *     mappedBy="dropzone",
     *     cascade={"all"},
     *     orphanRemoval=true
     * )
     */
    protected $peerReviewCriteria;

    public function __construct()
    {
        $this->drops = new ArrayCollection();
        $this->peerReviewCriteria = new ArrayCollection();
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
    }

    /**
     * @return mixed
     */
    public function getAllowDropInReview()
    {
        return $this->allowDropInReview;
    }

    /**
     * @param mixed $allowDropInReview
     */
    public function setAllowDropInReview($allowDropInReview)
    {
        $this->allowDropInReview = $allowDropInReview;
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
        $this->manualState = $manualState;
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
     * @return mixed
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
     * @return mixed
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

    public function getPathArray()
    {
        $path = $this->getResourceNode()->getPath();
        $pathItems = explode("`", $path);
        $pathArray = array();
        foreach ($pathItems as $item) {
            preg_match("/-([0-9]+)$/", $item, $matches);
            if (count($matches) > 0) {
                $id = substr($matches[0], 1);
                $name = preg_replace("/-([0-9]+)$/", "", $item);
                $pathArray[] = array('id' => $id, 'name' => $name);
            }
        }

        return $pathArray;
    }

    public function isNotStarted()
    {
        if ($this->manualPlanning) {
            return $this->manualState == 'notStarted';
        } else {
            $now = new \DateTime();

            return $now->getTimestamp() < $this->startAllowDrop->getTimestamp() and $now->getTimestamp() < $this->startReview->getTimestamp();
        }
    }

    public function isAllowDrop()
    {
        if ($this->manualPlanning) {
            return $this->manualState == 'allowDrop';
        } else {
            $now = new \DateTime();

            return $now->getTimestamp() >= $this->startAllowDrop->getTimestamp() and $now->getTimestamp() < $this->endAllowDrop->getTimestamp();
        }
    }

    public function isPeerReview()
    {
        if ($this->peerReview) {
            if ($this->manualPlanning) {
                return $this->manualState == 'peerReview';
            } else {
                $now = new \DateTime();

                return $now->getTimestamp() >= $this->startReview->getTimestamp() and $now->getTimestamp() < $this->endReview->getTimestamp();
            }
        } else {
            return false;
        }
    }

    public function isFinished()
    {
        if ($this->manualPlanning) {
            return $this->manualState == 'finished';
        } else {
            $now = new \DateTime();

            return $now->getTimestamp() > $this->endAllowDrop->getTimestamp() and $now->getTimestamp() > $this->endReview->getTimestamp();
        }
    }

    public function getTimeRemaining($reference)
    {
        if ($this->manualPlanning || $reference == null) {
            return -1;
        }
        $now = new \DateTime();
        if ($now->getTimestamp() <$reference->getTimestamp()) {
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
}