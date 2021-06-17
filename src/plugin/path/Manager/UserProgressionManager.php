<?php

namespace Innova\PathBundle\Manager;

use Claroline\CoreBundle\Entity\Resource\ResourceEvaluation;
use Claroline\CoreBundle\Entity\Resource\ResourceUserEvaluation;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Manager\Resource\ResourceEvaluationManager;
use Claroline\CoreBundle\Repository\Resource\ResourceEvaluationRepository;
use Claroline\EvaluationBundle\Entity\Evaluation\AbstractEvaluation;
use Doctrine\Persistence\ObjectManager;
use Innova\PathBundle\Entity\Path\Path;
use Innova\PathBundle\Entity\Step;
use Innova\PathBundle\Entity\UserProgression;

class UserProgressionManager
{
    /** @var ObjectManager */
    private $om;

    /** @var ResourceEvaluationManager */
    private $resourceEvalManager;

    private $progressionRepo;
    private $stepRepo;
    private $resourceUserEvalRepo;
    /** @var ResourceEvaluationRepository */
    private $resourceEvalRepo;

    public function __construct(
        ObjectManager $om,
        ResourceEvaluationManager $resourceEvalManager
    ) {
        $this->om = $om;
        $this->resourceEvalManager = $resourceEvalManager;

        $this->progressionRepo = $this->om->getRepository(UserProgression::class);
        $this->stepRepo = $this->om->getRepository(Step::class);
        $this->resourceUserEvalRepo = $this->om->getRepository(ResourceUserEvaluation::class);
        $this->resourceEvalRepo = $this->om->getRepository(ResourceEvaluation::class);
    }

    /**
     * Create a new progression for a User and a Step (by default, the first action is 'seen').
     */
    public function create(Step $step, User $user = null, string $status = null, bool $checkDuplicate = true): UserProgression
    {
        // Check if progression already exists, if so return retrieved progression
        if ($checkDuplicate && $user instanceof User) {
            /** @var UserProgression $progression */
            $progression = $this->progressionRepo->findOneBy([
                'step' => $step,
                'user' => $user,
            ]);

            if (!empty($progression)) {
                return $progression;
            }
        }

        $progression = new UserProgression();

        $progression->setStep($step);

        if (empty($status)) {
            $status = UserProgression::getDefaultStatus();
        }

        $progression->setStatus($status);

        if ($user instanceof User) {
            $progression->setUser($user);
            $this->om->persist($progression);
            $this->om->flush();
        }

        return $progression;
    }

    public function update(Step $step, User $user = null, string $status)
    {
        // Retrieve the current progression for this step
        $progression = $this->progressionRepo->findOneBy([
            'step' => $step,
            'user' => $user,
        ]);

        if (empty($progression)) {
            // No progression for User => initialize a new one
            $progression = $this->create($step, $user, $status, false);
        } else {
            // Update existing progression
            $progression->setStatus($status);
            if ($user instanceof User) {
                $this->om->persist($progression);
                $this->om->flush();
            }
        }

        $this->updateResourceEvaluation($step, $user, $status);

        return $progression;
    }

    /**
     * Fetch or create resource user evaluation.
     */
    public function getResourceUserEvaluation(Path $path, User $user): ?ResourceUserEvaluation
    {
        return $this->resourceEvalManager->getResourceUserEvaluation($path->getResourceNode(), $user);
    }

    public function getCurrentAttempt(Path $path, $user)
    {
        $pathAttempt = $this->resourceEvalRepo->findLast($path->getResourceNode(), $user);
        if (empty($pathAttempt)) {
            $pathAttempt = $this->resourceEvalManager->createResourceEvaluation($path->getResourceNode(), $user);
        }

        return $pathAttempt;
    }

    /**
     * Get all steps progression for an user.
     */
    public function getStepsProgressionForUser(Path $path, User $user): array
    {
        $stepsProgression = [];

        foreach ($path->getSteps() as $step) {
            $userProgression = $this->progressionRepo->findOneBy(['step' => $step, 'user' => $user]);

            if ($userProgression) {
                $stepsProgression[$step->getUuid()] = $userProgression->getStatus();
            }
        }

        return $stepsProgression;
    }

