<?php

namespace UJM\ExoBundle\Manager;

use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Persistence\ObjectManager;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use UJM\ExoBundle\Entity\Exercise;
use UJM\ExoBundle\Entity\Hint;
use UJM\ExoBundle\Entity\LinkHintPaper;
use UJM\ExoBundle\Entity\Paper;
use UJM\ExoBundle\Entity\Question;
use UJM\ExoBundle\Entity\Response;
use UJM\ExoBundle\Event\Log\LogExerciseEvaluatedEvent;
use UJM\ExoBundle\Library\Mode\CorrectionMode;
use UJM\ExoBundle\Library\Mode\MarkMode;
use UJM\ExoBundle\Services\classes\PaperService;
use UJM\ExoBundle\Transfer\Json\QuestionHandlerCollector;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * @DI\Service("ujm.exo.paper_manager")
 */
class PaperManager
{
    private $om;
    private $handlerCollector;
    private $exerciseManager;
    private $questionManager;
    private $translator;

    /**
     * @DI\InjectParams({
     *     "om"                 = @DI\Inject("claroline.persistence.object_manager"),
     *     "eventDispatcher"    = @DI\Inject("event_dispatcher"),
     *     "collector"          = @DI\Inject("ujm.exo.question_handler_collector"),
     *     "exerciseManager"    = @DI\Inject("ujm.exo.exercise_manager"),
     *     "questionManager"    = @DI\Inject("ujm.exo.question_manager"),
     *     "translator"         = @DI\Inject("translator"),
     *     "paperService"       = @DI\Inject("ujm.exo_paper")
     * })
     *
     * @param ObjectManager            $om
     * @param EventDispatcherInterface $eventDispatcher
     * @param QuestionHandlerCollector $collector
     * @param ExerciseManager          $exerciseManager
     * @param QuestionManager          $questionManager
     * @param TranslatorInterface      $translator
     * @param PaperService             $paperService
     */
    public function __construct(
        ObjectManager $om,
        EventDispatcherInterface $eventDispatcher,
        QuestionHandlerCollector $collector,
        ExerciseManager $exerciseManager,
        QuestionManager $questionManager,
        TranslatorInterface $translator,
        PaperService $paperService
    ) {
        $this->om = $om;
        $this->eventDispatcher = $eventDispatcher;
        $this->handlerCollector = $collector;
        $this->exerciseManager = $exerciseManager;
        $this->questionManager = $questionManager;
        $this->translator = $translator;
        $this->paperService = $paperService;
    }

    /**
     * Returns the JSON representation of an exercise with its last associated paper
     * for a given user. If no paper exists, a new one is created.
     *
     * @param Exercise $exercise
     * @param User     $user
     *
     * @return array
     */
    public function openPaper(Exercise $exercise, User $user)
    {
        $repo = $this->om->getRepository('UJMExoBundle:Paper');
        $papers = $repo->findUnfinishedPapers($user, $exercise);

        if (count($papers) === 0) {
            $paper = $this->createPaper($user, $exercise);
        } else {
            if (!$exercise->getDispButtonInterrupt()) {
                // User is not allowed to continue is previous paper => open a new one and close the previous
                $this->closePaper($papers[0]);

                $paper = $this->createPaper($user, $exercise);
            } else {
                $paper = $papers[0];
            }
        }

        return $this->exportPaper($paper);
    }

