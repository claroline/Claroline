<?php

namespace UJM\ExoBundle\Manager\Attempt;

use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Persistence\ObjectManager;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use UJM\ExoBundle\Entity\Attempt\Paper;
use UJM\ExoBundle\Entity\Exercise;
use UJM\ExoBundle\Event\Log\LogExerciseEvaluatedEvent;
use UJM\ExoBundle\Library\Mode\CorrectionMode;
use UJM\ExoBundle\Library\Mode\MarkMode;
use UJM\ExoBundle\Library\Options\Transfer;
use UJM\ExoBundle\Library\Validator\ValidationException;
use UJM\ExoBundle\Manager\Item\ItemManager;
use UJM\ExoBundle\Repository\PaperRepository;
use UJM\ExoBundle\Serializer\Attempt\PaperSerializer;

/**
 * @DI\Service("ujm_exo.manager.paper")
 */
class PaperManager
{
    /**
     * @var ObjectManager
     */
    private $om;

    /**
     * @var PaperRepository
     */
    private $repository;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @var PaperSerializer
     */
    private $serializer;

    /**
     * PaperManager constructor.
     *
     * @DI\InjectParams({
     *     "om"              = @DI\Inject("claroline.persistence.object_manager"),
     *     "eventDispatcher" = @DI\Inject("event_dispatcher"),
     *     "serializer"      = @DI\Inject("ujm_exo.serializer.paper"),
     *     "itemManager"     = @DI\Inject("ujm_exo.manager.item")
     * })
     *
     * @param ObjectManager            $om
     * @param EventDispatcherInterface $eventDispatcher
     * @param PaperSerializer          $serializer
     * @param ItemManager              $itemManager
     */
    public function __construct(
        ObjectManager $om,
        EventDispatcherInterface $eventDispatcher,
        PaperSerializer $serializer,
        ItemManager $itemManager)
    {
        $this->om = $om;
        $this->repository = $om->getRepository('UJMExoBundle:Attempt\Paper');
        $this->eventDispatcher = $eventDispatcher;
        $this->serializer = $serializer;
        $this->itemManager = $itemManager;
    }

    /**
     * Serializes a user paper.
     *
     * @param Paper $paper
     * @param array $options
     *
     * @return \stdClass
     */
    public function serialize(Paper $paper, array $options = [])
    {
        // Adds user score if available and the method options do not already request it
        if (!in_array(Transfer::INCLUDE_USER_SCORE, $options)
            && $this->isScoreAvailable($paper->getExercise(), $paper)) {
            $options[] = Transfer::INCLUDE_USER_SCORE;
        }

        return $this->serializer->serialize($paper, $options);
    }

    /**
     * Check if a Paper is full evaluated and dispatch a Log event if yes.
     *
     * @param Paper $paper
     *
     * @return bool
     */
    public function checkPaperEvaluated(Paper $paper)
    {
        $fullyEvaluated = $this->repository->isFullyEvaluated($paper);
        if ($fullyEvaluated) {
            $event = new LogExerciseEvaluatedEvent($paper->getExercise(), [
                'result' => $paper->getScore(),
                'resultMax' => $this->calculateTotal($paper),
            ]);

            $this->eventDispatcher->dispatch('log', $event);
        }

        return $fullyEvaluated;
    }

    /**
     * Calculates the score of a Paper.
     *
     * @param Paper $paper
     * @param float $base
     *
     * @return float
     */
    public function calculateScore(Paper $paper, $base = null)
    {
        $score = $this->repository->findScore($paper);
        if (!empty($base) && $base > 0) {
            $scoreTotal = $this->calculateTotal($paper);
            if ($scoreTotal !== $base) {
                $score = ($score / $scoreTotal) * $base;
            }
        }

        return $score;
    }

    /**
     * Calculates the total score of a Paper.
     *
     * @param Paper $paper
     *
     * @return float
     */
    public function calculateTotal(Paper $paper)
    {
        $total = 0;

        $structure = json_decode($paper->getStructure());
        foreach ($structure->steps as $step) {
            foreach ($step->items as $item) {
                if (1 === preg_match('#^application\/x\.[^/]+\+json$#', $item->type)) {
                    $total += $this->itemManager->calculateTotal($item);
                }
            }
        }

        return $total;
    }

