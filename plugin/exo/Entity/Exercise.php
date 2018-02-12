<?php

namespace UJM\ExoBundle\Entity;

use Claroline\CoreBundle\Entity\Model\UuidTrait;
use Claroline\CoreBundle\Entity\Resource\AbstractResource;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use UJM\ExoBundle\Entity\Item\Item;
use UJM\ExoBundle\Library\Mode\CorrectionMode;
use UJM\ExoBundle\Library\Mode\MarkMode;
use UJM\ExoBundle\Library\Model\AttemptParametersTrait;
use UJM\ExoBundle\Library\Options\ExerciseNumbering;
use UJM\ExoBundle\Library\Options\ExerciseType;

/**
 * @ORM\Entity(repositoryClass="UJM\ExoBundle\Repository\ExerciseRepository")
 * @ORM\Table(name="ujm_exercise")
 */
class Exercise extends AbstractResource
{
    use UuidTrait;

    use AttemptParametersTrait;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text", nullable=true)
     */
    private $description = '';

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
     * @ORM\Column(type="boolean")
     */
    private $interruptible = false;

    /**
     * Show overview to users or directly start the quiz.
     *
     * @ORM\Column(name="show_overview", type="boolean")
     *
     * @var bool
     */
    private $showOverview = true;

    /**
     * Show an end page when the user has finished the quiz.
     *
     * @ORM\Column(name="show_end_page", type="boolean")
     *
     * @var bool
     */
    private $showEndPage = false;

    /**
     * Show an end page when the user has finished the quiz.
     *
     * @ORM\Column(name="show_end_confirm", type="boolean")
     *
     * @var bool
     */
    private $showEndConfirm = true;

    /**
     * A message to display at the end of the quiz.
     *
     * @ORM\Column(name="end_message", type="text", nullable=true)
     *
     * @var string
     */
    private $endMessage = '';

    /**
     * Show navigation buttons on the end page.
     *
     * @ORM\Column(name="end_navigation", type="boolean")
     *
     * @var bool
     */
    private $endNavigation = true;

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
     * If true, the users who pass the exercise are anonymized in papers.
     *
     * @var bool
     *
     * @ORM\Column(name="anonymous", type="boolean", nullable=true)
     */
    private $anonymizeAttempts = false;

    /**
     * Type of the Exercise.
     *
     * @var string
     *
     * @ORM\Column(type="string")
     */
    private $type = ExerciseType::SUMMATIVE;

    /**
     * @ORM\OneToMany(targetEntity="Step", mappedBy="exercise", cascade={"all"}, orphanRemoval=true)
     * @ORM\OrderBy({"order" = "ASC"})
     *
     * @var ArrayCollection|Step[]
     */
    private $steps;

    /**
     * Show feedback flag.
     *
     * @var string
     *
     * @ORM\Column(name="show_feedback", type="boolean")
     */
    private $showFeedback = false;

    /**
     * Score on which we wish to render a paper.
     * If 0, the score will be computed based on question maxs score.
     * Else score will be computed based on this value.
     *
     * @ORM\Column(type="float")
     *
     * @var float
     */
    private $totalScoreOn = 0;

    /**
     * Score to obtain to pass the exercise.
     *
     * @ORM\Column(name="success_score", type="float", nullable=true)
     *
     * @var float
     */
    private $successScore;

    /**
     * @var string
     *
     * @ORM\Column(type="string")
     */
    private $numbering = ExerciseNumbering::NONE;

    /**
     * Number of papers allowed.
     * If 0, infinite amount of papers.
     *
     * @ORM\Column(name="max_papers", type="integer")
     *
     * @var int
     */
    private $maxPapers = 0;

    /**
     * Use all papers to compute stats.
     *
     * @var bool
     *
     * @ORM\Column(name="all_papers_stats", type="boolean", options={"default" = 1})
     */
    private $allPapersStatistics = true;

    /**
     * Sets the mandatory question flag.
     *
     * @var string
     *
     * @ORM\Column(name="mandatory_questions", type="boolean")
     */
    private $mandatoryQuestions = false;

    /**
     * Exercise constructor.
     */
    public function __construct()
    {
        $this->refreshUuid();
        $this->dateCorrection = new \DateTime();
        $this->steps = new ArrayCollection();
    }

    public function getTitle()
    {
        return !empty($this->resourceNode) ? $this->resourceNode->getName() : null;
    }

