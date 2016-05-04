<?php

namespace UJM\ExoBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * UJM\ExoBundle\Entity\Step.
 *
 * @ORM\Entity(repositoryClass="UJM\ExoBundle\Repository\StepRepository")
 * @ORM\Table(name="ujm_step")
 */
class Step
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="value", type="string", length=255)
     */
    private $text;

    /**
     * @var int
     *
     * @ORM\Column(name="nbQuestion", type="integer")
     */
    private $nbQuestion = 0;

    /**
     * @ORM\Column(name="keepSameQuestion", type="boolean", nullable=true)
     */
    private $keepSameQuestion;

    /**
     * @var bool
     *
     * @ORM\Column(name="shuffle", type="boolean", nullable=true)
     */
    private $shuffle = false;

    /**
     * @var int
     *
     * @ORM\Column(name="duration", type="integer")
     */
    private $duration = 0;

    /**
     * @var int
     *
     * @ORM\Column(name="max_attempts", type="integer")
     */
    private $maxAttempts = 5;

    /**
     * @var int
     *
     * @ORM\Column(name="ordre", type="integer")
     */
    private $order;

    /**
     * @ORM\ManyToOne(targetEntity="Exercise", inversedBy="steps")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private $exercise;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(
     *     targetEntity="StepQuestion",
     *     mappedBy="step",
     * )
     */
    private $stepQuestions;

    public function __construct()
    {
        $this->stepQuestions = new ArrayCollection();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set text.
     *
     * @param string $text
     */
    public function setText($text)
    {
        $this->text = $text;
    }

    /**
     * Get text.
     *
     * @return string
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * Set nbQuestion.
     *
     * @param int $nbQuestion
     */
    public function setNbQuestion($nbQuestion)
    {
        $this->nbQuestion = $nbQuestion;
    }

    /**
     * Get nbQuestion.
     *
     * @return int
     */
    public function getNbQuestion()
    {
        return $this->nbQuestion;
    }

    /**
     * Set keepSameQuestion.
     *
     * @param bool $keepSameQuestion
     */
    public function setKeepSameQuestion($keepSameQuestion)
    {
        $this->keepSameQuestion = $keepSameQuestion;
    }

    /**
     * Get keepSameQuestion.
     */
    public function getKeepSameQuestion()
    {
        return $this->keepSameQuestion;
    }

    /**
     * Set shuffle.
     *
     * @param bool $shuffle
     */
    public function setShuffle($shuffle)
    {
        $this->shuffle = $shuffle;
    }

    /**
     * Get shuffle.
     */
    public function getShuffle()
    {
        return $this->shuffle;
    }

    /**
     * Set duration.
     *
     * @param int $duration
     */
    public function setDuration($duration)
    {
        $this->duration = $duration;
    }

    /**
     * Get duration.
     *
     * @return int
     */
    public function getDuration()
    {
        return $this->duration;
    }

    /**
     * Set maxAttempts.
     *
     * @param int $maxAttempts
     */
    public function setMaxAttempts($maxAttempts)
    {
        $this->maxAttempts = $maxAttempts;
    }

    /**
     * Get maxAttempts.
     *
     * @return int
     */
    public function getMaxAttempts()
    {
        return $this->maxAttempts;
    }

    /**
     * Set order.
     *
     * @param int $order
     */
    public function setOrder($order)
    {
        $this->order = $order;
    }

    /**
     * Get order.
     *
     * @return int
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * @param Exercise $exercise
     */
    public function setExercise(Exercise $exercise)
    {
        $this->exercise = $exercise;
    }

    /**
     * @return Exercise
     */
    public function getExercise()
    {
        return $this->exercise;
    }

    /**
     * @return ArrayCollection
     */
    public function getStepQuestions()
    {
        return $this->stepQuestions;
    }

    /**
     * @param StepQuestion $stepQuestion
     */
    public function addStepQuestion(StepQuestion $stepQuestion)
    {
        if (!$this->stepQuestions->contains($stepQuestion)) {
            $this->stepQuestions->add($stepQuestion);
        }
    }

    /**
     * @param StepQuestion $stepQuestion
     */
    public function removeStepQuestion(StepQuestion $stepQuestion)
    {
        if ($this->stepQuestions->contains($stepQuestion)) {
            $this->stepQuestions->removeElement($stepQuestion);
        }
    }
}
