<?php

namespace Innova\PathBundle\Manager;

use Claroline\CoreBundle\Entity\AbstractEvaluation;
use Claroline\CoreBundle\Entity\Resource\ResourceEvaluation;
use Claroline\CoreBundle\Entity\Resource\ResourceUserEvaluation;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Manager\Resource\ResourceEvaluationManager;
use Doctrine\Common\Persistence\ObjectManager;
use Innova\PathBundle\Entity\Path\Path;
use Innova\PathBundle\Entity\Step;
use Innova\PathBundle\Entity\UserProgression;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class UserProgressionManager
{
    /** @var ObjectManager */
    private $om;

    /** @var TokenStorageInterface */
    private $tokenStorage;

    /** @var ResourceEvaluationManager */
    private $resourceEvalManager;

    private $progressionRepo;
    private $stepRepo;
    private $resourceUserEvalRepo;

    /**
     * UserProgressionManager constructor.
     *
     * @param ObjectManager             $om
     * @param TokenStorageInterface     $tokenStorage
     * @param ResourceEvaluationManager $resourceEvalManager
     */
    public function __construct(
        ObjectManager $om,
        TokenStorageInterface $tokenStorage,
        ResourceEvaluationManager $resourceEvalManager
    ) {
        $this->om = $om;
        $this->tokenStorage = $tokenStorage;
        $this->resourceEvalManager = $resourceEvalManager;

        $this->progressionRepo = $this->om->getRepository(UserProgression::class);
        $this->stepRepo = $this->om->getRepository(Step::class);
        $this->resourceUserEvalRepo = $this->om->getRepository(ResourceUserEvaluation::class);
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

        return $progression;
    }

    /**
     * Fetch resource user evaluation with up-to-date status.
     *
     * @param Path $path
     *
     * @return ResourceUserEvaluation
     */
    public function getUpdatedResourceUserEvaluation(Path $path)
    {
        $resourceUserEvaluation = null;

        $user = $this->tokenStorage->getToken()->getUser();
        if ($user instanceof User) {
            $resourceUserEvaluation = $this->resourceEvalManager->getResourceUserEvaluation($path->getResourceNode(), $user);
            $data = $this->computeResourceUserEvaluation($path, $user);

            if ($data['progression'] !== $resourceUserEvaluation->getProgression() ||
                $data['progressionMax'] !== $resourceUserEvaluation->getProgressionMax() ||
                $data['status'] !== $resourceUserEvaluation->getStatus()
            ) {
                $resourceUserEvaluation->setProgression($data['progression']);
                $resourceUserEvaluation->setProgressionMax($data['progressionMax']);
                $resourceUserEvaluation->setStatus($data['status']);
                $resourceUserEvaluation->setDate(new \DateTime());
                $this->om->persist($resourceUserEvaluation);
                $this->om->flush();
            }
        }

        return $resourceUserEvaluation;
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
    public function generateResourceEvaluation(Step $step, User $user, $status)
    {
        $statusData = $this->computeResourceUserEvaluation($step->getPath(), $user);
        $stepIndex = array_search($step->getUuid(), $statusData['stepsToDo']);

        if (false !== $stepIndex && false !== array_search($status, ['seen', 'done'])) {
            ++$statusData['progression'];
            array_splice($statusData['stepsToDo'], $stepIndex, 1);
        }

        $evaluationData = [
            'step' => $step->getUuid(),
            'status' => $status,
            'toDo' => $statusData['stepsToDo'],
        ];

        return $this->resourceEvalManager->createResourceEvaluation(
            $step->getPath()->getResourceNode(),
            $user,
            null,
            [
                'status' => $statusData['status'],
//                'score' => $statusData['score'],
//                'scoreMax' => $statusData['scoreMax'],
                'progression' => $statusData['progression'],
                'progressionMax' => $statusData['progressionMax'],
                'data' => $evaluationData,
            ],
            [
                'status' => true,
                'score' => true,
                'progression' => true,
            ]
        );
    }

    /**
     * Compute current resource evaluation status.
     *
     * @param Path $path
     * @param User $user
     *
     * @return array
     */
    public function computeResourceUserEvaluation(Path $path, User $user)
    {
        $steps = $path->getSteps()->toArray();
        $stepsUuids = array_map(function (Step $step) {
            return $step->getUuid();
        }, $steps);
        $resourceUserEval = $this->resourceEvalManager->getResourceUserEvaluation($path->getResourceNode(), $user);
        $evaluations = $resourceUserEval->getEvaluations();
        $progression = 0;
        $progressionMax = count($steps);

        /** @var ResourceEvaluation $evaluation */
        foreach ($evaluations as $evaluation) {
            $data = $evaluation->getData();

            if (isset($data['step']) && isset($data['status'])) {
                $statusIndex = array_search($data['status'], ['seen', 'done']);
                $uuidIndex = array_search($data['step'], $stepsUuids);

                if (false !== $statusIndex && false !== $uuidIndex) {
                    ++$progression;
                    array_splice($stepsUuids, $uuidIndex, 1);
                }
            }
        }
        $status = $progression >= $progressionMax ?
            AbstractEvaluation::STATUS_COMPLETED :
            AbstractEvaluation::STATUS_INCOMPLETE;

        return [
            'progression' => $progression,
            'progressionMax' => $progressionMax,
            'status' => $status,
            'stepsToDo' => $stepsUuids,
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
     * @param ResourceEvaluation $evaluation
     */
    public function handleResourceEvaluation(ResourceEvaluation $evaluation)
    {
        $resourceUserEvaluation = $evaluation->getResourceUserEvaluation();
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
