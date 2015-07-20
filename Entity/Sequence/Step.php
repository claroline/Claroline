<?php

namespace UJM\ExoBundle\Entity\Sequence;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use UJM\ExoBundle\Entity\Sequence\Sequence;

use Doctrine\Common\Collections\ArrayCollection;

/**
 * Sequence Step Entity
 *
 * @ORM\Table(name="ujm_sequence_step")
 * @ORM\Entity
 */
class Step implements \JsonSerializable {

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var description
     *
     * @ORM\Column(name="description", type="text", nullable=true)
     */
    protected $description;

    /**
     * @var Number $position
     *
     * @ORM\Column(name="position", type="smallint")
     * @Assert\NotBlank
     */
    protected $position;

    /**
     * 
     * @var boolean
     * @ORM\Column(name="shuffle", type="boolean")
     */
    protected $shuffle;

    /**
     *
     * @var boolean
     * @ORM\Column(name="is_first", type="boolean") 
     */
    protected $isFirst;

    /**
     *
     * @var boolean
     * @ORM\Column(name="is_last", type="boolean") 
     */
    protected $isLast;

    /**
     * @var ExercisePlayer 
     * 
     * @ORM\ManyToOne(targetEntity="UJM\ExoBundle\Entity\Sequence\Sequence", inversedBy="steps")
     * @ORM\JoinColumn(name="sequence_id", nullable=false)
     */
    protected $sequence;

    /**
     * Mapping Entity which allow ordering informations
     * @ORM\OneToMany(targetEntity="UJM\ExoBundle\Entity\Sequence\StepQuestion", cascade={"all"}, orphanRemoval=true, mappedBy="step")
     * @ORM\OrderBy({"position" = "ASC"})
     */
    protected $stepQuestions;

    public function __construct() {
        $this->shuffle = false;
        $this->isFirst = false;
        $this->isLast = false;
        $this->stepQuestions = new ArrayCollection();
    }

    /**
     * Get page Id
     * @return integer
     */
    public function getId() {
        return $this->id;
    }

    public function getDescription() {
        return $this->description;
    }

    /**
     * 
     * @param string $description
     * @return \UJM\ExoBundle\Entity\Sequence\Step
     */
    public function setDescription($description) {
        $this->description = $description;
        return $this;
    }

    /**
     * 
     * @param Sequence $sequence
     * @return \UJM\ExoBundle\Entity\Sequence\Step
     */
    public function setSequence(Sequence $sequence) {
        $this->sequence = $sequence;
        return $this;
    }

    /**
     * 
     * @return sequence
     */
    public function getSequence() {
        return $this->sequence;
    }

    /**
     * 
     * @param integer $position
     * @return \UJM\ExoBundle\Entity\Sequence\Step
     */
    public function setPosition($position) {
        $this->position = $position;
        return $this;
    }

    /**
     * 
     * @return integer
     */
    public function getPosition() {
        return $this->position;
    }

    /**
     * 
     * @param boolean $shuffle
     * @return \UJM\ExoBundle\Entity\Sequence\Step
     */
    public function setShuffle($shuffle) {
        $this->shuffle = $shuffle;
        return $this;
    }

    /**
     * 
     * @return boolean
     */
    public function getShuffle() {
        return $this->shuffle;
    }

    /**
     * 
     * @param boolean $isLast
     * @return \UJM\ExoBundle\Entity\Sequence\Step
     */
    public function setIsLast($isLast) {
        $this->isLast = $isLast;
        return $this;
    }

    /**
     * 
     * @return boolean
     */
    public function getIsLast() {
        return $this->isLast;
    }

    /**
     * 
     * @param boolean $isFirst
     * @return \UJM\ExoBundle\Entity\Sequence\Step
     */
    public function setIsFirst($isFirst) {
        $this->isFirst = $isFirst;
        return $this;
    }

    /**
     * 
     * @return boolean
     */
    public function getIsFirst() {
        return $this->isFirst;
    }

    /**
     * 
     * @param \UJM\ExoBundle\Entity\Sequence\StepQuestion $stepQuestion
     * @return \UJM\ExoBundle\Entity\Sequence\Step
     */
    public function addStepQuestion(StepQuestion $stepQuestion) {
        $this->stepQuestions[] = $stepQuestion;
        return $this;
    }

    /**
     * 
     * @param \UJM\ExoBundle\Entity\Sequence\StepQuestion $stepQuestion
     * @return \UJM\ExoBundle\Entity\Sequence\Step
     */
    public function removeStepQuestion(StepQuestion $stepQuestion) {
        $this->stepQuestions->removeElement($stepQuestion);
        return $this;
    }

    /**
     * 
     * @return ArrayCollection
     */
    public function getStepQuestions() {
        return $this->stepQuestions;
    }

    public function jsonSerialize() {
        // TODO serialize questions arraycollection
        return array(
            'id' => $this->id,
            'position' => $this->position,
            'shuffle' => $this->shuffle,
            'isFirst' => $this->isFirst,
            'isLast' => $this->isLast,
            'description' => $this->description,
            'sequenceId' => $this->sequence->getId(),
            'questions' => $this->stepQuestions//count($this->stepQuestions) > 0 ? $this->stepQuestions : array()
        );
    }

}