    /**
     * Returns the papers for a given exercise, in a JSON format.
     *
     * @param Exercise $exercise
     * @param User     $user
     *
     * @return array
     */
    public function serializeExercisePapers(Exercise $exercise, User $user = null)
    {
        if (!empty($user)) {
            // Load papers for of a single user
            $papers = $this->repository->findBy([
                'exercise' => $exercise,
                'user' => $user,
            ]);
        } else {
            // Load all papers submitted for the exercise
            $papers = $this->repository->findBy([
                'exercise' => $exercise,
            ]);
        }

        return array_map(function (Paper $paper) {
            return $this->serialize($paper);
        }, $papers);
    }

    /**
     * Deletes all the papers associated with an exercise.
     *
     * @param Exercise $exercise
     *
     * @throws ValidationException if the exercise has been published at least once
     */
    public function deleteAll(Exercise $exercise)
    {
        if ($exercise->wasPublishedOnce()) {
            throw new ValidationException('Papers cannot be deleted', [[
                'path' => '',
                'message' => 'exercise has been published once.',
            ]]);
        }

        $papers = $this->repository->findBy([
            'exercise' => $exercise,
        ]);

        foreach ($papers as $paper) {
            $this->om->remove($paper);
        }

        $this->om->flush();
    }

    /**
     * Deletes a paper.
     *
     * @param Paper $paper
     *
     * @throws ValidationException if the exercise has been published at least once
     */
    public function delete(Paper $paper)
    {
        if ($paper->getExercise()->wasPublishedOnce()) {
            // Question is used, we can't delete it
            throw new ValidationException('Paper can not be deleted', [[
                'path' => '',
                'message' => 'exercise has been published once.',
            ]]);
        }

        $this->om->remove($paper);
        $this->om->flush();
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
        return $this->repository->countUserFinishedPapers($exercise, $user);
    }

    /**
     * Returns the number of papers already done for a given exercise.
     *
     * @param Exercise $exercise
     *
     * @return int
     */
    public function countExercisePapers(Exercise $exercise)
    {
        return $this->repository->countExercisePapers($exercise);
    }

    /**
     * Returns the number of different registered users that have passed a given exercise.
     *
     * @param Exercise $exercise
     *
     * @return int
     */
    public function countPapersUsers(Exercise $exercise)
    {
        return $this->repository->countPapersUsers($exercise);
    }

    /**
     * Returns the number of different anonymous users that have passed a given exercise.
     *
     * @param Exercise $exercise
     *
     * @return int
     */
    public function countAnonymousPapers(Exercise $exercise)
    {
        return $this->repository->countAnonymousPapers($exercise);
    }

    /**
     * Returns the max min and average score for a given exercise.
     *
     * @param Exercise $exercise
     * @param float    $scoreOn
     *
     * @return \stdClass
     */
    public function getMinMaxAverageScores(Exercise $exercise, $scoreOn)
    {
        $papers = $this->repository->findBy([
            'exercise' => $exercise,
        ]);

        $scores = $this->getPapersScores($papers, $scoreOn);

        $result = new \stdClass();
        $result->min = count($scores) === 0 ? 0 : min($scores);
        $result->max = count($scores) === 0 ? 0 : max($scores);
        $average = count($scores) === 0 ? 0 : array_sum($scores) / count($scores);
        $result->avg = $average !== floor($average) ? floatval(number_format($average, 2)) : $average;

        return $result;
    }

    /**
     * Returns the number of fully, partially successfull and missed papers for a given exercise.
     *
     * @param Exercise $exercise
     * @param float    $scoreOn
     *
     * @return \stdClass
     */
    public function getPapersSuccessDistribution(Exercise $exercise, $scoreOn)
    {
        $papers = $this->repository->findBy([
            'exercise' => $exercise,
        ]);

        $nbSuccess = 0;
        $nbMissed = 0;
        $nbPartialSuccess = 0;

        $scores = $this->getPapersScores($papers, $scoreOn);

        /* @var Paper $paper */
        foreach ($scores as $score) {
            if ($score === floatval(0)) {
                ++$nbMissed;
            } elseif ($score === floatval($scoreOn)) {
                ++$nbSuccess;
            } else {
                ++$nbPartialSuccess;
            }
        }

        $papersSuccessDistribution = new \stdClass();
        $papersSuccessDistribution->nbSuccess = $nbSuccess;
        $papersSuccessDistribution->nbMissed = $nbMissed;
        $papersSuccessDistribution->nbPartialSuccess = $nbPartialSuccess;

        return $papersSuccessDistribution;
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
                $available = 0 === $exercise->getMaxAttempts() || $paper->getNumber() === $exercise->getMaxAttempts();
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
}
