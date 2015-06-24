<?php

namespace UJM\ExoBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Claroline\CoreBundle\Entity\Resource\AbstractResource;

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
     * @var text $description
     *
     * @ORM\Column(name="description", type="text", nullable=true)
     */
    private $description;

    /**
     * @var boolean $shuffle
     *
     * @ORM\Column(name="shuffle", type="boolean", nullable=true)
     */
    private $shuffle;

    /**
     * @var integer $nbQuestion
     *
     * @ORM\Column(name="nb_question", type="integer")
     */
    private $nbQuestion;

    /**
     * @var boolean $keepSameQuestion
     *
     * @ORM\Column(name="keepSameQuestion", type="boolean", nullable=true)
     */
    private $keepSameQuestion;

    /**
     * @var datetime $dateCreate
     *
     * @ORM\Column(name="date_create", type="datetime")
     */
    private $dateCreate;

    /**
     * @var integer $duration
     *
     * @ORM\Column(name="duration", type="integer")
     */
    private $duration;

    /**
     * @var integer $nbQuestionPage
     *
     * @ORM\Column(name="nb_question_page", type="integer")
     */
    private $nbQuestionPage;

    /**
     * @var boolean $doprint
     *
     * @ORM\Column(name="doprint", type="boolean", nullable=true)
     */
    private $doprint;

    /**
     * @var integer $maxAttempts
     *
     * @ORM\Column(name="max_attempts", type="integer")
     */
    private $maxAttempts;

    /**
     * @var string $correctionMode
     *
     * @ORM\Column(name="correction_mode", type="string", length=255)
     */
    private $correctionMode;

    /**
     * @var datetime $dateCorrection
     *
     * @ORM\Column(name="date_correction", type="datetime", nullable=true)
     */
    private $dateCorrection;

    /**
     * @var string $markMode
     *
     * @ORM\Column(name="mark_mode", type="string", length=255)
     */
    private $markMode;

    /**
     * @var datetime $startDate
     *
     * @ORM\Column(name="start_date", type="datetime")
     */
    private $startDate;

    /**
     * @var boolean $useDateEnd
     *
     * @ORM\Column(name="use_date_end", type="boolean", nullable=true)
     */
    private $useDateEnd;

    /**
     * @var datetime $end_date
     *
     * @ORM\Column(name="end_date", type="datetime", nullable=true)
     */
    private $endDate;

    /**
     * @var boolean $dispButtonInterrupt
     *
     * @ORM\Column(name="disp_button_interrupt", type="boolean", nullable=true)
     */
    private $dispButtonInterrupt;

    /**
     * @var boolean $lockAttempt
     *
     * @ORM\Column(name="lock_attempt", type="boolean", nullable=true)
     */
    private $lockAttempt;

    /**
     * @ORM\ManyToMany(targetEntity="UJM\ExoBundle\Entity\Groupes")
     * @ORM\JoinTable(
     *     name="ujm_exercise_group",
     *     joinColumns={
     *         @ORM\JoinColumn(name="exercise_id", referencedColumnName="id")
     *     },
     *     inverseJoinColumns={
     *         @ORM\JoinColumn(name="group_id", referencedColumnName="id")}
     * )
     */
    private $groupes;

    /**
     * @var boolean $published
     *
     * @ORM\Column(name="published", type="boolean")
     */
    private $published;

    public function __construct()
    {
        $this->groupes = new \Doctrine\Common\Collections\ArrayCollection;
        $this->lockAttempt = false;
        $this->dispButtonInterrupt  = false;
        $this->doprint = false;
        $this->shuffle = false;
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
     * @param text $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * Get description
     *
     * @return text
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
     * Set dateCreate
     *
     * @param datetime $dateCreate
     */
    public function setDateCreate($dateCreate)
    {
        $this->dateCreate = $dateCreate;
    }

    /**
     * Get dateCreate
     *
     * @return datetime
     */
    public function getDateCreate()
    {
        return $this->dateCreate;
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
     * @param datetime $dateCorrection
     */
    public function setDateCorrection($dateCorrection)
    {
        $this->dateCorrection = $dateCorrection;
    }

    /**
     * Get dateCorrection
     *
     * @return datetime
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
     * Set startDate
     *
     * @param datetime $startDate
     */
    public function setStartDate($startDate)
    {
        $this->startDate = $startDate;
    }

    /**
     * Get startDate
     *
     * @return datetime
     */
    public function getStartDate()
    {
        return $this->startDate;
    }

     /**
     * Set useDateEnd
     *
     * @param boolean $useDateEnd
     */
    public function setUseDateEnd($useDateEnd)
    {
        $this->useDateEnd = $useDateEnd;
    }

    /**
     * Get useDateEnd
     */
    public function getUseDateEnd()
    {
        return $this->useDateEnd;
    }

    /**
     * Set endDate
     *
     * @param datetime $endDate
     */
    public function setEndDate($endDate)
    {
        $this->endDate = $endDate;
    }

    /**
     * Get endDate
     *
     * @return datetime
     */
    public function getEndDate()
    {
        return $this->endDate;
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

    /**
     * Gets an array of Groupes.
     *
     * @return array An array of Groupes objects
     */
    public function getGroupes()
    {
        return $this->groupes;
    }

    /**
     * Add Groupe
     *
     * @param UJM\ExoBundle\Entity\Groupes $Groupe
     */
    public function addGroupe(\UJM\ExoBundle\Entity\Groupes $groupe)
    {
        $this->groupes[] = $groupe;
    }

    public function archiveExercise()
    {
        $this->resourceNode = null;
    }

    /**
     * Set published
     *
     * @param boolean $published
     */
    public function setPublished($published)
    {
        $this->published = $published;
    }

    /**
     * Get published
     */
    public function getpublished()
    {
        return $this->published;
    }
}
