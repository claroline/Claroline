<?php

namespace UJM\ExoBundle\Manager;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Validator\Exception\InvalidDataException;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use UJM\ExoBundle\Entity\Attempt\Answer;
use UJM\ExoBundle\Entity\Attempt\Paper;
use UJM\ExoBundle\Entity\Exercise;
use UJM\ExoBundle\Entity\Item\Item;
use UJM\ExoBundle\Event\Log\LogExerciseEvent;
use UJM\ExoBundle\Library\Attempt\PaperGenerator;
use UJM\ExoBundle\Manager\Attempt\AnswerManager;
use UJM\ExoBundle\Manager\Attempt\PaperManager;
use UJM\ExoBundle\Manager\Item\ItemManager;
use UJM\ExoBundle\Repository\PaperRepository;
use UJM\ExoBundle\Serializer\Item\ItemSerializer;

/**
 * AttemptManager provides methods to manage user attempts to exercises.
 */
class AttemptManager
{
    /**
     * @var ObjectManager
     */
    private $om;

    /**
     * @var PaperGenerator
     */
    private $paperGenerator;

    /**
     * @var PaperManager
     */
    private $paperManager;

    /**
     * @var PaperRepository
     */
    private $paperRepository;

    /**
     * @var AnswerManager
     */
    private $answerManager;

    /**
     * @var ItemManager
     */
    private $itemManager;

