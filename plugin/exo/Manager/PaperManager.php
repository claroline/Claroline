<?php

namespace UJM\ExoBundle\Manager;

use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Persistence\ObjectManager;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Translation\TranslatorInterface;
use UJM\ExoBundle\Entity\Exercise;
use UJM\ExoBundle\Entity\Hint;
use UJM\ExoBundle\Entity\Paper;
use UJM\ExoBundle\Entity\Question;
use UJM\ExoBundle\Entity\Response;
use UJM\ExoBundle\Entity\StepQuestion;
use UJM\ExoBundle\Event\Log\LogExerciseEvaluatedEvent;
use UJM\ExoBundle\Library\Mode\CorrectionMode;
use UJM\ExoBundle\Library\Mode\MarkMode;
use UJM\ExoBundle\Services\classes\PaperService;
use UJM\ExoBundle\Transfer\Json\QuestionHandlerCollector;

/**
 * @DI\Service("ujm.exo.paper_manager")
 */
class PaperManager
{
    /**
     * @var ObjectManager
     */
    private $om;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var QuestionHandlerCollector
     */
    private $handlerCollector;

    /**
     * @var QuestionManager
     */
    private $questionManager;

    /**
     * @var HintManager
     */
    private $hintManager;

    /**
     * @var PaperService
     */
    private $paperService;

    /**
     * @DI\InjectParams({
     *     "om"              = @DI\Inject("claroline.persistence.object_manager"),
     *     "eventDispatcher" = @DI\Inject("event_dispatcher"),
     *     "translator"      = @DI\Inject("translator"),
     *     "collector"       = @DI\Inject("ujm.exo.question_handler_collector"),
     *     "questionManager" = @DI\Inject("ujm.exo.question_manager"),
     *     "hintManager"     = @DI\Inject("ujm.exo.hint_manager"),
     *     "paperService"    = @DI\Inject("ujm.exo_paper")
     * })
     *
     * @param ObjectManager            $om
     * @param EventDispatcherInterface $eventDispatcher
     * @param TranslatorInterface      $translator
     * @param QuestionHandlerCollector $collector
     * @param QuestionManager          $questionManager
     * @param HintManager              $hintManager
     * @param PaperService             $paperService
     */
    public function __construct(
        ObjectManager $om,
        EventDispatcherInterface $eventDispatcher,
        TranslatorInterface $translator,
        QuestionHandlerCollector $collector,
        QuestionManager $questionManager,
        HintManager $hintManager,
        PaperService $paperService
    ) {
        $this->om = $om;
        $this->eventDispatcher = $eventDispatcher;
        $this->translator = $translator;
        $this->handlerCollector = $collector;
        $this->questionManager = $questionManager;
        $this->hintManager = $hintManager;
        $this->paperService = $paperService;
    }

    /**
     * Returns the JSON representation of an exercise with its last associated paper
     * for a given user. If no paper exists, a new one is created.
     *
     * @param Exercise $exercise
     * @param User     $user
     *
     * @return Paper
     */
    public function openPaper(Exercise $exercise, User $user = null)
    {
        $papers = [];
        if ($user) {
            // If it's not an anonymous, load the previous unfinished papers
            $papers = $this->om->getRepository('UJMExoBundle:Paper')->findUnfinishedPapers($user, $exercise);
        }

        if (count($papers) === 0) {
            // Create a new paper for anonymous or if no unfinished
            $paper = $this->createPaper($exercise, $user);
        } else {
            if (!$exercise->getDispButtonInterrupt()) {
                // User is not allowed to continue is previous paper => open a new one and close the previous
                $this->closePaper($papers[0]);

                $paper = $this->createPaper($exercise, $user);
            } else {
                $paper = $papers[0];
            }
        }

        return $paper;
    }

