<?php

namespace Innova\PathBundle\Manager;

use Claroline\CoreBundle\Entity\Resource\AbstractResourceEvaluation;
use Claroline\CoreBundle\Entity\Resource\ResourceEvaluation;
use Claroline\CoreBundle\Entity\Resource\ResourceUserEvaluation;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Manager\Resource\ResourceEvaluationManager;
use Doctrine\Common\Persistence\ObjectManager;
use Innova\PathBundle\Entity\Path\Path;
use Innova\PathBundle\Entity\Step;
use Innova\PathBundle\Entity\UserProgression;
use Innova\PathBundle\Repository\UserProgressionRepository;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * @DI\Service("innova_path.manager.user_progression")
 */
class UserProgressionManager
{
    /** @var ObjectManager */
    private $om;

    /** @var UserProgressionRepository */
    private $repository;

    /** @var TokenStorageInterface */
    private $tokenStorage;

    /** @var ResourceEvaluationManager */
    private $resourceEvalManager;

    /**
     * UserProgressionManager constructor.
     *
     * @DI\InjectParams({
     *     "om"                  = @DI\Inject("claroline.persistence.object_manager"),
     *     "tokenStorage"        = @DI\Inject("security.token_storage"),
     *     "resourceEvalManager" = @DI\Inject("claroline.manager.resource_evaluation_manager")
     * })
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
        $this->repository = $this->om->getRepository('InnovaPathBundle:UserProgression');
        $this->tokenStorage = $tokenStorage;
        $this->resourceEvalManager = $resourceEvalManager;
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
     * @param string|User $user
     * @param array       $paths
     *
     * @return int
     */
    public function calculateUserProgression($user, array $paths)
    {
        if ($user instanceof User) {
            return $this->repository->findUserProgression($user, $paths);
        }

        return 0;
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

            if ($data['score'] !== $resourceUserEvaluation->getScore() ||
                $data['scoreMax'] !== $resourceUserEvaluation->getScoreMax() ||
                $data['status'] !== $resourceUserEvaluation->getStatus()
            ) {
                $resourceUserEvaluation->setScore($data['score']);
                $resourceUserEvaluation->setScoreMax($data['scoreMax']);
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
            ++$statusData['score'];
            array_splice($statusData['stepsToDo'], $stepIndex, 1);
        }

        $evaluationData = [
            'step' => $step->getUuid(),
            'status' => $status,
            'toDo' => $statusData['stepsToDo'],
        ];
        $progression = !is_null($statusData['score']) && $statusData['scoreMax'] > 0 ?
            floor(($statusData['score'] / $statusData['scoreMax']) * 100) :
            null;

        return $this->resourceEvalManager->createResourceEvaluation(
            $step->getPath()->getResourceNode(),
            $user,
            new \DateTime(),
            $statusData['status'],
            $statusData['score'],
            null,
            $statusData['scoreMax'],
            null,
            $progression,
            null,
            null,
            $evaluationData,
            true
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
        $score = 0;
        $scoreMax = count($steps);

        /** @var ResourceEvaluation $evaluation */
        foreach ($evaluations as $evaluation) {
            $data = $evaluation->getData();

            if (isset($data['step']) && isset($data['status'])) {
                $statusIndex = array_search($data['status'], ['seen', 'done']);
                $uuidIndex = array_search($data['step'], $stepsUuids);

                if (false !== $statusIndex && false !== $uuidIndex) {
                    ++$score;
                    array_splice($stepsUuids, $uuidIndex, 1);
                }
            }
        }
        $status = $score >= $scoreMax ?
            AbstractResourceEvaluation::STATUS_COMPLETED :
            AbstractResourceEvaluation::STATUS_INCOMPLETE;

        return [
            'score' => $score,
            'scoreMax' => $scoreMax,
            'status' => $status,
            'stepsToDo' => $stepsUuids,
        ];
    }
}