    /**
     * @var ItemSerializer
     */
    private $itemSerializer;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * AttemptManager constructor.
     */
    public function __construct(
        ObjectManager $om,
        PaperGenerator $paperGenerator,
        PaperManager $paperManager,
        AnswerManager $answerManager,
        ItemManager $itemManager,
        ItemSerializer $itemSerializer,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->om = $om;
        $this->paperGenerator = $paperGenerator;
        $this->paperManager = $paperManager;
        $this->paperRepository = $this->om->getRepository(Paper::class);
        $this->answerManager = $answerManager;
        $this->itemManager = $itemManager;
        $this->itemSerializer = $itemSerializer;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * Checks if a user is allowed to pass a quiz or not.
     *
     * Based on the maximum attempt allowed and the number of already done by the user.
     *
     * @param User $user
     *
     * @return bool
     */
    public function canPass(Exercise $exercise, User $user = null)
    {
        // TODO : max attempts by day

        $canPass = true;
        if ($user) {
            $max = $exercise->getMaxAttempts();
            if ($max > 0) {
                $nbFinishedPapers = $this->paperManager->countUserFinishedPapers($exercise, $user);
                if ($nbFinishedPapers >= $max) {
                    $canPass = false;
                }
            }
        }

        return $canPass;
    }

    public function getErrors(Exercise $exercise, User $user = null)
    {
        // TODO : max attempts by day

        $errors = [];
        if ($user) {
            $max = $exercise->getMaxAttempts();
            if ($max > 0) {
                // quiz has limited attempts
                $nbFinishedPapers = $this->paperManager->countUserFinishedPapers($exercise, $user);

                $errors['maxAttemptsReached'] = false;
                if ($nbFinishedPapers >= $max) {
                    $errors['maxAttemptsReached'] = true;
                }
            }
        }

        return $errors;
    }

    /**
     * Checks if a user can submit answers to a paper or use hints.
     *
     * A user can submit to a paper only if it is its own and the paper is not closed (= no end).
     * ATTENTION : As is, anonymous have access to all the other anonymous Papers !!!
     *
     * @param User $user
     *
     * @return bool
     */
    public function canUpdate(Paper $paper, User $user = null)
    {
        return empty($paper->getEnd())
            && $user === $paper->getUser();
    }

    /**
     * Starts or continues an exercise paper.
     *
     * Returns an unfinished paper if the user has one (and exercise allows continue)
     * or creates a new paper in the other cases.
     * Note : an anonymous user will never be able to continue a paper
     *
     * @param Exercise $exercise - the exercise to play
     * @param User     $user     - the user who wants to play the exercise
     *
     * @return Paper - a new paper or an unfinished one
     */
    public function startOrContinue(Exercise $exercise, User $user = null)
    {
        $paper = null; // The paper to use for the new attempt

        // If it's not an anonymous, load the previous unfinished papers
        $unfinishedPapers = (null !== $user) ? $this->paperRepository->findUnfinishedPapers($exercise, $user) : [];
        if (!empty($unfinishedPapers)) {
            if ($exercise->isInterruptible()) {
                // Continue a previous attempt
                $paper = $unfinishedPapers[0];
            } else {
                // Close the paper
                $this->end($unfinishedPapers[0], false);
            }
        }

        // Start a new attempt is needed
        if (empty($paper)) {
            // Get the last paper for generation
            $lastPaper = $this->getLastPaper($exercise, $user);

            // Generate a new paper
            $paper = $this->paperGenerator->create($exercise, $user, $lastPaper);

            // Calculate the total score of the paper
            // This can be recomputed later but it's a slightly heavy task and require the use of the manager.
            $paper->setTotal(
                $this->paperManager->calculateTotal($paper)
            );

            // Save the new paper
            $this->om->persist($paper);
            $this->om->flush();
        }

        $user = $paper->getUser();
        $event = new LogExerciseEvent('resource-ujm_exercise-paper-start-or-continue', $paper->getExercise(), [
          'user' => $user ?
           ['username' => $user->getUsername(), 'first_name' => $user->getFirstName(), 'last_name' => $user->getLastName()] : 'anon',
        ]);
        $this->eventDispatcher->dispatch($event, 'log');

        return $paper;
    }

    public function getLastPaper(Exercise $exercise, User $user = null)
    {
        if (null !== $user) {
            return $this->paperRepository->findLastPaper($exercise, $user);
        }

        return null;
    }

    /**
     * Submits user answers to a paper.
     *
     * @param string $clientIp
     *
     * @throws InvalidDataException - if there is any invalid answer
     *
     * @return Answer[]
     */
    public function submit(Paper $paper, array $answers, $clientIp)
    {
        $submitted = [];

        $this->om->startFlushSuite();

        foreach ($answers as $answerData) {
            $question = $paper->getQuestion($answerData['questionId']);

            if (empty($question)) {
                throw new InvalidDataException('Submitted answers are invalid', [['path' => '/questionId', 'message' => 'question is not part of the attempt']]);
            }

            $existingAnswer = $paper->getAnswer($answerData['questionId']);
            $decodedQuestion = $this->itemSerializer->deserialize($question, new Item());

            try {
                if (empty($existingAnswer)) {
                    $answer = $this->answerManager->create($decodedQuestion, $answerData);
                } else {
                    $answer = $this->answerManager->update($decodedQuestion, $existingAnswer, $answerData);
                }
            } catch (InvalidDataException $e) {
                throw new InvalidDataException('Submitted answers are invalid', $e->getErrors());
            }

            $answer->setIp($clientIp);
            $answer->setTries($answer->getTries() + 1);

            // Calculate new answer score if needed
            $answer->setScore(
                $this->itemManager->calculateScore($decodedQuestion, $answer)
            );

            $paper->addAnswer($answer);
            $submitted[] = $answer;
        }

        $this->om->persist($paper);
        $this->om->endFlushSuite();

        return $submitted;
    }

    /**
     * Ends a user paper.
     * Sets the end date of the paper and calculates its score.
     *
     * @param bool $finished
     * @param bool $generateEvaluation
     */
    public function end(Paper $paper, $finished = true, $generateEvaluation = true)
    {
        $this->om->startFlushSuite();

        if (!$paper->getEnd()) {
            $paper->setEnd(new \DateTime());
        }

        $paper->setInterrupted(!$finished);
        $score = $this->paperManager->calculateScore($paper);
        $paper->setScore($score);

        $this->om->persist($paper);
        $this->om->endFlushSuite();

        $this->paperManager->checkPaperEvaluated($paper);

        if ($generateEvaluation) {
            $this->paperManager->generateResourceEvaluation($paper, $finished);

            $user = $paper->getUser();
            $event = new LogExerciseEvent('resource-ujm_exercise-paper-end', $paper->getExercise(), [
                'user' => $user ? ['username' => $user->getUsername(), 'first_name' => $user->getFirstName(), 'last_name' => $user->getLastName()] : 'anon',
            ]);
            $this->eventDispatcher->dispatch($event, 'log');
        }
    }

    /**
     * Flags an hint has used in the user paper and returns the hint content.
     *
     * @param string $questionId
     * @param string $hintId
     * @param string $clientIp
     *
     * @return mixed
     */
    public function useHint(Paper $paper, $questionId, $hintId, $clientIp)
    {
        $question = $paper->getQuestion($questionId);

        if (empty($question)) {
            throw new \LogicException("Question {$questionId} and paper {$paper->getId()} are not related");
        }

        $hint = null;

        foreach ($question['hints'] as $questionHint) {
            if ($hintId === $questionHint['id']) {
                $hint = $questionHint;
                break;
            }
        }

        if (empty($hint)) {
            // Hint is not related to a question of the current attempt
            throw new \LogicException("Hint {$hintId} and paper {$paper->getId()} are not related");
        }

        // Retrieve or create the answer for the question
        $answer = $paper->getAnswer($question['id']);

        if (empty($answer)) {
            $answer = new Answer();
            $answer->setTries(0); // Using an hint is not a try. This will be updated when user will submit his answer
            $answer->setQuestionId($question['id']);
            $answer->setIp($clientIp);

            // Link the new answer to the paper
            $paper->addAnswer($answer);
        }

        $answer->addUsedHint($hintId);

        // Calculate new answer score
        $decodedQuestion = $this->itemSerializer->deserialize($question, new Item());
        $score = $this->itemManager->calculateScore($decodedQuestion, $answer);
        $answer->setScore($score);

        $this->om->persist($answer);
        $this->om->flush();

        return $hint;
    }
}