    /**
     * Creates a new exercise paper for a given user.
     *
     * @param Exercise $exercise
     * @param User     $user
     *
     * @return Paper
     */
    public function createPaper(Exercise $exercise, User $user = null)
    {
        // Get the number of the new Paper
        $paperNum = 1;
        if ($user) {
            $lastPaper = $this->om->getRepository('UJMExoBundle:Paper')->findOneBy(
                ['user' => $user, 'exercise' => $exercise],
                ['start' => 'DESC']
            );

            if ($lastPaper) {
                $paperNum = $lastPaper->getNumPaper() + 1;
            }
        }

        // Generate the list of Steps and Questions for the Paper
        $order = '';
        if (!empty($lastPaper) && $exercise->getKeepSteps()) {
            // Get steps order from the last user Paper
            $order = $lastPaper->getOrdreQuestion();
        } else {
            // Generate paper step order
            $questions = $this->pickQuestions($exercise);
            foreach ($questions as $question) {
                $order .= $question->getId().';';
            }
        }

        // Create the new Paper entity
        $paper = new Paper();
        $paper->setExercise($exercise);
        $paper->setUser($user);
        $paper->setNumPaper($paperNum);
        $paper->setOrdreQuestion($order);
        $paper->setAnonymous($exercise->getAnonymous());

        $this->om->persist($paper);
        $this->om->flush();

        return $paper;
    }

    /**
     * Records or updates an answer for a given question and paper.
     *
     * @param Paper    $paper
     * @param Question $question
     * @param mixed    $data
     * @param string   $ip
     */
    public function recordAnswer(Paper $paper, Question $question, $data, $ip)
    {
        $handler = $this->handlerCollector->getHandlerForInteractionType($question->getType());

        $response = $this->om->getRepository('UJMExoBundle:Response')
            ->findOneBy(['paper' => $paper, 'question' => $question]);

        if (!$response) {
            $response = new Response();
            $response->setPaper($paper);
            $response->setQuestion($question);
            $response->setIp($ip);
        } else {
            $response->setNbTries($response->getNbTries() + 1);
        }

        $handler->storeAnswerAndMark($question, $response, $data);
        if (-1 !== $response->getMark()) {
            // Only apply penalties if the answer has been marked
            $this->applyPenalties($paper, $question, $response);
        }

        $this->om->persist($response);
        $this->om->flush();
    }

    /**
     * @param Question $question
     * @param Paper    $paper
     * @param int      $score
     */
    public function recordScore(Question $question, Paper $paper, $score)
    {
        /** @var Response $response */
        $response = $this->om->getRepository('UJMExoBundle:Response')
            ->findOneBy(['paper' => $paper, 'question' => $question]);

        $response->setMark($score);

        // Apply penalties to the score
        $this->applyPenalties($paper, $question, $response);

        $scorePaper = $paper->getScore();
        $scoreExercise = $scorePaper + $response->getMark();
        $paper->setScore($scoreExercise);

        $this->om->persist($paper);
        $this->om->persist($response);
        $this->om->flush();

        $this->checkPaperEvaluated($paper);
    }

    /**
     * Terminates a user paper.
     *
     * @param Paper $paper
     */
    public function finishPaper(Paper $paper)
    {
        if (!$paper->getEnd()) {
            $paper->setEnd(new \DateTime());
        }

        $paper->setInterupt(false);
        $paper->setScore($this->calculateScore($paper->getId()));

        $this->om->flush();

        $this->checkPaperEvaluated($paper);
    }

    /**
     * Close a Paper that is not finished (because the Exercise does not allow interruption).
     *
     * @param Paper $paper
     */
    public function closePaper(Paper $paper)
    {
        if (!$paper->getEnd()) {
            $paper->setEnd(new \DateTime());
        }

        $paper->setInterupt(true); // keep track that the user has not finished
        $paper->setScore($this->calculateScore($paper->getId()));

        $this->om->flush();

        $this->checkPaperEvaluated($paper);
    }

    /**
     * Check if a Paper is full evaluated and dispatch a Log event if yes.
     *
     * @param Paper $paper
     *
     * @return $boolean
     */
    public function checkPaperEvaluated(Paper $paper)
    {
        $fullyEvaluated = $this->om->getRepository('UJMExoBundle:Response')->allPaperResponsesMarked($paper);
        if ($fullyEvaluated) {
            $event = new LogExerciseEvaluatedEvent($paper->getExercise(), [
                'result' => $paper->getScore(),
                'resultMax' => $this->paperService->getPaperTotalScore($paper->getId()),
            ]);

            $this->eventDispatcher->dispatch('log', $event);
        }

        return $fullyEvaluated;
    }

