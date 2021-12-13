<?php

namespace UJM\ExoBundle\Entity;

use Claroline\AppBundle\Entity\Identifier\Uuid;
use Claroline\CoreBundle\Entity\Resource\AbstractResource;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use UJM\ExoBundle\Entity\Item\Item;
use UJM\ExoBundle\Library\Model\AttemptParametersTrait;
use UJM\ExoBundle\Library\Options\ExerciseNumbering;
use UJM\ExoBundle\Library\Options\ExerciseType;
use UJM\ExoBundle\Library\Options\ShowCorrectionAt;
use UJM\ExoBundle\Library\Options\ShowScoreAt;

/**
 * @ORM\Entity(repositoryClass="UJM\ExoBundle\Repository\ExerciseRepository")
 * @ORM\Table(name="ujm_exercise")
 */
class Exercise extends AbstractResource
{
    use AttemptParametersTrait;

    /**
     * Type of the Exercise.
     *
     * @ORM\Column(type="string")
     *
     * @var string
     */
    private $type = ExerciseType::CUSTOM;

    /**
     * @ORM\Column(name="description", type="text", nullable=true)
     *
     * @var string
     */
    private $description = '';

    /**
     * When corrections are available to the Users ?
     *
     * @ORM\Column(name="correction_mode", type="string")
     *
     * @var string
     */
    private $correctionMode = ShowCorrectionAt::AFTER_END;

    /**
     * Date of availability of the corrections.
     *
     * @ORM\Column(name="date_correction", type="datetime", nullable=true)
     *
     * @var string
     */
    private $dateCorrection;

    /**
     * When marks are available to the Users ?
     *
     * @ORM\Column(name="mark_mode", type="string")
     *
     * @var string
     */
    private $markMode = ShowScoreAt::WITH_CORRECTION;

    /**
     * Add a button to stop the Exercise before the end.
     *
     * @ORM\Column(type="boolean")
     *
     * @var bool
     */
    private $interruptible = true;

    /**
     * Show overview to users or directly start the quiz.
     *
     * @ORM\Column(name="show_overview", type="boolean")
     *
     * @var bool
     */
    private $showOverview = true;

    /**
     * Show back button in player.
     *
     * @ORM\Column(name="show_back", type="boolean")
     *
     * @var bool
     */
    private $showBack = true;

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
     * Show intermediates scores by steps, by tags or not at all on the end page.
     *
     * @ORM\Column(name="intermediate_scores", type="text", nullable=true)
     *
     * @var string
     */
    private $intermediateScores = 'none';

    /**
     * A message to display when a user has done all its attempts.
     *
     * @ORM\Column(name="attempts_reached_message", type="text", nullable=true)
     *
     * @var string
     */
    private $attemptsReachedMessage = '';

    /**
     * @ORM\Column(name="success_message", type="text", nullable=true)
     *
     * @var string
     */
    private $successMessage = '';

    /**
     * @ORM\Column(name="failure_message", type="text", nullable=true)
     *
     * @var string
     */
    private $failureMessage = '';

    /**
     * Show navigation buttons on the end page.
     *
     * @ORM\Column(name="end_navigation", type="boolean")
     *
     * @var bool
     */
    private $endNavigation = true;

    /**
     * Show attempts stats on the end page.
     *  - none : no stats displayed.
     *  - user : only current user stats displayed.
     *  - all : all participants stats displayed.
     *
     * @ORM\Column(type="string")
     *
     * @var string
     */
    private $endStats = 'none';

    /**
     * Show attempts stats on the overview page.
     *  - none : no stats displayed.
     *  - user : only current user stats displayed.
     *  - all : all participants stats displayed.
     *
     * @ORM\Column(type="string")
     *
     * @var string
     */
    private $overviewStats = 'none';

    /**
     * Show the Exercise meta in the overview of the Exercise.
     *
     * @ORM\Column(name="metadata_visible", type="boolean")
     *
     * @var bool
     */
    private $metadataVisible = true;

    /**
     * Show stats about User responses in the Correction.
     *
     * @ORM\Column(type="boolean")
     *
     * @var bool
     */
    private $statistics = false;

    /**
     * Flag indicating that we do not show the entire correction for the exercise
     * (equals hide Awaited answer filed) when displaying instant feedback and exercise correction page.
     *
     * @ORM\Column(name="minimal_correction", type="boolean")
     *
     * @var bool
     */
    private $minimalCorrection = false;

    /**
     * If true, the users who pass the exercise are anonymized in papers.
     *
     * @ORM\Column(name="anonymous", type="boolean", nullable=true)
     *
     * @var bool
     */
    private $anonymizeAttempts = false;

    /**
     * Show feedback flag.
     *
     * @ORM\Column(name="show_feedback", type="boolean")
     *
     * @var bool
     */
    private $showFeedback = false;

    /**
     * @ORM\Column(type="text", nullable=true)
     *
     * @var string
     */
    private $scoreRule;

    /**
     * Score to obtain to pass the exercise.
     *
     * @ORM\Column(name="success_score", type="float", nullable=true)
     *
     * @var float
     */
    private $successScore = 50;

    /**
     * Displays step numbering.
     *
     * @ORM\Column(type="string")
     *
     * @var string
     */
    private $numbering = ExerciseNumbering::NONE;

    /**
     * Displays question numbering.
     *
     * @ORM\Column(type="string")
     *
     * @var string
     */
    private $questionNumbering = ExerciseNumbering::NONE;

