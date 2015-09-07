<?php

namespace UJM\ExoBundle\Entity;

use Claroline\CoreBundle\Entity\Resource\AbstractResource;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * UJM\ExoBundle\Entity\Exercise
 *
 * @ORM\Entity(repositoryClass="UJM\ExoBundle\Repository\ExerciseRepository")
 * @ORM\Table(name="ujm_exercise")
 */
class Exercise extends AbstractResource
{
    /**
     * @var string $title
     *
     * @ORM\Column(name="title", type="string", length=255)
     */
    private $title;

    /**
     * @var string $description
     *
     * @ORM\Column(name="description", type="text", nullable=true)
     */
    private $description;

    /**
     * @var boolean $shuffle
     *
     * @ORM\Column(name="shuffle", type="boolean", nullable=true)
     */
    private $shuffle = false;

    /**
     * @var integer $nbQuestion
     *
     * @ORM\Column(name="nb_question", type="integer")
     */
    private $nbQuestion = 0;

    /**
     * @var boolean $keepSameQuestion
     *
     * @ORM\Column(name="keepSameQuestion", type="boolean", nullable=true)
     */
    private $keepSameQuestion;

    /**
     * @var integer $duration
     *
     * @ORM\Column(name="duration", type="integer")
     */
    private $duration = 0;

    /**
     * @var integer $nbQuestionPage
     *
     * @ORM\Column(name="nb_question_page", type="integer")
     */
    private $nbQuestionPage = 0;

    /**
     * @var boolean $doprint
     *
     * @ORM\Column(name="doprint", type="boolean", nullable=true)
     */
    private $doprint = false;

    /**
     * @var integer $maxAttempts
     *
     * @ORM\Column(name="max_attempts", type="integer")
     */
    private $maxAttempts = 0;

    /**
     * @todo mode should be at least a class constant
     *
     * @var string $correctionMode
     *
     * @ORM\Column(name="correction_mode", type="string", length=255)
     */
    private $correctionMode = '1';

    /**
     * @var \Datetime $dateCorrection
     *
     * @ORM\Column(name="date_correction", type="datetime", nullable=true)
     */
    private $dateCorrection;

    /**
     * @todo mode should be at least a class constant
     *
     * @var string $markMode
     *
     * @ORM\Column(name="mark_mode", type="string", length=255)
     */
    private $markMode = '1';

    /**
     * @var boolean $dispButtonInterrupt
     *
     * @ORM\Column(name="disp_button_interrupt", type="boolean", nullable=true)
     */
    private $dispButtonInterrupt = false;

    /**
     * @var boolean $lockAttempt
     *
     * @ORM\Column(name="lock_attempt", type="boolean", nullable=true)
     */
    private $lockAttempt = false;

    /**
     * Flag indicating whether the exercise has been published at least
     * one time. An exercise that has never been published has all its
     * existing papers deleted at the first publication.
     *
     * @var boolean $waPublishedOnce
     *
     * @ORM\Column(name="published", type="boolean")
     */
    private $wasPublishedOnce = false;

    public function __construct()
    {
        $this->dateCorrection = new \DateTime();
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set title
     *
     * @param string $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set description
     *
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set shuffle
     *
     * @param boolean $shuffle
     */
    public function setShuffle($shuffle)
    {
        $this->shuffle = $shuffle;
    }

    /**
     * Get shuffle
     */
    public function getShuffle()
    {
        return $this->shuffle;
    }

    /**
     * Set nbQuestion
     *
     * @param integer $nbQuestion
     */
    public function setNbQuestion($nbQuestion)
    {
        $this->nbQuestion = $nbQuestion;
    }

    /**
     * Get nbQuestion
     *
     * @return integer
     */
    public function getNbQuestion()
    {
        return $this->nbQuestion;
    }

    /**
     * Set keepSameQuestion
     *
     * @param boolean $keepSameQuestion
     */
    public function setKeepSameQuestion($keepSameQuestion)
    {
        $this->keepSameQuestion = $keepSameQuestion;
    }

    /**
     * Get keepSameQuestion
     */
    public function getKeepSameQuestion()
    {
        return $this->keepSameQuestion;
    }

    /**
     * Set duration
     *
     * @param integer $duration
     */
    public function setDuration($duration)
    {
        $this->duration = $duration;
    }

    /**
     * Get duration
     *
     * @return integer
     */
    public function getDuration()
    {
        return $this->duration;
    }

    /**
     * Set nbQuestionPage
     *
     * @param integer $nbQuestionPage
     */
    public function setNbQuestionPage($nbQuestionPage)
    {
        $this->nbQuestionPage = $nbQuestionPage;
    }

    /**
     * Get nbQuestionPage
     *
     * @return integer
     */
    public function getNbQuestionPage()
    {
        return $this->nbQuestionPage;
    }

    /**
     * Set doprint
     *
     * @param boolean $doprint
     */
    public function setDoprint($doprint)
    {
        $this->doprint = $doprint;
    }

    /**
     * Get doprint
     */
    public function getDoprint()
    {
        return $this->doprint;
    }

    /**
     * Set maxAttempts
     *
     * @param integer $maxAttempts
     */
    public function setMaxAttempts($maxAttempts)
    {
        $this->maxAttempts = $maxAttempts;
    }

    /**
     * Get maxAttempts
     *
     * @return integer
     */
    public function getMaxAttempts()
    {
        return $this->maxAttempts;
    }

    /**
     * Set correctionMode
     *
     * @param string $correctionMode
     */
    public function setCorrectionMode($correctionMode)
    {
        $this->correctionMode = $correctionMode;
    }

    /**
     * Get correctionMode
     *
     * @return string
     */
    public function getCorrectionMode()
    {
        return $this->correctionMode;
    }

    /**
     * Set dateCorrection
     *
     * @param \Datetime $dateCorrection
     */
    public function setDateCorrection(\DateTime $dateCorrection)
    {
        $this->dateCorrection = $dateCorrection;
    }

    /**
     * Get dateCorrection
     *
     * @return \Datetime
     */
    public function getDateCorrection()
    {
        return $this->dateCorrection;
    }

    /**
     * Set markMode
     *
     * @param string $markMode
     */
    public function setMarkMode($markMode)
    {
        $this->markMode = $markMode;
    }

    /**
     * Get markMode
     *
     * @return string
     */
    public function getMarkMode()
    {
        return $this->markMode;
    }

    /**
     * Set dispButtonInterrupt
     *
     * @param boolean $dispButtonInterrupt
     */
    public function setDispButtonInterrupt($dispButtonInterrupt)
    {
        $this->dispButtonInterrupt = $dispButtonInterrupt;
    }

    /**
     * Get dispButtonInterrupt
     */
    public function getDispButtonInterrupt()
    {
        return $this->dispButtonInterrupt;
    }

    /**
     * Set lockAttempt
     *
     * @param boolean $lockAttempt
     */
    public function setLockAttempt($lockAttempt)
    {
        $this->lockAttempt = $lockAttempt;
    }

    /**
     * Get lockAttempt
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
     * @return boolean
     */
    public function wasPublishedOnce()
    {
        return $this->wasPublishedOnce;
    }

    /**
     * @param boolean $wasPublishedOnce
     */
    public function setPublishedOnce($wasPublishedOnce)
    {
        $this->wasPublishedOnce = $wasPublishedOnce;
    }
}
