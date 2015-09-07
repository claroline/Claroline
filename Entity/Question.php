<?php

namespace UJM\ExoBundle\Entity;

use Claroline\CoreBundle\Entity\Resource\AbstractResource;
use Doctrine\ORM\Mapping as ORM;

/**
 * UJM\ExoBundle\Entity\Exercise.
 *
 * @ORM\Entity(repositoryClass="UJM\ExoBundle\Repository\ExerciseRepository")
 * @ORM\Table(name="ujm_exercise")
 */
class Exercise extends AbstractResource
{
    /**
     * @var string
     *
     * @ORM\Column(name="title", type="string", length=255)
     */
    private $title;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text", nullable=true)
     */
    private $description;

    /**
     * @var bool
     *
     * @ORM\Column(name="shuffle", type="boolean", nullable=true)
     */
    private $shuffle = false;

    /**
     * @var int
     *
     * @ORM\Column(name="nb_question", type="integer")
     */
    private $nbQuestion = 0;

    /**
     * @var bool
     *
     * @ORM\Column(name="keepSameQuestion", type="boolean", nullable=true)
     */
    private $keepSameQuestion;

    /**
     * @var int
     *
     * @ORM\Column(name="duration", type="integer")
     */
    private $duration = 0;

    /**
     * @var boolean $doprint
     *
     * @ORM\Column(name="doprint", type="boolean", nullable=true)
     */
    private $doprint = false;

    /**
     * @var int
     *
     * @ORM\Column(name="max_attempts", type="integer")
     */
    private $maxAttempts = 0;

    /**
     * @todo mode should be at least a class constant
     *
     * @var string
     *
     * @ORM\Column(name="correction_mode", type="string", length=255)
     */
    private $correctionMode = '1';

    /**
     * @var \Datetime
     *
     * @ORM\Column(name="date_correction", type="datetime", nullable=true)
     */
    private $dateCorrection;

    /**
     * @todo mode should be at least a class constant
     *
     * @var string
     *
     * @ORM\Column(name="mark_mode", type="string", length=255)
     */
    private $markMode = '1';

    /**
     * @var bool
     *
     * @ORM\Column(name="disp_button_interrupt", type="boolean", nullable=true)
     */
    private $dispButtonInterrupt = false;

    /**
     * @var bool
     *
     * @ORM\Column(name="lock_attempt", type="boolean", nullable=true)
     */
    private $lockAttempt = false;

    /**
     * Flag indicating whether the exercise has been published at least
     * one time. An exercise that has never been published has all its
     * existing papers deleted at the first publication.
     *
     * @var bool
     *
     * @ORM\Column(name="published", type="boolean")
     */
    private $wasPublishedOnce = false;

    public function __construct()
    {
        $this->dateCorrection = new \DateTime();
    }

    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set title.
     *
     * @param string $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * Get title.
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set description.
     *
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * Get description.
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
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
     * Set doprint
     *
     * @param bool $doprint
     */
    public function setDoprint($doprint)
    {
        $this->doprint = $doprint;
    }

    /**
     * Get doprint.
     */
    public function getDoprint()
    {
        return $this->doprint;
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
     * Set correctionMode.
     *
     * @param string $correctionMode
     */
    public function setCorrectionMode($correctionMode)
    {
        $this->correctionMode = $correctionMode;
    }

    /**
     * Get correctionMode.
     *
     * @return string
     */
    public function getCorrectionMode()
    {
        return $this->correctionMode;
    }

    /**
     * Set dateCorrection.
     *
     * @param \Datetime $dateCorrection
     */
    public function setDateCorrection(\DateTime $dateCorrection = null)
    {
        $this->dateCorrection = $dateCorrection;
    }

    /**
     * Get dateCorrection.
     *
     * @return \Datetime
     */
    public function getDateCorrection()
    {
        return $this->dateCorrection;
    }

    /**
     * Set markMode.
     *
     * @param string $markMode
     */
    public function setMarkMode($markMode)
    {
        $this->markMode = $markMode;
    }

    /**
     * Get markMode.
     *
     * @return string
     */
    public function getMarkMode()
    {
        return $this->markMode;
    }

    /**
     * Set dispButtonInterrupt.
     *
     * @param bool $dispButtonInterrupt
     */
    public function setDispButtonInterrupt($dispButtonInterrupt)
    {
        $this->dispButtonInterrupt = $dispButtonInterrupt;
    }

    /**
     * Get dispButtonInterrupt.
     */
    public function getDispButtonInterrupt()
    {
        return $this->dispButtonInterrupt;
    }

    /**
     * Set lockAttempt.
     *
     * @param bool $lockAttempt
     */
    public function setLockAttempt($lockAttempt)
    {
        $this->lockAttempt = $lockAttempt;
    }

    /**
     * Get lockAttempt.
     */
    public function getLockAttempt()
    {
        return $this->lockAttempt;
    }

    public function archiveExercise()
    {
        $this->resourceNode = null;
    }

    /**
     * @return bool
     */
    public function wasPublishedOnce()
    {
        return $this->wasPublishedOnce;
    }

    /**
     * @param bool $wasPublishedOnce
     */
    public function setPublishedOnce($wasPublishedOnce)
    {
        $this->wasPublishedOnce = $wasPublishedOnce;
    }
}
