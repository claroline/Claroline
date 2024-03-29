<?php

namespace UJM\ExoBundle\Manager;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Resource\ResourceEvaluation;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Validator\Exception\InvalidDataException;
use Claroline\EvaluationBundle\Manager\ResourceEvaluationManager;
use UJM\ExoBundle\Entity\Attempt\Answer;
use UJM\ExoBundle\Entity\Attempt\Paper;
use UJM\ExoBundle\Entity\Exercise;
use UJM\ExoBundle\Entity\Item\Item;
use UJM\ExoBundle\Library\Attempt\PaperGenerator;
use UJM\ExoBundle\Manager\Attempt\AnswerManager;
use UJM\ExoBundle\Manager\Attempt\PaperManager;
use UJM\ExoBundle\Manager\Item\ItemManager;
use UJM\ExoBundle\Repository\PaperRepository;
use UJM\ExoBundle\Serializer\Item\ItemSerializer;

/**
 * AttemptManager provides methods to manage user attempts to quiz.
 */
class AttemptManager
{
    private PaperRepository $paperRepository;

    public function __construct(
        private readonly ObjectManager $om,
        private readonly ResourceEvaluationManager $resourceEvalManager,
        private readonly PaperGenerator $paperGenerator,
        private readonly PaperManager $paperManager,
        private readonly AnswerManager $answerManager,
        private readonly ItemManager $itemManager,
        private readonly ItemSerializer $itemSerializer
    ) {
        $this->paperRepository = $this->om->getRepository(Paper::class);
    }

    /**
     * Checks if a user is allowed to pass a quiz or not.
     *
     * Based on the maximum attempt allowed and the number of already done by the user.
     */
    public function canPass(Exercise $exercise, User $user = null): bool
    {
        $canPass = true;
        if ($user) {
            $max = $exercise->getMaxAttempts();
            if ($max > 0) {
                $evaluation = $this->resourceEvalManager->getUserEvaluation($exercise->getResourceNode(), $user);
                if ($evaluation->getNbAttempts() >= $max) {
                    $canPass = false;
                }
            }
        }

        return $canPass;
    }

    public function getErrors(Exercise $exercise, User $user = null): array
    {
        $errors = [];
        if ($user) {
            $max = $exercise->getMaxAttempts();
            if ($max > 0) {
                // quiz has limited attempts
                $evaluation = $this->resourceEvalManager->getUserEvaluation($exercise->getResourceNode(), $user);

                $errors['maxAttemptsReached'] = false;
                if ($evaluation->getNbAttempts() >= $max) {
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
     */
    public function canUpdate(Paper $paper, User $user = null): bool
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
    public function startOrContinue(Exercise $exercise, User $user = null): Paper
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
            // This can be recomputed later, but it's a slightly heavy task and require the use of the manager.
            $paper->setTotal(
                $this->paperManager->calculateTotal($paper)
            );

            // Save the new paper
            $this->om->persist($paper);
            $this->om->flush();
        }

        return $paper;
    }

    public function getLastPaper(Exercise $exercise, User $user = null): ?Paper
    {
        if (null !== $user) {
            return $this->paperRepository->findLastPaper($exercise, $user);
        }

        return null;
    }

    /**
     * Submits user answers to a paper.
     *
     * @throws InvalidDataException - if there is any invalid answer
     */
    public function submit(Paper $paper, array $answers, string $clientIp): array
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
     */
    public function end(Paper $paper, ?bool $finished = true, ?bool $generateEvaluation = true): ?ResourceEvaluation
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

        $attempt = null;
        if ($generateEvaluation) {
            $attempt = $this->paperManager->generateResourceEvaluation($paper, $finished);
        }

        return $attempt;
    }

    /**
     * Flags a hint has used in the user paper and returns the hint content.
     */
    public function useHint(Paper $paper, string $questionId, string $hintId, string $clientIp): ?array
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

    public function getAttempt(Paper $paper): ?ResourceEvaluation
    {
        return $this->paperRepository->getPaperAttempt($paper);
    }
}