    /**
     * Displays step titles.
     *
     * @ORM\Column(type="boolean")
     *
     * @var bool
     */
    private $showTitles = true;

    /**
     * Displays question titles.
     *
     * @ORM\Column(type="boolean")
     *
     * @var bool
     */
    private $showQuestionTitles = true;

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
     * @ORM\Column(name="all_papers_stats", type="boolean", options={"default" = 1})
     *
     * @var bool
     */
    private $allPapersStatistics = true;

    /**
     * Sets the mandatory question flag.
     *
     * @ORM\Column(name="mandatory_questions", type="boolean")
     *
     * @var bool
     */
    private $mandatoryQuestions = false;

    /**
     * If true, the time to answer the exercise will be limited by the defined duration.
     *
     * @ORM\Column(name="time_limited", type="boolean", options={"default" = 0})
     *
     * @var bool
     */
    private $timeLimited = false;

    /**
     * @ORM\Column(name="progression_displayed", type="boolean", options={"default" = 1})
     *
     * @var bool
     */
    private $progressionDisplayed = true;

    /**
     * @ORM\Column(name="answers_editable", type="boolean", options={"default" = 1})
     *
     * @var bool
     */
    private $answersEditable = true;

    /**
     * @ORM\Column(name="expected_answers", type="boolean")
     *
     * @var bool
     */
    private $expectedAnswers = true;

    /**
     * @ORM\OneToMany(targetEntity="Step", mappedBy="exercise", cascade={"all"}, orphanRemoval=true)
     * @ORM\OrderBy({"order" = "ASC"})
     *
     * @var ArrayCollection|Step[]
     */
    private $steps;

    /**
     * Exercise constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->dateCorrection = new \DateTime();
        $this->steps = new ArrayCollection();
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

    public function getEndMessage()
    {
        return $this->endMessage;
    }

    public function setEndMessage($endMessage)
    {
        $this->endMessage = $endMessage;
    }

    public function getOverviewStats(): string
    {
        return $this->overviewStats;
    }

    public function setOverviewStats(string $overviewStats)
    {
        $this->overviewStats = $overviewStats;
    }

    public function getEndStats(): string
    {
        return $this->endStats;
    }

    public function setEndStats(string $endStats)
    {
        $this->endStats = $endStats;
    }

    public function getIntermediateScores()
    {
        return $this->intermediateScores;
    }

    public function setIntermediateScores($intermediateScores)
    {
        $this->intermediateScores = $intermediateScores;
    }

    public function setAttemptsReachedMessage($attemptsReachedMessage)
    {
        $this->attemptsReachedMessage = $attemptsReachedMessage;
    }

    public function getAttemptsReachedMessage()
    {
        return $this->attemptsReachedMessage;
    }

    public function setSuccessMessage($successMessage)
    {
        $this->successMessage = $successMessage;
    }

    public function getSuccessMessage()
    {
        return $this->successMessage;
    }

    public function setFailureMessage($failureMessage)
    {
        $this->failureMessage = $failureMessage;
    }

    public function getFailureMessage()
    {
        return $this->failureMessage;
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
     * Set show back.
     *
     * @param bool $showBack
     */
    public function setShowBack($showBack)
    {
        $this->showBack = $showBack;
    }

    /**
     * Is back shown ?
     *
     * @return bool
     */
    public function getShowBack()
    {
        return $this->showBack;
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
     * @return ArrayCollection|Step[]
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
     * @return string
     */
    public function getScoreRule()
    {
        return $this->scoreRule;
    }

    /**
     * @param string $scoreRule
     *
     * @return string
     */
    public function setScoreRule($scoreRule)
    {
        $this->scoreRule = $scoreRule;

        return $this->scoreRule;
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

    public function setQuestionNumbering($numbering)
    {
        $this->questionNumbering = $numbering;
    }

    public function getQuestionNumbering()
    {
        return $this->questionNumbering;
    }

    public function setShowTitles($showTitles)
    {
        $this->showTitles = $showTitles;
    }

    public function getShowTitles()
    {
        return $this->showTitles;
    }

    public function setShowQuestionTitles($showTitles)
    {
        $this->showQuestionTitles = $showTitles;
    }

    public function getShowQuestionTitles()
    {
        return $this->showQuestionTitles;
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

    /**
     * @return bool
     */
    public function isTimeLimited()
    {
        return $this->timeLimited;
    }

    /**
     * @param bool $timeLimited
     */
    public function setTimeLimited($timeLimited)
    {
        $this->timeLimited = $timeLimited;
    }

    /**
     * @return bool
     */
    public function isProgressionDisplayed()
    {
        return $this->progressionDisplayed;
    }

    /**
     * @param bool $progressionDisplayed
     */
    public function setProgressionDisplayed($progressionDisplayed)
    {
        $this->progressionDisplayed = $progressionDisplayed;
    }

    /**
     * @return bool
     */
    public function isAnswersEditable()
    {
        return $this->answersEditable;
    }

    /**
     * @param bool $answersEditable
     */
    public function setAnswersEditable($answersEditable)
    {
        $this->answersEditable = $answersEditable;
    }

    /**
     * @return bool
     */
    public function hasExpectedAnswers()
    {
        return $this->expectedAnswers;
    }

    /**
     * @param bool $expectedAnswers
     */
    public function setExpectedAnswers($expectedAnswers)
    {
        $this->expectedAnswers = $expectedAnswers;
    }
}