    public function setTitle($title)
    {
        if (!empty($this->resourceNode)) {
            $this->resourceNode->setName($title);
        }
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
     * Set interruptible.
     *
     * @param bool $interruptible
     */
    public function setInterruptible($interruptible)
    {
        $this->interruptible = $interruptible;
    }

    /**
     * Is interruptible?
     *
     * @return bool
     */
    public function isInterruptible()
    {
        return $this->interruptible;
    }

    /**
     * Set show end confirm dialog.
     *
     * @param bool $showEndConfirm
     */
    public function setShowEndConfirm($showEndConfirm)
    {
        $this->showEndConfirm = $showEndConfirm;
    }

    /**
     * Is end confirm dialog shown ?
     *
     * @return bool
     */
    public function getShowEndConfirm()
    {
        return $this->showEndConfirm;
    }

    /**
     * Set show end page.
     *
     * @param bool $showEndPage
     */
    public function setShowEndPage($showEndPage)
    {
        $this->showEndPage = $showEndPage;
    }

    /**
     * Is end page shown ?
     *
     * @return bool
     */
    public function getShowEndPage()
    {
        return $this->showEndPage;
    }

    public function setEndMessage($endMessage)
    {
        $this->endMessage = $endMessage;
    }

    public function getEndMessage()
    {
        return $this->endMessage;
    }

    /**
     * Set show overview.
     *
     * @param bool $showOverview
     */
    public function setShowOverview($showOverview)
    {
        $this->showOverview = $showOverview;
    }

    /**
     * Is overview shown ?
     *
     * @return bool
     */
    public function getShowOverview()
    {
        return $this->showOverview;
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
     * Set minimal correction.
     *
     * @param bool $minimalCorrection
     */
    public function setMinimalCorrection($minimalCorrection)
    {
        $this->minimalCorrection = $minimalCorrection;
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
     * Set anonymize attempts.
     *
     * @param bool $anonymizeAttempts
     */
    public function setAnonymizeAttempts($anonymizeAttempts)
    {
        $this->anonymizeAttempts = $anonymizeAttempts;
    }

    /**
     * Get anonymize attempts.
     *
     * @return bool
     */
    public function getAnonymizeAttempts()
    {
        return $this->anonymizeAttempts;
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
     * Gets a step by its UUID.
     *
     * @param string $uuid
     *
     * @return Step|null
     */
    public function getStep($uuid)
    {
        foreach ($this->steps as $step) {
            if ($step->getUuid() === $uuid) {
                return $step;
            }
        }

        return null;
    }

    /**
     * Gets a question by its UUID.
     *
     * @param string $uuid
     *
     * @return Item|null
     */
    public function getQuestion($uuid)
    {
        foreach ($this->steps as $step) {
            $questions = $step->getQuestions();
            foreach ($questions as $question) {
                if ($question->getUuid() === $uuid) {
                    return $question;
                }
            }
        }

        return null;
    }

    /**
     * Adds a step to the Exercise.
     *
     * @param Step $step
     *
     * @return $this
     */
    public function addStep(Step $step)
    {
        if (!$this->steps->contains($step)) {
            $step->setOrder($this->steps->count());
            $this->steps->add($step);
            $step->setExercise($this);
        }

        return $this;
    }

    /**
     * Removes a Step from the Exercise.
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

    /**
     * Sets show feedback.
     *
     * @param bool $showFeedback
     */
    public function setShowFeedback($showFeedback)
    {
        $this->showFeedback = $showFeedback;
    }

    /**
     * Gets show feedback.
     *
     * @return bool
     */
    public function getShowFeedback()
    {
        return $this->showFeedback;
    }

    /**
     * Sets totalScoreOn.
     *
     * @param float $totalScoreOn
     */
    public function setTotalScoreOn($totalScoreOn)
    {
        $this->totalScoreOn = $totalScoreOn;
    }

    /**
     * Gets totalScoreOn.
     *
     * @return float
     */
    public function getTotalScoreOn()
    {
        return $this->totalScoreOn;
    }

    /**
     * Sets successScore.
     *
     * @param float $successScore
     */
    public function setSuccessScore($successScore)
    {
        $this->successScore = $successScore;
    }

    /**
     * Gets successScore.
     *
     * @return float
     */
    public function getSuccessScore()
    {
        return $this->successScore;
    }

    public function setNumbering($numbering)
    {
        $this->numbering = $numbering;
    }

    public function getNumbering()
    {
        return $this->numbering;
    }

    public function setPicking($picking)
    {
        $this->picking = $picking;
    }

    public function getPicking()
    {
        return $this->picking;
    }

    public function setMaxPapers($maxPapers)
    {
        $this->maxPapers = $maxPapers;
    }

    public function getMaxPapers()
    {
        return $this->maxPapers;
    }

    /**
     * Gets allPapersStatistics.
     *
     * @return bool
     */
    public function isAllPapersStatistics()
    {
        return $this->allPapersStatistics;
    }

    /**
     * Sets allPapersStatistics.
     *
     * @param bool $allPapersStatistics
     */
    public function setAllPapersStatistics($allPapersStatistics)
    {
        $this->allPapersStatistics = $allPapersStatistics;
    }

    public function setMandatoryQuestions($mandatoryQuestions)
    {
        $this->mandatoryQuestions = $mandatoryQuestions;
    }

    public function getMandatoryQuestions()
    {
        return $this->mandatoryQuestions;
    }

    public function hasEndNavigation()
    {
        return $this->endNavigation;
    }

    public function setEndNavigation($endNavigation)
    {
        $this->endNavigation = $endNavigation;
    }
}
