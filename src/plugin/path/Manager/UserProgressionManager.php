<?php

namespace Innova\PathBundle\Manager;

use Claroline\CoreBundle\Entity\Resource\ResourceEvaluation;
use Claroline\CoreBundle\Entity\Resource\ResourceUserEvaluation;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Repository\Resource\ResourceEvaluationRepository;
use Claroline\EvaluationBundle\Entity\AbstractEvaluation;
use Claroline\EvaluationBundle\Manager\ResourceEvaluationManager;
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
        $this->resourceEvalRepo = $this->om->getRepository(ResourceEvaluation::class);
    }

    public function update(Step $step, User $user, string $status): UserProgression
    {
        // Retrieve the current progression for this step
        $progression = $this->progressionRepo->findOneBy([
            'step' => $step,
            'user' => $user,
        ]);

        if (empty($progression)) {
            // No progression for User => initialize a new one
            $progression = new UserProgression();
            $progression->setStep($step);
            $progression->setUser($user);
        }

        $progression->setStatus($status);

        $this->om->persist($progression);
        $this->om->flush();

        $this->updateResourceEvaluation($step, $user, $status);

        return $progression;
    }

    /**
     * Fetch or create resource user evaluation.
     */
    public function getResourceUserEvaluation(Path $path, User $user): ResourceUserEvaluation
    {
        return $this->resourceEvalManager->getUserEvaluation($path->getResourceNode(), $user);
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

            foreach ($steps as $step) {
                // get the current attempt of the path
                $pathAttempt = $this->resourceEvalRepo->findLast($step->getPath()->getResourceNode(), $user);
                if ($pathAttempt) {
                    // will be used to know if we need to recompute the path ResourceEvaluation
                    // the path attempt will be recomputed if a linked resource is terminated or gets a new/better score
                    $updateEvaluation = false;

                    $attemptData = $pathAttempt->getData();
                    if (empty($attemptData['resources'])) {
                        $attemptData['resources'] = [];
                    }

                    $resourceData = [
                        'id' => $resourceAttempt->getId(),
                        'terminated' => false,
                    ];

                    if (!empty($attemptData['resources'][$step->getUuid()])) {
                        $resourceData = array_merge($resourceData, $attemptData['resources'][$step->getUuid()]);
                    }

                    // check if the status of the resource has changed
                    if (!$resourceData['terminated'] && $resourceAttempt->isTerminated()) {
                        $resourceData['terminated'] = true;

                        $updateEvaluation = true;
                    }

                    // check if the score of the resource has changed
                    $oldScore = null;
                    if (isset($resourceData['score']) && !is_null($resourceData['score'])) {
                        $oldScore = $resourceData['score'] / $resourceData['max'];
                    }

                    if (empty($oldScore)
                        || (!is_null($resourceAttempt->getScore()) && $resourceAttempt->getScore() / $resourceAttempt->getScoreMax() >= $oldScore)
                    ) {
                        // only update path attempt if it's the first time the user do the resource
                        // or if he gets a better score
                        $resourceData['score'] = $resourceAttempt->getScore();
                        $resourceData['max'] = $resourceAttempt->getScoreMax();

                        $updateEvaluation = true;
                    }

                    if ($updateEvaluation) {
                        $attemptData['resources'][$step->getUuid()] = $resourceData;

                        // recompute path attempt progression
                        $progressionData = $this->computeProgression($step->getPath(), $attemptData);
                        // recompute path attempt score if the path is finished
                        $scoreData = [];
                        if (AbstractEvaluation::STATUS_COMPLETED === $progressionData['status']) {
                            $scoreData = $this->computeScore($step->getPath(), $attemptData['resources']);
                        }

                        $data = array_merge(['data' => $attemptData], $progressionData, $scoreData);

                        // forward update to core to let him recompute the ResourceUserEvaluation if needed
                        $this->resourceEvalManager->updateResourceEvaluation($pathAttempt, $data, $resourceAttempt->getDate());
                    }
                }
            }
        }
    }

    private function updateResourceEvaluation(Step $step, User $user, string $status): ResourceEvaluation
    {
        $evaluation = $this->resourceEvalRepo->findLast($step->getPath()->getResourceNode(), $user);

        $data = [
            'done' => [],
            'resources' => [],
        ];
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

        // recompute path attempt progression
        $progressionData = $this->computeProgression($step->getPath(), $data);
        // recompute path attempt score if the path is finished
        $scoreData = [];
        if (AbstractEvaluation::STATUS_COMPLETED === $progressionData['status']) {
            $scoreData = $this->computeScore($step->getPath(), $data['resources']);
        }

        $statusData = array_merge(['data' => $data], $progressionData, $scoreData);

        if ($evaluation) {
            return $this->resourceEvalManager->updateResourceEvaluation($evaluation, $statusData);
        }

        return $this->resourceEvalManager->createResourceEvaluation(
            $step->getPath()->getResourceNode(),
            $user,
            $statusData
        );
    }

    /**
     * Computes score for path and updates user evaluation.
     */
    private function computeScore(Path $path, ?array $resourceScores = [])
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
        $progression = 0;
        $progressionMax = count($path->getSteps());

        $status = AbstractEvaluation::STATUS_OPENED;
        // only compute progression if path is not empty
        if ($progressionMax) {
            $resourcesData = [];
            if (!empty($data['resources'])) {
                $resourcesData = $data['resources'];
            }

            foreach ($path->getSteps() as $step) {
                if (!$step->isEvaluated() && in_array($step->getUuid(), $data['done'])) {
                    ++$progression;
                }

                if ($step->isEvaluated() && !empty($resourcesData[$step->getUuid()]) && $resourcesData[$step->getUuid()]['terminated']) {
                    ++$progression;
                }
            }

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
