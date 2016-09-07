<?php

namespace UJM\ExoBundle\Entity;

use Claroline\CoreBundle\Entity\Resource\AbstractResource;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use UJM\ExoBundle\Library\Mode\CorrectionMode;
use UJM\ExoBundle\Library\Mode\MarkMode;

/**
 * @ORM\Entity(repositoryClass="UJM\ExoBundle\Repository\ExerciseRepository")
 * @ORM\Table(name="ujm_exercise")
 */
class Exercise extends AbstractResource
{
    const TYPE_SUMMATIVE = '1';
    const TYPE_EVALUATIVE = '2';
    const TYPE_FORMATIVE = '3';

    /**
     * @ORM\Column(name="description", type="text", nullable=true)
     */
    private $description = '';

    /**
     * Are the Steps shuffled ?
     *
     * @ORM\Column(name="shuffle", type="boolean", nullable=true)
     */
    private $shuffle = false;

    /**
     * Number of Steps to use when we play the Exercise
     * If 0, all the steps are used in the Player.
     *
     * @var int
     *
     * @ORM\Column(name="nb_question", type="integer")
     */
    private $pickSteps = 0;

    /**
     * Do we need to always give a same User the same Steps in the same order ?
     * Works with `shuffle` and `pickSteps`.
     *
     * @var bool
     *
     * @ORM\Column(name="keepSameQuestion", type="boolean", nullable=true)
     */
    private $keepSteps = false;

    /**
     * Maximum time allowed to do the Exercise.
     *
     * @var int
     *
     * @ORM\Column(name="duration", type="integer")
     */
    private $duration = 0;

    /**
     * @ORM\Column(name="doprint", type="boolean", nullable=true)
     */
    private $doprint = false;

    /**
     * Number of attempts allowed for the Exercise.
     *
     * @var int
     *
     * @ORM\Column(name="max_attempts", type="integer")
     */
    private $maxAttempts = 0;

    /**
     * When corrections are available to the Users ?
     *
     * @var string
     *
     * @ORM\Column(name="correction_mode", type="string", length=255)
     */
    private $correctionMode = CorrectionMode::AFTER_END;

    /**
     * Date of availability of the corrections.
     *
     * @var string
     *
     * @ORM\Column(name="date_correction", type="datetime", nullable=true)
     */
    private $dateCorrection;

    /**
     * When marks are available to the Users ?
     *
     * @var string
     *
     * @ORM\Column(name="mark_mode", type="string", length=255)
     */
    private $markMode = MarkMode::WITH_CORRECTION;

    /**
     * Add a button to stop the Exercise before the end.
     *
     * @var bool
     *
     * @ORM\Column(name="disp_button_interrupt", type="boolean", nullable=true)
     */
    private $dispButtonInterrupt = false;

    /**
     * Show the Exercise meta in the overview of the Exercise.
     *
     * @var bool
     *
     * @ORM\Column(name="metadata_visible", type="boolean")
     */
    private $metadataVisible = true;

    /**
     * Show stats about User responses in the Correction.
     *
     * @var bool
     *
     * @ORM\Column(type="boolean")
     */
    private $statistics = false;

    /**
     * @ORM\Column(name="lock_attempt", type="boolean", nullable=true)
     */
    private $lockAttempt = false;

    /**
     * Flag indicating that we do not show the entire correction for the exercise
     * (equals hide Awaited answer filed) when displaying instant feedback and exercise correction page.
     *
     * @ORM\Column(name="minimal_correction", type="boolean")
     */
    private $minimalCorrection = false;

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

    /**
     * Are anonymous allowed to play the Exercise ?
     *
     * @var bool
     *
     * @ORM\Column(name="anonymous", type="boolean", nullable=true)
     */
    private $anonymous = false;

    /**
     * Type of the Exercise.
     *
     * @var string
     *
     * @ORM\Column(name="type", type="string", length=255)
     * sommatif, formatif, certificatif
     */
    private $type = self::TYPE_SUMMATIVE;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Step", mappedBy="exercise", cascade={"all"})
     * @ORM\OrderBy({"order" = "ASC"})
     */
    private $steps;

    public function __construct()
    {
        $this->dateCorrection = new \DateTime();
        $this->steps = new ArrayCollection();
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
     * Set pickSteps.
     *
     * @param int $pickSteps
     */
    public function setPickSteps($pickSteps)
    {
        $this->pickSteps = $pickSteps;
    }

    /**
     * Get pickSteps.
     *
     * @return int
     */
    public function getPickSteps()
    {
        return $this->pickSteps;
    }

    /**
     * Set keepSteps.
     *
     * @param bool $keepSteps
     */
    public function setKeepSteps($keepSteps)
    {
        $this->keepSteps = $keepSteps;
    }

    /**
     * Get keepSteps.
     *
     * @return bool
     */
    public function getKeepSteps()
    {
        return $this->keepSteps;
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
     * Set doprint.
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
     * Set visibility of metadata.
     *
     * @param bool $visible
     */
    public function setMetadataVisible($visible)
    {
        $this->metadataVisible = $visible;
    }

    /**
     * Are metadata visible ?
     *
     * @return bool
     */
    public function isMetadataVisible()
    {
        return $this->metadataVisible;
    }

    /**
     * Do the current exercise include statistics ?
     *
     * @return bool
     */
    public function hasStatistics()
    {
        return $this->statistics;
    }

    /**
     * Set statistics.
     *
     * @param bool $statistics
     */
    public function setStatistics($statistics)
    {
        $this->statistics = $statistics;
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

    /**
     * Do we have to show the minimal correction view ?
     */
    public function setMinimalCorrection($minimalCorrection)
    {
        $this->minimalCorrection = $minimalCorrection;

        return $this;
    }

    /**
     * Do we have to show the minimal correction view ?
     *
     * @return bool
     */
    public function isMinimalCorrection()
    {
        return $this->minimalCorrection;
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

    /**
     * Set anonymous.
     *
     * @param bool $anonymous
     */
    public function setAnonymous($anonymous)
    {
        $this->anonymous = $anonymous;
    }

    /**
     * Get anonymous.
     */
    public function getAnonymous()
    {
        return $this->anonymous;
    }

    /**
     * @param string $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return ArrayCollection
     */
    public function getSteps()
    {
        return $this->steps;
    }

    /**
     * Add a step to the Exercise.
     *
     * @param Step $step
     *
     * @return $this
     */
    public function addStep(Step $step)
    {
        if (!$this->steps->contains($step)) {
            $this->steps->add($step);

            $step->setExercise($this);
        }

        return $this;
    }

    /**
     * Remove a Step from the Exercise.
     *
     * @param Step $step
     *
     * @return $this
     */
    public function removeStep(Step $step)
    {
        if ($this->steps->contains($step)) {
            $this->steps->removeElement($step);
        }

        return $this;
    }
}
