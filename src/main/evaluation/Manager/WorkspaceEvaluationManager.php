<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\EvaluationBundle\Manager;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Log\Connection\LogConnectWorkspace;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\Resource\ResourceUserEvaluation;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Evaluation;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\EvaluationBundle\Entity\AbstractEvaluation;
use Claroline\EvaluationBundle\Event\EvaluationEvents;
use Claroline\EvaluationBundle\Event\WorkspaceEvaluationEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class WorkspaceEvaluationManager extends AbstractEvaluationManager
{
    /** @var EventDispatcherInterface */
    private $eventDispatcher;
    /** @var ObjectManager */
    private $om;

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        ObjectManager $om
    ) {
        $this->eventDispatcher = $eventDispatcher;
        $this->om = $om;
    }

    /**
     * Retrieve or create evaluation for a workspace and an user.
     */
    public function getUserEvaluation(Workspace $workspace, User $user, ?bool $withCreation = true): ?Evaluation
    {
        $evaluation = $this->om->getRepository(Evaluation::class)->findOneBy([
            'workspace' => $workspace,
            'user' => $user,
        ]);

        if ($withCreation && empty($evaluation)) {
            $evaluation = new Evaluation();

            $evaluation->setWorkspace($workspace);
            $evaluation->setWorkspaceCode($workspace->getCode());
            $evaluation->setUser($user);
            $evaluation->setUserName($user->getFullName());
            $evaluation->setStatus(AbstractEvaluation::STATUS_NOT_ATTEMPTED);

            $this->om->persist($evaluation);
            $this->om->flush();

            // TODO : this should compute required resources
        }

        return $evaluation;
    }

    public function updateUserEvaluation(Workspace $workspace, User $user, ?array $data = [], ?\DateTime $date = null): Evaluation
    {
        $evaluation = $this->getUserEvaluation($workspace, $user);

        $this->updateEvaluation($evaluation, $data, $date);

        return $evaluation;
    }

    /**
     * @return ResourceNode[]
     */
    public function getRequiredResources(Workspace $workspace): array
    {
        return $this->om->getRepository(ResourceNode::class)->findBy([
            'required' => true,
            'published' => true,
            'active' => true,
            'workspace' => $workspace,
        ]);
    }

    /**
     * Compute evaluation status and progression of an user in a workspace.
     */
    public function computeEvaluation(Workspace $workspace, User $user, ResourceUserEvaluation $currentRue = null): Evaluation
    {
        $evaluation = $this->getUserEvaluation($workspace, $user);

        $resources = $this->getRequiredResources($workspace);
        if (empty($resources)) {
            // nothing to do if there is no required resources in the workspace
            return $evaluation;
        }

        $statusCount = [
            AbstractEvaluation::STATUS_PASSED => 0,
            AbstractEvaluation::STATUS_FAILED => 0,
            AbstractEvaluation::STATUS_COMPLETED => 0,
            AbstractEvaluation::STATUS_INCOMPLETE => 0,
            AbstractEvaluation::STATUS_NOT_ATTEMPTED => 0,
            AbstractEvaluation::STATUS_UNKNOWN => 0,
            AbstractEvaluation::STATUS_OPENED => 0,
            AbstractEvaluation::STATUS_PARTICIPATED => 0,
            AbstractEvaluation::STATUS_TODO => 0,
        ];

        $score = 0;
        $scoreMax = 0;
        $progressionMax = count($resources);

        // if there is a triggering resource evaluation checks if is part of the workspace requirements
        // if not, no evaluation is computed
        if ($currentRue) {
            $currentResourceId = $currentRue->getResourceNode()->getUuid();

            if (isset($resources[$currentResourceId])) {
                if ($currentRue->getStatus()) {
                    ++$statusCount[$currentRue->getStatus()];
                    $score += $currentRue->getScore() ?? 0;
                    $scoreMax += $currentRue->getScoreMax() ?? 0;
                }
                unset($resources[$currentResourceId]);
            }
        }

        foreach ($resources as $resource) {
            $resourceEval = $this->om->getRepository(ResourceUserEvaluation::class)->findOneBy([
                'resourceNode' => $resource,
                'user' => $user,
            ]);

            if ($resourceEval && $resourceEval->getStatus()) {
                ++$statusCount[$resourceEval->getStatus()];
                $score += $resourceEval->getScore() ?? 0;
                $scoreMax += $resourceEval->getScoreMax() ?? 0;
            }
        }

        $progression = $statusCount[AbstractEvaluation::STATUS_PASSED] +
            $statusCount[AbstractEvaluation::STATUS_FAILED] +
            $statusCount[AbstractEvaluation::STATUS_COMPLETED] +
            $statusCount[AbstractEvaluation::STATUS_PARTICIPATED];

        $status = $evaluation->getStatus();
        if ($progression >= $progressionMax) {
            if (0 !== $statusCount[AbstractEvaluation::STATUS_FAILED]) {
                // if there is one failed resource the workspace is considered as failed also
                $status = AbstractEvaluation::STATUS_FAILED;
            } else {
                // if all resources have been done without failure the workspace is completed
                $status = AbstractEvaluation::STATUS_PASSED;
            }
        } elseif ((0 !== $progression && $progression < $progressionMax) || 0 < $statusCount[AbstractEvaluation::STATUS_INCOMPLETE]) {
            $status = AbstractEvaluation::STATUS_INCOMPLETE;
        }

        $evaluationData = [
            'status' => $status,
            'progression' => $progression,
            'progressionMax' => $progressionMax,
        ];

        // recompute workspace evaluation if all resources
        if ($scoreMax && $progression >= $progressionMax) {
            $evaluationData['score'] = $score;
            $evaluationData['scoreMax'] = $scoreMax;
        }

        $this->updateEvaluation($evaluation, $evaluationData, $currentRue ? $currentRue->getDate() : null);

        $this->om->persist($evaluation);
        $this->om->flush();

        $this->eventDispatcher->dispatch(new WorkspaceEvaluationEvent($evaluation), EvaluationEvents::WORKSPACE);

        return $evaluation;
    }

    /**
     * Add duration to a workspace user evaluation.
     */
    public function addDurationToWorkspaceEvaluation(Workspace $workspace, User $user, int $duration)
    {
        $this->om->startFlushSuite();

        $workspaceEval = $this->getUserEvaluation($workspace, $user);

        $evaluationDuration = $workspaceEval->getDuration();
        if (is_null($workspaceEval->getDuration())) {
            $evaluationDuration = $this->computeDuration($workspaceEval);
        }

        $workspaceEval->setDuration($evaluationDuration + $duration);

        $this->om->persist($workspaceEval);
        $this->om->flush();

        $this->om->endFlushSuite();
    }

    /**
     * Compute duration for a workspace user evaluation.
     */
    public function computeDuration(Evaluation $workspaceEvaluation): int
    {
        /** @var LogConnectWorkspace[] $workspaceLogs */
        $workspaceLogs = $this->om->getRepository(LogConnectWorkspace::class)->findBy([
            'workspace' => $workspaceEvaluation->getWorkspace(),
            'user' => $workspaceEvaluation->getUser(),
        ]);

        $duration = 0;
        foreach ($workspaceLogs as $log) {
            if ($log->getDuration()) {
                $duration += $log->getDuration();
            }
        }

        $workspaceEvaluation->setDuration($duration);

        $this->om->persist($workspaceEvaluation);
        $this->om->flush();

        return $duration;
    }
}