    /**
     * Fetches and updates score for all paths linked to the evaluation.
     */
    public function handleResourceEvaluation(ResourceUserEvaluation $resourceUserEvaluation, ResourceEvaluation $resourceAttempt)
    {
        // only update paths evaluations if the current attempt is fully evaluated
        if (!is_null($resourceAttempt->getScore()) && !is_null($resourceAttempt->getScoreMax())) {
            $resourceNode = $resourceUserEvaluation->getResourceNode();
            $user = $resourceUserEvaluation->getUser();

            // Gets all steps containing the resource node
            /** @var Step[] $steps */
            $steps = $this->stepRepo->findBy(['resource' => $resourceNode, 'evaluated' => true]);

            $pathAttempts = [];
            foreach ($steps as $step) {
                // get the current attempt of the path
                if (!empty($pathAttempts[$step->getPath()->getUuid()])) {
                    $pathAttempt = $pathAttempts[$step->getPath()->getUuid()];
                } else {
                    $pathAttempt = $this->resourceEvalRepo->findLast($step->getPath()->getResourceNode(), $user);
                }

                if ($pathAttempt) {
                    // only update the path attempt if there is no evaluation for this resource yet
                    $attemptData = $pathAttempt->getData();
                    if (empty($attemptData['resources'])) {
                        $attemptData['resources'] = [];
                    }

                    $attemptData['resources'][$step->getUuid()] = [
                        'id' => $resourceAttempt->getId(),
                        'score' => $resourceAttempt->getScore(),
                        'max' => $resourceAttempt->getScoreMax(),
                    ];

                    // recompute path attempt score
                    $data = array_merge(['data' => $attemptData], $this->computeScore($step->getPath(), $attemptData['resources']));

                    // forward update to core to let him recompute the ResourceUserEvaluation if needed
                    $this->resourceEvalManager->updateResourceEvaluation($pathAttempt, $resourceAttempt->getDate(), $data, false, false);
                }
            }
        }
    }

    private function updateResourceEvaluation(Step $step, User $user, string $status): ResourceEvaluation
    {
        $evaluation = $this->resourceEvalRepo->findLast($step->getPath()->getResourceNode(), $user);

        $data = ['done' => []];
        if ($evaluation) {
            $data = array_merge($data, $evaluation->getData() ?? []);
        }

        if (!in_array($step->getUuid(), $data['done']) && in_array($status, ['seen', 'done'])) {
            // mark the step as done if it has the correct status
            $data['done'][] = $step->getUuid();
        } elseif (in_array($step->getUuid(), $data['done']) && !in_array($status, ['seen', 'done'])) {
            // mark the step as not done
            array_splice($data['done'], array_search($step->getUuid(), $data['done']), 1);
        }

        $statusData = array_merge(['data' => $data], $this->computeProgression($step->getPath(), $data));

        if ($evaluation) {
            return $this->resourceEvalManager->updateResourceEvaluation($evaluation, null, $statusData, false, false);
        }

        return $this->resourceEvalManager->createResourceEvaluation(
            $step->getPath()->getResourceNode(),
            $user,
            null,
            $statusData
        );
    }

    /**
     * Computes score for path and updates user evaluation.
     */
    private function computeScore(Path $path, array $resourceScores = [])
    {
        $toEvaluate = count(array_filter($path->getSteps()->toArray(), function (Step $step) {
            return $step->isEvaluated();
        }));

        if (!empty($resourceScores)) {
            // get the scores of all embedded resources for this attempt to compute path score
            $evaluatedSteps = 0;
            $score = 0;
            $scoreTotal = 0;
            foreach ($resourceScores as $resourceScore) {
                ++$evaluatedSteps;
                $score += $resourceScore['score'];
                $scoreTotal += $resourceScore['max'];
            }

            if (0 < $scoreTotal) {
                $finalScore = ($score * $path->getScoreTotal()) / $scoreTotal;
                $finished = $toEvaluate === $evaluatedSteps;
                $success = $finished && $path->getSuccessScore() <= ($score * 100) / $scoreTotal;

                $status = null;
                if ($finished) {
                    // change the attempt status if all steps have been evaluated
                    $status = $success ? AbstractEvaluation::STATUS_PASSED : AbstractEvaluation::STATUS_FAILED;
                }

                return [
                    'status' => $status,
                    'score' => $finalScore,
                    'scoreMax' => $path->getScoreTotal(),
                ];
            }
        }

        // no evaluation data for embedded resources => nothing to recompute
        return [];
    }

    /**
     * Compute current resource evaluation status.
     */
    private function computeProgression(Path $path, array $data = []): array
    {
        $steps = array_map(function (Step $step) {
            return $step->getUuid();
        }, $path->getSteps()->toArray());

        $progression = 0;
        $progressionMax = count($steps);

        $status = AbstractEvaluation::STATUS_OPENED;
        // only compute progression if path is not empty
        if ($progressionMax) {
            $rest = array_diff($steps, $data['done'] ?? []);

            $progression = $progressionMax - count($rest);

            if ($progression >= $progressionMax) {
                $status = AbstractEvaluation::STATUS_COMPLETED;
            } else {
                $status = AbstractEvaluation::STATUS_INCOMPLETE;
            }
        }

        return [
            'progression' => $progression,
            'progressionMax' => $progressionMax,
            'status' => $status,
        ];
    }
}
