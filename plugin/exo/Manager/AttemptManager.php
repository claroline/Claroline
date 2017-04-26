<?php

namespace UJM\ExoBundle\Manager;

use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Persistence\ObjectManager;
use JMS\DiExtraBundle\Annotation as DI;
use UJM\ExoBundle\Entity\Attempt\Answer;
use UJM\ExoBundle\Entity\Attempt\Paper;
use UJM\ExoBundle\Entity\Exercise;
use UJM\ExoBundle\Entity\Item\Item;
use UJM\ExoBundle\Library\Attempt\PaperGenerator;
use UJM\ExoBundle\Library\Validator\ValidationException;
use UJM\ExoBundle\Manager\Attempt\AnswerManager;
use UJM\ExoBundle\Manager\Attempt\PaperManager;
use UJM\ExoBundle\Manager\Item\ItemManager;
use UJM\ExoBundle\Repository\PaperRepository;
use UJM\ExoBundle\Serializer\Item\ItemSerializer;

/**
 * AttemptManager provides methods to manage user attempts to exercises.
 *
 * @DI\Service("ujm_exo.manager.attempt")
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
     * AttemptManager constructor.
     *
     * @DI\InjectParams({
     *     "om"             = @DI\Inject("claroline.persistence.object_manager"),
     *     "paperGenerator" = @DI\Inject("ujm_exo.generator.paper"),
     *     "paperManager"   = @DI\Inject("ujm_exo.manager.paper"),
     *     "answerManager"  = @DI\Inject("ujm_exo.manager.answer"),
     *     "itemManager"    = @DI\Inject("ujm_exo.manager.item"),
     *     "itemSerializer" = @DI\Inject("ujm_exo.serializer.item")
     * })
     *
     * @param ObjectManager  $om
     * @param PaperGenerator $paperGenerator
     * @param PaperManager   $paperManager
     * @param AnswerManager  $answerManager
     * @param ItemManager    $itemManager
     * @param ItemSerializer $itemSerializer
     */
    public function __construct(
        ObjectManager $om,
        PaperGenerator $paperGenerator,
        PaperManager $paperManager,
        AnswerManager $answerManager,
        ItemManager $itemManager,
        ItemSerializer $itemSerializer)
    {
        $this->om = $om;
        $this->paperGenerator = $paperGenerator;
        $this->paperManager = $paperManager;
        $this->paperRepository = $this->om->getRepository('UJMExoBundle:Attempt\Paper');
        $this->answerManager = $answerManager;
        $this->itemManager = $itemManager;
        $this->itemSerializer = $itemSerializer;
    }

    /**
     * Checks if a user is allowed to pass a quiz or not.
     *
     * Based on the maximum attempt allowed and the number of already done by the user.
     *
     * @param Exercise $exercise
     * @param User     $user
     *
     * @return bool
     */
    public function canPass(Exercise $exercise, User $user = null)
    {
        $canPass = true;
        if ($user) {
            $max = $exercise->getMaxAttempts();
            $nbFinishedPapers = $this->paperManager->countUserFinishedPapers($exercise, $user);

            if ($max > 0 && $nbFinishedPapers >= $max) {
                $canPass = false;
            }
        }

        return $canPass;
    }

    /**
     * Checks if a user can submit answers to a paper or use hints.
     *
     * A user can submit to a paper only if it is its own and the paper is not closed (= no end).
     * ATTENTION : As is, anonymous have access to all the other anonymous Papers !!!
     *
     * @param Paper $paper
     * @param User  $user
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
            $lastPaper = (null !== $user) ? $this->paperRepository->findLastPaper($exercise, $user) : null;

            // Generate a new paper
            $paper = $this->paperGenerator->create($exercise, $user, $lastPaper);

            // Save the new paper
            $this->om->persist($paper);
            $this->om->flush();
        }

        return $paper;
    }

    /**
     * Submits user answers to a paper.
     *
     * @param Paper       $paper
     * @param \stdClass[] $answers
     * @param string      $clientIp
     *
     * @throws ValidationException - if there is any invalid answer
     *
     * @return Answer[]
     */
    public function submit(Paper $paper, array $answers, $clientIp)
    {
        $submitted = [];

        $this->om->startFlushSuite();

        foreach ($answers as $answerData) {
            $question = $paper->getQuestion($answerData->questionId);
            if (empty($question)) {
                throw new ValidationException('Submitted answers are invalid', [[
                    'path' => '/questionId',
                    'message' => 'question is not part of the attempt',
                ]]);
            }

            $existingAnswer = $paper->getAnswer($answerData->questionId);
            $decodedQuestion = $this->itemSerializer->deserialize($question, new Item());

            try {
                if (empty($existingAnswer)) {
                    $answer = $this->answerManager->create($decodedQuestion, $answerData);
                } else {
                    $answer = $this->answerManager->update($decodedQuestion, $existingAnswer, $answerData);
                }
            } catch (ValidationException $e) {
                throw new ValidationException('Submitted answers are invalid', $e->getErrors());
            }

            $answer->setIp($clientIp);
            $answer->setTries($answer->getTries() + 1);

            // Calculate new answer score
            $score = $this->itemManager->calculateScore($decodedQuestion, $answer);
            $answer->setScore($score);

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
     * @param Paper $paper
     * @param bool  $finished
     */
    public function end(Paper $paper, $finished = true)
    {
        if (!$paper->getEnd()) {
            $paper->setEnd(new \DateTime());
        }

        $paper->setInterrupted(!$finished);
        $paper->setScore($this->paperManager->calculateScore($paper, $paper->getExercise()->getTotalScoreOn()));

        $this->om->persist($paper);
        $this->om->flush();

        $this->paperManager->checkPaperEvaluated($paper);
    }

    /**
     * Flags an hint has used in the user paper and returns the hint content.
     *
     * @param Paper  $paper
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
        foreach ($question->hints as $questionHint) {
            if ($hintId === $questionHint->id) {
                $hint = $questionHint;
                break;
            }
        }

        if (empty($hint)) {
            // Hint is not related to a question of the current attempt
            throw new \LogicException("Hint {$hintId} and paper {$paper->getId()} are not related");
        }

        // Retrieve or create the answer for the question
        $answer = $paper->getAnswer($question->id);
        if (empty($answer)) {
            $answer = new Answer();
            $answer->setTries(0); // Using an hint is not a try. This will be updated when user will submit his answer
            $answer->setQuestionId($question->id);
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
