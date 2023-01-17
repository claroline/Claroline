<?php

namespace UJM\ExoBundle\Entity;

use Claroline\CoreBundle\Entity\Resource\AbstractResource;
use Claroline\CoreBundle\Entity\Resource\HasEndPage;
use Claroline\CoreBundle\Entity\Resource\HasHomePage;
use Claroline\EvaluationBundle\Entity\EvaluationFeedbacks;
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
    use HasHomePage;
    use HasEndPage;
    use EvaluationFeedbacks;
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
     * @var \DateTime
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
     * @ORM\Column(name="show_end_confirm", type="boolean")
     *
     * @var bool
     */
    private $showEndConfirm = true;

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

    public function __construct()
    {
        parent::__construct();

        $this->dateCorrection = new \DateTime();
        $this->steps = new ArrayCollection();
    }

    public function setCorrectionMode(string $correctionMode): void
    {
        $this->correctionMode = $correctionMode;
    }

    public function getCorrectionMode(): string
    {
        return $this->correctionMode;
    }

    public function setDateCorrection(?\DateTimeInterface $dateCorrection = null): void
    {
        $this->dateCorrection = $dateCorrection;
    }

    public function getDateCorrection(): ?\DateTimeInterface
    {
        return $this->dateCorrection;
    }

    public function setMarkMode(string $markMode): void
    {
        $this->markMode = $markMode;
    }

    public function getMarkMode(): string
    {
        return $this->markMode;
    }

    public function setInterruptible(bool $interruptible): void
    {
        $this->interruptible = $interruptible;
    }

    public function isInterruptible(): bool
    {
        return $this->interruptible;
    }

    public function setShowEndConfirm(bool $showEndConfirm): void
    {
        $this->showEndConfirm = $showEndConfirm;
    }

    public function getShowEndConfirm(): bool
    {
        return $this->showEndConfirm;
    }

    public function getOverviewStats(): string
    {
        return $this->overviewStats;
    }

    public function setOverviewStats(string $overviewStats): void
    {
        $this->overviewStats = $overviewStats;
    }

    public function getEndStats(): string
    {
        return $this->endStats;
    }

    public function setEndStats(string $endStats): void
    {
        $this->endStats = $endStats;
    }

    public function getIntermediateScores(): ?string
    {
        return $this->intermediateScores;
    }

    public function setIntermediateScores(?string $intermediateScores = null): void
    {
        $this->intermediateScores = $intermediateScores;
    }

    public function setAttemptsReachedMessage(?string $attemptsReachedMessage = null): void
    {
        $this->attemptsReachedMessage = $attemptsReachedMessage;
    }

    public function getAttemptsReachedMessage(): ?string
    {
        return $this->attemptsReachedMessage;
    }

    public function setShowBack(bool $showBack): void
    {
        $this->showBack = $showBack;
    }

    public function getShowBack(): bool
    {
        return $this->showBack;
    }

    public function setMetadataVisible(bool $visible): void
    {
        $this->metadataVisible = $visible;
    }

    public function isMetadataVisible(): bool
    {
        return $this->metadataVisible;
    }

    /**
     * Do the current exercise include statistics ?
     */
    public function hasStatistics(): bool
    {
        return $this->statistics;
    }

    public function setStatistics(bool $statistics): void
    {
        $this->statistics = $statistics;
    }

    /**
     * Set minimal correction.
     */
    public function setMinimalCorrection(bool $minimalCorrection): void
    {
        $this->minimalCorrection = $minimalCorrection;
    }

    /**
     * Do we have to show the minimal correction view ?
     */
    public function isMinimalCorrection(): bool
    {
        return $this->minimalCorrection;
    }

    public function setAnonymizeAttempts(bool $anonymizeAttempts): void
    {
        $this->anonymizeAttempts = $anonymizeAttempts;
    }

    public function getAnonymizeAttempts(): bool
    {
        return $this->anonymizeAttempts;
    }

    public function setType(string $type): void
    {
        $this->type = $type;
    }

    public function getType(): string
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
     */
    public function getStep(string $uuid): ?Step
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
     */
    public function getQuestion(string $uuid): ?Item
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
    public function addStep(Step $step): void
    {
        if (!$this->steps->contains($step)) {
            $step->setOrder($this->steps->count());
            $this->steps->add($step);
            $step->setExercise($this);
        }
    }

    /**
     * Removes a Step from the Exercise.
     */
    public function removeStep(Step $step): void
    {
        if ($this->steps->contains($step)) {
            $this->steps->removeElement($step);
        }
    }

    public function setShowFeedback(bool $showFeedback): void
    {
        $this->showFeedback = $showFeedback;
    }

    public function getShowFeedback(): bool
    {
        return $this->showFeedback;
    }

    public function getScoreRule(): ?string
    {
        return $this->scoreRule;
    }

    public function setScoreRule(?string $scoreRule): void
    {
        $this->scoreRule = $scoreRule;
    }

    public function setSuccessScore(?float $successScore = null): void
    {
        $this->successScore = $successScore;
    }

    public function getSuccessScore(): ?float
    {
        return $this->successScore;
    }

    public function setNumbering(string $numbering): void
    {
        $this->numbering = $numbering;
    }

    public function getNumbering(): string
    {
        return $this->numbering;
    }

    public function setQuestionNumbering(string $numbering): void
    {
        $this->questionNumbering = $numbering;
    }

    public function getQuestionNumbering(): string
    {
        return $this->questionNumbering;
    }

    public function setShowTitles(bool $showTitles): void
    {
        $this->showTitles = $showTitles;
    }

    public function getShowTitles(): bool
    {
        return $this->showTitles;
    }

    public function setShowQuestionTitles(bool $showTitles): void
    {
        $this->showQuestionTitles = $showTitles;
    }

    public function getShowQuestionTitles(): bool
    {
        return $this->showQuestionTitles;
    }

    public function setPicking(string $picking): void
    {
        $this->picking = $picking;
    }

    public function getPicking(): string
    {
        return $this->picking;
    }

    public function setMaxPapers(?int $maxPapers = null): void
    {
        $this->maxPapers = $maxPapers;
    }

    public function getMaxPapers(): ?int
    {
        return $this->maxPapers;
    }

    public function isAllPapersStatistics(): bool
    {
        return $this->allPapersStatistics;
    }

    public function setAllPapersStatistics(bool $allPapersStatistics): void
    {
        $this->allPapersStatistics = $allPapersStatistics;
    }

    public function setMandatoryQuestions(bool $mandatoryQuestions): void
    {
        $this->mandatoryQuestions = $mandatoryQuestions;
    }

    public function getMandatoryQuestions(): bool
    {
        return $this->mandatoryQuestions;
    }

    public function isTimeLimited(): bool
    {
        return $this->timeLimited;
    }

    public function setTimeLimited(bool $timeLimited): void
    {
        $this->timeLimited = $timeLimited;
    }

    public function isProgressionDisplayed(): bool
    {
        return $this->progressionDisplayed;
    }

    public function setProgressionDisplayed(bool $progressionDisplayed): void
    {
        $this->progressionDisplayed = $progressionDisplayed;
    }

    public function isAnswersEditable(): bool
    {
        return $this->answersEditable;
    }

    public function setAnswersEditable(bool $answersEditable): void
    {
        $this->answersEditable = $answersEditable;
    }

    public function hasExpectedAnswers(): bool
    {
        return $this->expectedAnswers;
    }

    public function setExpectedAnswers(bool $expectedAnswers): void
    {
        $this->expectedAnswers = $expectedAnswers;
    }
}
