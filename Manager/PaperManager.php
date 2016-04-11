<?php

namespace UJM\ExoBundle\Manager;

use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Persistence\ObjectManager;
use JMS\DiExtraBundle\Annotation as DI;
use UJM\ExoBundle\Entity\Exercise;
use UJM\ExoBundle\Entity\Hint;
use UJM\ExoBundle\Entity\LinkHintPaper;
use UJM\ExoBundle\Entity\Paper;
use UJM\ExoBundle\Entity\Question;
use UJM\ExoBundle\Entity\Response;
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
     *     "collector"          = @DI\Inject("ujm.exo.question_handler_collector"),
     *     "exerciseManager"    = @DI\Inject("ujm.exo.exercise_manager"),
     *     "questionManager"    = @DI\Inject("ujm.exo.question_manager"),
     *     "translator"         = @DI\Inject("translator")
     * })
     *
     * @param ObjectManager $om
     * @param QuestionHandlerCollector $collector
     * @param ExerciseManager $exerciseManager
     * @param QuestionManager $questionManager
     * @param Translator $translator
     */
    public function __construct(
        ObjectManager $om,
        QuestionHandlerCollector $collector,
        ExerciseManager $exerciseManager,
        QuestionManager $questionManager,
        TranslatorInterface $translator

    ) {
        $this->om = $om;
        $this->handlerCollector = $collector;
        $this->exerciseManager = $exerciseManager;
        $this->questionManager = $questionManager;
        $this->translator = $translator;
    }

    /**
     * Returns the JSON representation of an exercise with its last associated paper
     * for a given user. If no paper exists, a new one is created.
     *
     * @param Exercise $exercise
     * @param User $user
     * @param bool $withSolutions
     * @return array
     */
    public function openPaper(Exercise $exercise, User $user, $withSolutions = false)
    {
        return [
            'exercise' => $this->exerciseManager->exportExercise($exercise, $withSolutions),
            'paper' => $this->exportPaper($exercise, $user)
        ];
    }

    /**
     * Creates a new exercise paper for a given user.
     *
     * @param User $user
     * @param Exercise $exercise
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
            $order .= $question->getId() . ';';
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
     * Exports a paper in a JSON-encodable format. If no unfinished paper exists for
     * that given exercise and user, a new one is created. Otherwise the latest is
     * returned.
     *
     * @param User $user
     * @param Exercise $exercise
     * @return Paper
     */
    public function exportPaper(Exercise $exercise, User $user)
    {
        $repo = $this->om->getRepository('UJMExoBundle:Paper');
        $papers = $repo->findUnfinishedPapers($user, $exercise);

        if (count($papers) === 0) {
            $paper = $this->createPaper($user, $exercise);
            $questions = [];
        } else {
            $paper = $papers[0];
            $questions = $this->exportPaperQuestions($paper);
        }

        return [
            'id' => $paper->getId(),
            'number' => $paper->getNumPaper(),
            'questions' => $questions
        ];
    }

    /**
     * Records or updates an answer for a given question and paper.
     *
     * @param Paper $paper
     * @param Question $question
     * @param mixed $data
     * @param string $ip
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
        $this->applyPenalties($paper, $question, $response);

        $this->om->persist($response);
        $this->om->flush();
    }

    /**
     *
     * @param Question $question
     * @param Paper $paper
     * @param int $score
     */
    public function recordOpenScore(Question $question, Paper $paper, $score)
    {
        $response = $this->om->getRepository('UJMExoBundle:Response')
            ->findOneBy(['paper' => $paper, 'question' => $question]);

        $response->setMark($score);

        $scorePaper = $paper->getScore();
        $scoreExercise = $scorePaper + $score;
        $paper->setScore($scoreExercise);

        $this->om->persist($paper);
        $this->om->persist($response);
        $this->om->flush();
    }


    /**
     * Returns whether a hint is related to a paper.
     *
     * @param Paper $paper
     * @param Hint $hint
     * @return bool
     */
    public function hasHint(Paper $paper, Hint $hint)
    {
        $link = $this->om->getRepository('UJMExoBundle:StepQuestion')->findStepByExoQuestion(
            $paper->getExercise(),
            $hint->getQuestion()
        );

        return $link !== null;
    }

    /**
     * Returns the contents of a hint and records a log asserting that the hint
     * has been consulted for a given paper.
     *
     * @param Paper $paper
     * @param Hint $hint
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
    }

    private function calculateScore($paperId)
    {
        $score = $this->om->getRepository('UJMExoBundle:Response')
                          ->getScoreExercise($paperId);

        return $score;
    }

    /**
     * Returns the papers of a user for a given exercise, in a JSON format.
     * Also includes the complete definition and solution of each question
     * associated with the exercise.
     *
     * @param Exercise $exercise
     * @param User $user
     * @return array
     */
    public function exportUserPapers(Exercise $exercise, User $user)
    {
        $questionRepo = $this->om->getRepository('UJMExoBundle:Question');
        $papers = $this->om->getRepository('UJMExoBundle:Paper')
            ->findBy(['exercise' => $exercise, 'user' => $user]);

        $papers = array_map(function ($paper) {

            return [
                'id' => $paper->getId(),
                'number' => $paper->getNumPaper(),
                'user' => $this->showUserPaper($paper),
                'start' => $paper->getStart()->format('Y-m-d H:i:s'),
                'end' => $paper->getEnd() ? $paper->getEnd()->format('Y-m-d H:i:s') : null,
                'interrupted' => $paper->getInterupt(),
                'questions' => $this->exportPaperQuestions($paper)
            ];
        }, $papers);

        $questions = array_map(function ($question) {
            return $this->questionManager->exportQuestion($question, true);
        }, $questionRepo->findByExercise($exercise));

        return [
            'questions' => $questions,
            'papers' => $papers
        ];
    }

    /**
     * Returns one specific paper details.
     * Also includes the complete definition and solution of each question
     * associated with the exercise.
     *
     * @param Paper $paper
     * @param Exercise $exercise
     * @return array
     */
    public function exportUserPaper(Paper $paper, Exercise $exercise)
    {
        $questionRepo = $this->om->getRepository('UJMExoBundle:Question');

        $_paper = [
            'id' => $paper->getId(),
            'number' => $paper->getNumPaper(),
            'user' => $this->showUserPaper($paper),
            'start' => $paper->getStart()->format('Y-m-d H:i:s'),
            'end' => $paper->getEnd() ? $paper->getEnd()->format('Y-m-d H:i:s') : null,
            'interrupted' => $paper->getInterupt(),
            'questions' => $this->exportPaperQuestions($paper)
        ];

        $questions = array_map(function ($question) {
            return $this->questionManager->exportQuestion($question, true, true);
        }, $questionRepo->findByExercise($exercise));

        return [
            'questions' => $questions,
            'paper' => $_paper
        ];
    }

    /**
     * Return user name or anonymous, according to exercise settings
     *
     * @param Paper $paper
     * @return string
     */
    private function showUserPaper(Paper $paper)
    {
        $user = $paper->getUser();

        $showUser = $user->getFirstName() . ' ' . $user->getLastName();

        if ($paper->getAnonymous()) {
            $showUser = $this->translator->trans('anonymous', array(), 'ujm_exo');
        }

        return $showUser;
    }

    /**
     * Returns the number of finished papers already done by the user for a given exercise
     * @param Exercise $exercise
     * @param User $user
     * @return array
     */
    public function countUserFinishedPapers(Exercise $exercise, User $user)
    {
        $nbPapers = $this->om->getRepository('UJMExoBundle:Paper')
            ->countUserFinishedPapers($exercise, $user);
        return $nbPapers;
    }

    /**
     * Returns the papers for a given exercise, in a JSON format.
     * Also includes the complete definition and solution of each question
     * associated with the exercise.
     *
     * @param Exercise $exercise
     * @return array
     */
    public function exportExercisePapers(Exercise $exercise)
    {
        $questionRepo = $this->om->getRepository('UJMExoBundle:Question');
        $papers = $this->om->getRepository('UJMExoBundle:Paper')
            ->findBy(['exercise' => $exercise]);

        $papers = array_map(function ($paper) {

            return [
                'id' => $paper->getId(),
                'user' => $this->showUserPaper($paper),
                'number' => $paper->getNumPaper(),
                'start' => $paper->getStart()->format('Y-m-d H:i:s'),
                'end' => $paper->getEnd() ? $paper->getEnd()->format('Y-m-d H:i:s') : null,
                'interrupted' => $paper->getInterupt(),
                'questions' => $this->exportPaperQuestions($paper)
            ];
        }, $papers);

        $questions = array_map(function ($question) {
            return $this->questionManager->exportQuestion($question, true, true);
        }, $questionRepo->findByExercise($exercise));

        return [
            'questions' => $questions,
            'papers' => $papers
        ];
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

    private function exportPaperQuestions(Paper $paper)
    {
        $responseRepo = $this->om->getRepository('UJMExoBundle:Response');
        $linkRepo = $this->om->getRepository('UJMExoBundle:LinkHintPaper');
        $questionRepo = $this->om->getRepository('UJMExoBundle:Question');
        $questions = $questionRepo->findByExercise($paper->getExercise());
        $paperQuestions = [];

        foreach ($questions as $question) {
            $handler = $this->handlerCollector->getHandlerForInteractionType($question->getType());
            // TODO: these two queries must be moved out of the loop
            $response = $responseRepo->findOneBy(['paper' => $paper, 'question' => $question]);
            $links = $linkRepo->findViewedByPaperAndQuestion($paper, $question);

            $answer = $response ? $handler->convertAnswerDetails($response) : null;
            $answerScore = $response ? $response->getMark() : 0;
            $hints = array_map(function ($link) {
                return (string)$link->getHint()->getId();
            }, $links);

            if ($answer || count($hints) > 0) {
                $paperQuestions[] = [
                    'id' => (string)$question->getId(),
                    'answer' => $answer,
                    'hints' => $hints,
                    'score' => $answerScore
                ];
            }
        }

        return $paperQuestions;
    }
}
