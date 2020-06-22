<?php

namespace Innova\PathBundle\Manager;

use Claroline\CoreBundle\Entity\Evaluation\AbstractEvaluation;
use Claroline\CoreBundle\Entity\Resource\ResourceEvaluation;
use Claroline\CoreBundle\Entity\Resource\ResourceUserEvaluation;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Manager\Resource\ResourceEvaluationManager;
use Claroline\CoreBundle\Repository\Resource\ResourceEvaluationRepository;
use Doctrine\Common\Persistence\ObjectManager;
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

    /**
     * UserProgressionManager constructor.
     *
     * @param ObjectManager             $om
     * @param ResourceEvaluationManager $resourceEvalManager
     */
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
     *
     * @param Step   $step
     * @param User   $user
     * @param string $status
     * @param bool   $authorized
     * @param bool   $checkDuplicate
     *
     * @return UserProgression
     */
    public function create(Step $step, User $user = null, $status = null, $authorized = false, $checkDuplicate = true)
    {
        // Check if progression already exists, if so return retrieved progression
        if ($checkDuplicate && $user instanceof User) {
            /** @var UserProgression $progression */
            $progression = $this->om->getRepository('InnovaPathBundle:UserProgression')->findOneBy([
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
        $progression->setAuthorized($authorized);

        if ($user instanceof User) {
            $progression->setUser($user);
            $this->om->persist($progression);
            $this->om->flush();
        }

        return $progression;
    }

    public function update(Step $step, User $user = null, $status, $authorized = false)
    {
        // Retrieve the current progression for this step
        $progression = $this->om->getRepository('InnovaPathBundle:UserProgression')->findOneBy([
            'step' => $step,
            'user' => $user,
        ]);

        if (empty($progression)) {
            // No progression for User => initialize a new one
            $progression = $this->create($step, $user, $status, $authorized, false);
        } else {
            // Update existing progression
            $progression->setStatus($status);
            $progression->setAuthorized($authorized);
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
     *
     * @param Path $path
     * @param User $user
     *
     * @return ResourceUserEvaluation
     */
    public function getResourceUserEvaluation(Path $path, User $user)
    {
        return $this->resourceEvalManager->getResourceUserEvaluation($path->getResourceNode(), $user);
    }

    /**
     * Create a ResourceEvaluation for a step.
     *
     * @param Step   $step
     * @param User   $user
     * @param string $status
     *
     * @return ResourceEvaluation
     */
    public function updateResourceEvaluation(Step $step, User $user, $status)
    {
        $evaluation = $this->resourceEvalRepo->findOneInProgress($step->getPath()->getResourceNode(), $user);

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

        $statusData = $this->computeResourceUserEvaluation($step->getPath(), $data);

        $evaluationData = [
            'status' => $statusData['status'],
            'progression' => $statusData['progression'],
            'progressionMax' => $statusData['progressionMax'],
            'data' => $data,
        ];

        if ($evaluation) {
            return $this->resourceEvalManager->updateResourceEvaluation($evaluation, null, $evaluationData, false, false);
        }

        return $this->resourceEvalManager->createResourceEvaluation(
            $step->getPath()->getResourceNode(),
            $user,
            null,
            $evaluationData
        );
    }

    /**
     * Compute current resource evaluation status.
     *
     * @param Path  $path
     * @param array $data
     *
     * @return array
     */
    public function computeResourceUserEvaluation(Path $path, array $data = [])
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

    /**
     * Get all steps progression for an user.
     *
     * @param Path $path
     * @param User $user
     *
     * @return array
     */
    public function getStepsProgressionForUser(Path $path, User $user)
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
     *
     * @param ResourceUserEvaluation $resourceUserEvaluation
     */
    public function handleResourceEvaluation(ResourceUserEvaluation $resourceUserEvaluation)
    {
        $resourceNode = $resourceUserEvaluation->getResourceNode();
        $user = $resourceUserEvaluation->getUser();

        // Gets all steps containing the resource node
        $steps = $this->stepRepo->findBy(['resource' => $resourceNode, 'evaluated' => true]);

        // Retrieves corresponding paths
        $paths = [];
        foreach ($steps as $step) {
            $paths[$step->getPath()->getUuid()] = $step->getPath();
        }

        // Gets all ResourceUserEvaluation entities linked to each path and user
        foreach ($paths as $path) {
            /** @var ResourceUserEvaluation $userEval */
            $userEval = $this->resourceUserEvalRepo->findOneBy([
                'user' => $user,
                'resourceNode' => $path->getResourceNode(),
            ]);

            if ($userEval) {
                // Updates score of each path having an evaluation for user
                $this->computeUserPathScore($path, $userEval);
            }
        }
    }

    /**
     * Computes score for path and updates user evaluation.
     *
     * @param Path                   $path
     * @param ResourceUserEvaluation $resourceUserEvaluation
     */
    public function computeUserPathScore(Path $path, ResourceUserEvaluation $resourceUserEvaluation)
    {
        $user = $resourceUserEvaluation->getUser();
        $total = $path->getScoreTotal();
        $successScore = $path->getSuccessScore();
        $nbEvaluatedStepsDone = 0;
        $score = 0;
        $scoreTotal = 0;
        $evaluatedSteps = [];

        foreach ($path->getSteps() as $step) {
            if ($step->isEvaluated()) {
                $evaluatedSteps[] = $step;
            }
        }

        if (0 < count($evaluatedSteps)) {
            foreach ($evaluatedSteps as $step) {
                $stepResource = $step->getResource();
                $stepEval = $this->resourceUserEvalRepo->findOneBy([
                    'user' => $user,
                    'resourceNode' => $stepResource,
                ]);

                if ($stepEval && !is_null($stepEval->getScore()) && !is_null($stepEval->getScoreMax())) {
                    ++$nbEvaluatedStepsDone;
                    $score += $stepEval->getScore();
                    $scoreTotal += $stepEval->getScoreMax();
                } else {
                    // TODO: retrieve total score of resource
                }
            }

            if (0 < $scoreTotal) {
                $finalScore = ($score * $total) / $scoreTotal;
                $finished = count($evaluatedSteps) === $nbEvaluatedStepsDone;
                $success = $finished && $successScore <= ($score * 100) / $scoreTotal;

                if (AbstractEvaluation::STATUS_PASSED !== $resourceUserEvaluation->getStatus()) {
                    $resourceUserEvaluation->setDate(new \DateTime());
                    $resourceUserEvaluation->setScore($finalScore);
                    $resourceUserEvaluation->setScoreMax($total);

                    if ($finished) {
                        $status = $success ? AbstractEvaluation::STATUS_PASSED : AbstractEvaluation::STATUS_FAILED;
                        $resourceUserEvaluation->setStatus($status);
                    }
                }
                $this->om->persist($resourceUserEvaluation);
                $this->om->flush();
            }
        }
    }
}