    /**
     * Creates a new exercise paper for a given user.
     *
     * @param User     $user
     * @param Exercise $exercise
     *
     * @return Paper
     */
    public function createPaper(User $user, Exercise $exercise)
    {
        $lastPaper = $this->om->getRepository('UJMExoBundle:Paper')->findOneBy(
            ['user' => $user, 'exercise' => $exercise],
            ['start' => 'DESC']
        );

        $paperNum = $lastPaper ? $lastPaper->getNumPaper() + 1 : 1;
        $questions = $this->exerciseManager->pickQuestions($exercise);
        $order = '';

        foreach ($questions as $question) {
            $order .= $question->getId().';';
        }

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
     * Returns whether a hint is related to a paper.
     *
     * @param Paper $paper
     * @param Hint  $hint
     *
     * @return bool
     */
    public function hasHint(Paper $paper, Hint $hint)
    {
        return $this->om->getRepository('UJMExoBundle:Paper')->hasHint($paper, $hint);
    }

    /**
     * Returns the contents of a hint and records a log asserting that the hint
     * has been consulted for a given paper.
     *
     * @param Paper $paper
     * @param Hint  $hint
     *
     * @return string
     */
    public function viewHint(Paper $paper, Hint $hint)
    {
        $log = $this->om->getRepository('UJMExoBundle:LinkHintPaper')
            ->findOneBy(['paper' => $paper, 'hint' => $hint]);

        if (!$log) {
            $log = new LinkHintPaper($hint, $paper);
            $this->om->persist($log);
            $this->om->flush();
        }

        return $hint->getValue();
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
            $paperQuestions->questions = $this->exportPaperQuestions($paper, $isAdmin);

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
            'scoreTotal' => $scoreAvailable ? $paper->getScore() : null,
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
        $user = $paper->getUser();

        $showUser = $user->getFirstName().' '.$user->getLastName();

        if ($paper->getAnonymous()) {
            $showUser = $this->translator->trans('anonymous', array(), 'ujm_exo');
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
        $logs = $this->om->getRepository('UJMExoBundle:LinkHintPaper')
            ->findViewedByPaperAndQuestion($paper, $question);

        if (count($logs) === 0) {
            return;
        }

        $penalty = 0;

        foreach ($logs as $log) {
            $penalty += $log->getHint()->getPenalty();
        }

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
     *
     * @return array
     */
    public function exportPaperQuestions(Paper $paper, $withSolution = false)
    {
        $solutionAvailable = $withSolution || $this->isSolutionAvailable($paper->getExercise(), $paper);

        $export = [];
        $questions = $this->getPaperQuestions($paper);
        foreach ($questions as $question) {
            $exportedQuestion = $this->questionManager->exportQuestion($question, $solutionAvailable, true);

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
    private function exportPaperAnswers(Paper $paper, $withScore = false)
    {
        $responseRepo = $this->om->getRepository('UJMExoBundle:Response');
        $linkRepo = $this->om->getRepository('UJMExoBundle:LinkHintPaper');
        $questions = $this->getPaperQuestions($paper);
        $paperQuestions = [];

        foreach ($questions as $question) {
            $handler = $this->handlerCollector->getHandlerForInteractionType($question->getType());
            // TODO: these two queries must be moved out of the loop
            $response = $responseRepo->findOneBy(['paper' => $paper, 'question' => $question]);
            $links = $linkRepo->findViewedByPaperAndQuestion($paper, $question);

            $answer = $response ? $handler->convertAnswerDetails($response) : null;
            $answerScore = $response ? $response->getMark() : 0;
            $hints = array_map(function ($link) {
                return [
                    'id' => $link->getHint()->getId(),
                    'value' => $link->getHint()->getValue(),
                    'penalty' => $link->getHint()->getPenalty(),
                ];
            }, $links);

            if ($answer || count($hints) > 0) {
                $paperQuestions[] = [
                    'id' => (string) $question->getId(),
                    'answer' => $answer,
                    'hints' => $hints,
                    'score' => $withScore ? $answerScore : null,
                ];
            }
        }

        return $paperQuestions;
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
        $stepsQuestions = [];

        $deleted = [];
        foreach ($exercise->getSteps() as $step) {
            $stepQuestions = [
                'id' => $step->getId(),
                'items' => [],
            ];

            // to keep the questions order
            foreach ($questions as $question) {
                $sq = $this->om->getRepository('UJMExoBundle:StepQuestion')->findOneBy(['step' => $step, 'question' => $question]);
                if ($sq && $sq->getQuestion()->getId()) {
                    $stepQuestions['items'][] = $sq->getQuestion()->getId();
                } else {
                    // Question is no longer in the Exercise
                    $deleted[] = $question;
                }
            }

            // Step is not empty
            if (!empty($stepQuestions['items'])) {
                $stepsQuestions[] = $stepQuestions;
            }
        }

        // Append deleted questions at the end of the Exercise
        if (!empty($deleted)) {
            $stepForDeleted = [
                'id' => null,
                'items' => [],
            ];

            foreach ($deleted as $question) {
                $stepForDeleted['items'][] = $question->getId();
            }

            $stepsQuestions[] = $stepForDeleted;
        }

        return $stepsQuestions;
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

            case MarkMode::WITH_CORRECTION:
            default:
                $available = $this->isSolutionAvailable($exercise, $paper);
                break;
        }

        return $available;
    }
}