    private function calculateScore($paperId)
    {
        $score = $this->om->getRepository('UJMExoBundle:Response')
                          ->getScoreExercise($paperId);

        return $score;
    }

    /**
     * Returns the papers for a given exercise, in a JSON format.
     *
     * @param Exercise $exercise
     * @param User     $user
     *
     * @return array
     */
    public function exportExercisePapers(Exercise $exercise, User $user = null)
    {
        $isAdmin = true;
        $search = [
            'exercise' => $exercise,
        ];

        if (!empty($user)) {
            // Load papers for a single non admin User
            $isAdmin = false;
            $search['user'] = $user;
        }

        $papers = $this->om->getRepository('UJMExoBundle:Paper')
            ->findBy($search);

        $exportPapers = [];
        $exportQuestions = [];
        foreach ($papers as $paper) {
            $exportPapers[] = $this->exportPaper($paper, $isAdmin);

            $paperQuestions = new \stdClass();
            $paperQuestions->paperId = $paper->getId();
            $paperQuestions->questions = $this->exportPaperQuestions($paper, $isAdmin, true);

            $exportQuestions[] = $paperQuestions;
        }

        return [
            'papers' => $exportPapers,
            'questions' => $exportQuestions,
        ];
    }

    /**
     * Returns one specific paper details.
     *
     * @param Paper $paper
     * @param bool  $withScore If true, the score will be exported even if it's not available (for Admins)
     *
     * @return array
     */
    public function exportPaper(Paper $paper, $withScore = false)
    {
        $scoreAvailable = $withScore || $this->isScoreAvailable($paper->getExercise(), $paper);

        $_paper = [
            'id' => $paper->getId(),
            'number' => $paper->getNumPaper(),
            'user' => $this->showUserPaper($paper),
            'start' => $paper->getStart()->format('Y-m-d\TH:i:s'),
            'end' => $paper->getEnd() ? $paper->getEnd()->format('Y-m-d\TH:i:s') : null,
            'interrupted' => $paper->getInterupt(),
            'score' => $scoreAvailable ? $paper->getScore() : null,
            'order' => $this->getStepsQuestions($paper),
            'questions' => $this->exportPaperAnswers($paper, $scoreAvailable),
        ];

        return $_paper;
    }

    /**
     * Return user name or anonymous, according to exercise settings.
     *
     * @param Paper $paper
     *
     * @return string
     */
    private function showUserPaper(Paper $paper)
    {
        if (!$paper->getUser() || $paper->getAnonymous()) {
            $showUser = $this->translator->trans('anonymous', [], 'ujm_exo');
        } else {
            $showUser = $paper->getUser()->getFirstName().' '.$paper->getUser()->getLastName();
        }

        return $showUser;
    }

    /**
     * Returns the number of finished papers already done by the user for a given exercise.
     *
     * @param Exercise $exercise
     * @param User     $user
     *
     * @return array
     */
    public function countUserFinishedPapers(Exercise $exercise, User $user)
    {
        return $this->om->getRepository('UJMExoBundle:Paper')
            ->countUserFinishedPapers($exercise, $user);
    }

    /**
     * Returns the number of papers already done for a given exercise.
     *
     * @param Exercise $exercise
     *
     * @return array
     */
    public function countExercisePapers(Exercise $exercise)
    {
        return $this->om->getRepository('UJMExoBundle:Paper')
            ->countExercisePapers($exercise);
    }

    private function applyPenalties(Paper $paper, Question $question, Response $response)
    {
        $penalty = $this->hintManager->getPenalty($paper, $question);

        $response->setMark($response->getMark() - $penalty);
        if ($response->getMark() < 0) {
            $response->setMark(0);
        }
    }

    /**
     * Export the Questions linked to the Paper.
     *
     * @param Paper $paper
     * @param bool  $withSolution
     * @param bool  $forPaperList
     *
     * @return array
     */
    public function exportPaperQuestions(Paper $paper, $withSolution = false, $forPaperList = false)
    {
        $solutionAvailable = $withSolution || $this->isSolutionAvailable($paper->getExercise(), $paper);

        $export = [];
        $questions = $this->getPaperQuestions($paper);
        foreach ($questions as $question) {
            $exportedQuestion = $this->questionManager->exportQuestion($question, $solutionAvailable, $forPaperList);

            $exportedQuestion->stats = null;
            if ($paper->getExercise()->hasStatistics()) {
                $exportedQuestion->stats = $this->questionManager->generateQuestionStats($question, $paper->getExercise());
            }

            $export[] = $exportedQuestion;
        }

        return $export;
    }

    /**
     * Export submitted answers for each Question of the Paper.
     *
     * @param Paper $paper
     * @param bool  $withScore Do we need to export the score of the Paper ?
     *
     * @return array
     *
     * @throws \UJM\ExoBundle\Transfer\Json\UnregisteredHandlerException
     */
    public function exportPaperAnswers(Paper $paper, $withScore = false)
    {
        $questions = $this->getPaperQuestions($paper);
        $paperQuestions = [];

        foreach ($questions as $question) {
            $paperQuestion = $this->exportPaperAnswer($question, $paper, $withScore);
            if (!empty($paperQuestion)) {
                $paperQuestions[] = $paperQuestion;
            }
        }

        return $paperQuestions;
    }

    /**
     * Export submitted answers for one Question of the Paper.
     *
     * @param Question $question
     * @param Paper    $paper
     * @param bool     $withScore Do we need to export the score of the Paper ?
     *
     * @return array
     *
     * @throws \UJM\ExoBundle\Transfer\Json\UnregisteredHandlerException
     */
    public function exportPaperAnswer(Question $question, Paper $paper, $withScore = false)
    {
        $responseRepo = $this->om->getRepository('UJMExoBundle:Response');

        $handler = $this->handlerCollector->getHandlerForInteractionType($question->getType());
        // TODO: these two queries must be moved out of the loop
        $response = $responseRepo->findOneBy(['paper' => $paper, 'question' => $question]);

        $usedHints = $this->hintManager->getUsedHints($paper, $question);
        $hints = array_map(function ($hint) {
            return $this->hintManager->exportHint($hint, true); // We always grab hint value for used hints
        }, $usedHints);

        $answer = $response ? $handler->convertAnswerDetails($response) : null;
        $answerScore = $response ? $response->getMark() : 0;
        $nbTries = $response ? $response->getNbTries() : 0;

        $paperQuestion = null;
        if ($response || count($hints) > 0) {
            $paperQuestion = [
                'id' => $question->getId(),
                'answer' => $answer,
                'hints' => $hints,
                'nbTries' => $nbTries,
                'score' => $withScore ? $answerScore : null,
            ];
        }

        return $paperQuestion;
    }

    /**
     * Get the Questions linked to a Paper.
     *
     * @param Paper $paper
     *
     * @return Question[]
     */
    private function getPaperQuestions(Paper $paper)
    {
        $ids = explode(';', substr($paper->getOrdreQuestion(), 0, -1));

        $questions = [];
        foreach ($ids as $id) {
            $question = $this->om->getRepository('UJMExoBundle:Question')->find($id);
            if ($question) {
                $questions[] = $question;
            }
        }

        return $questions;
    }

    /**
     * Returns array of array with the indexes "step" and "question".
     *
     * @param Paper $paper
     *
     * @return array
     */
    public function getStepsQuestions(Paper $paper)
    {
        $questions = $this->getPaperQuestions($paper);
        $exercise = $paper->getExercise();

        // to keep the questions order
        $deleted = []; // Questions not linked to the exercise anymore
        $stepsQuestions = []; // Questions linked to a Step of the Exercise (the keys are the step IDs)
        foreach ($questions as $question) {
            // Check if the question is attached to a Step

            /** @var StepQuestion $stepQuestion */
            $stepQuestion = $this->om->getRepository('UJMExoBundle:StepQuestion')->findByExerciseAndQuestion($exercise, $question->getId());
            if ($stepQuestion) {
                // Question linked to a Step
                $step = $stepQuestion->getStep();
                if (!isset($stepsQuestions[$step->getId()])) {
                    $stepsQuestions[$step->getId()] = [
                        'id' => $step->getId(),
                        'items' => [],
                    ];
                }

                $stepsQuestions[$step->getId()]['items'][] = $question->getId();
            } else {
                $deleted[] = $question->getId();
            }
        }

        // Remove step ids indexes to avoid receiving an array with undefined values in JS
        $steps = array_values($stepsQuestions);

        // Append deleted questions at the end of the Exercise
        if (!empty($deleted)) {
            $steps[] = [
                'id' => 'deleted',
                'items' => $deleted,
            ];
        }

        return $steps;
    }

    /**
     * Check if the solution of the Paper is available to User.
     *
     * @param Exercise $exercise
     * @param Paper    $paper
     *
     * @return bool
     */
    public function isSolutionAvailable(Exercise $exercise, Paper $paper)
    {
        $correctionMode = $exercise->getCorrectionMode();
        switch ($correctionMode) {
            case CorrectionMode::AFTER_END:
                $available = !empty($paper->getEnd());
                break;

            case CorrectionMode::AFTER_LAST_ATTEMPT:
                $available = 0 === $exercise->getMaxAttempts() || $paper->getNumPaper() === $exercise->getMaxAttempts();
                break;

            case CorrectionMode::AFTER_DATE:
                $now = new \DateTime();
                $available = empty($exercise->getDateCorrection()) || $now >= $exercise->getDateCorrection();
                break;

            case CorrectionMode::NEVER:
            default:
                $available = false;
                break;
        }

        return $available;
    }

    /**
     * Check if the score of the Paper is available to User.
     *
     * @param Exercise $exercise
     * @param Paper    $paper
     *
     * @return bool
     */
    public function isScoreAvailable(Exercise $exercise, Paper $paper)
    {
        $markMode = $exercise->getMarkMode();
        switch ($markMode) {
            case MarkMode::AFTER_END:
                $available = !empty($paper->getEnd());
                break;
            case MarkMode::NEVER:
                $available = false;
                break;
            case MarkMode::WITH_CORRECTION:
            default:
                $available = $this->isSolutionAvailable($exercise, $paper);
                break;
        }

        return $available;
    }

    /**
     * Returns a question list according to the *shuffle* and
     * *nbQuestions* parameters of an exercise, i.e. filtered
     * and/or randomized if needed.
     *
     * @param Exercise $exercise
     *
     * @return array
     */
    public function pickQuestions(Exercise $exercise)
    {
        $steps = $this->pickSteps($exercise);
        $questionRepo = $this->om->getRepository('UJMExoBundle:Question');
        $finalQuestions = [];

        foreach ($steps as $step) {
            // TODO : do not load the Questions from DB they already are in `$step->getStepQuestions()`
            $questions = $questionRepo->findByStep($step);
            $finalQuestions = array_merge($finalQuestions, $questions);
        }

        return $finalQuestions;
    }

    /**
     * Returns a step list according to the *shuffle* and
     * nbStep* parameters of an exercise, i.e. filtered
     * and/or randomized if needed.
     *
     * @param Exercise $exercise
     *
     * @return array
     */
    public function pickSteps(Exercise $exercise)
    {
        $steps = $exercise->getSteps()->toArray();

        if ($exercise->getShuffle()) {
            shuffle($steps);
        }

        if (($stepToPick = $exercise->getPickSteps()) > 0) {
            $steps = $this->pickItem($stepToPick, $steps);
        }

        return $steps;
    }

    /**
     * Returns item (step or question) list according to the *shuffle* and
     * *nbItem* parameters of an exercise or a step, i.e. filtered
     * and/or randomized if needed.
     *
     * @param int   $itemToPick
     * @param array $listItem   array of steps or array of question
     *
     * @return array
     */
    private function pickItem($itemToPick, array $listItem)
    {
        $newListItem = [];
        while ($itemToPick > 0) {
            $index = rand(0, count($listItem) - 1);
            $newListItem[] = $listItem[$index];
            unset($listItem[$index]);
            $listItem = array_values($listItem); // "re-index" the array
            --$itemToPick;
        }

        return $newListItem;
    }
}
