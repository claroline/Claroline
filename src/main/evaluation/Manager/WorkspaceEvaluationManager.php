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
use Claroline\EvaluationBundle\Messenger\Message\InitializeWorkspaceEvaluations;
use Claroline\EvaluationBundle\Messenger\Message\RecomputeWorkspaceEvaluations;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class WorkspaceEvaluationManager extends AbstractEvaluationManager
{
    /** @var MessageBusInterface */
    private $messageBus;
    /** @var EventDispatcherInterface */
    private $eventDispatcher;
    /** @var ObjectManager */
    private $om;

    public function __construct(
        MessageBusInterface $messageBus,
        EventDispatcherInterface $eventDispatcher,
        ObjectManager $om
    ) {
        $this->messageBus = $messageBus;
        $this->eventDispatcher = $eventDispatcher;
        $this->om = $om;
    }

    /**
     * Recomputes all the evaluations of a workspace.
     * This is called when required resources are added/removed in order to update the users progression and score.
     */
    public function recompute(Workspace $workspace): void
    {
        $users = $this->om->getRepository(User::class)->findByWorkspaces([$workspace]);
        if (!empty($users)) {
            $this->messageBus->dispatch(
                new RecomputeWorkspaceEvaluations($workspace->getId(), array_map(function (User $user) {
                    return $user->getId();
                }, $users))
            );
        }
    }

    /**
     * Initializes missing evaluations for a workspace.
     */
    public function initialize(Workspace $workspace): void
    {
        $users = $this->om->getRepository(User::class)->findByWorkspaces([$workspace]);
        if (!empty($users)) {
            $this->messageBus->dispatch(
                new InitializeWorkspaceEvaluations($workspace->getId(), array_map(function (User $user) {
                    return $user->getId();
                }, $users))
            );
        }
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
            $evaluation->setUser($user);
            $evaluation->setStatus(AbstractEvaluation::STATUS_NOT_ATTEMPTED);

            $this->om->persist($evaluation);
            $this->om->flush();
        }

        return $evaluation;
    }

    public function updateUserEvaluation(Workspace $workspace, User $user, ?array $data = [], ?\DateTimeInterface $date = null): Evaluation
    {
        $this->om->startFlushSuite();

        $evaluation = $this->getUserEvaluation($workspace, $user);
        $hasChanged = $this->updateEvaluation($evaluation, $data, $date);

        $this->om->endFlushSuite();

        if ($hasChanged['status'] || $hasChanged['progression'] || $hasChanged['score']) {
            $this->eventDispatcher->dispatch(new WorkspaceEvaluationEvent($evaluation, $hasChanged), EvaluationEvents::WORKSPACE_EVALUATION);
        }

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
    public function computeEvaluation(Workspace $workspace, User $user): Evaluation
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

        foreach ($resources as $resource) {
            $resourceEval = $this->om->getRepository(ResourceUserEvaluation::class)->findOneBy([
                'resourceNode' => $resource,
                'user' => $user,
            ]);

            if (!$resourceEval) {
                ++$statusCount[AbstractEvaluation::STATUS_NOT_ATTEMPTED];

                continue;
            }

            if ($resourceEval->getStatus()) {
                ++$statusCount[$resourceEval->getStatus()];

                if ($resource->isEvaluated() && 'custom/innova_path' !== $resource->getMimeType()) {
                    // we don't count Path score because it's calculated from the sum of all the scores of the embedded resources
                    // which are already taken into account in the workspace score.
                    $score += $resourceEval->getScore() ?? 0;
                    $scoreMax += $resourceEval->getScoreMax() ?? 0;
                }
            }
        }

        $progression = $statusCount[AbstractEvaluation::STATUS_PASSED] +
            $statusCount[AbstractEvaluation::STATUS_FAILED] +
            $statusCount[AbstractEvaluation::STATUS_COMPLETED] +
            $statusCount[AbstractEvaluation::STATUS_PARTICIPATED];

        $evaluationData = [];
        $status = $evaluation->getStatus();
        if ($progression >= $progressionMax) {
            // recompute workspace score and status if all resources are done
            if ($scoreMax) {
                $evaluationData['score'] = $score;
                $evaluationData['scoreMax'] = $scoreMax;
            }

            $successCondition = $workspace->getSuccessCondition();
            if ($scoreMax && !empty($successCondition)) {
                // success conditions only apply if the workspace as a score
                $status = AbstractEvaluation::STATUS_PASSED;

                // check user score (the condition is a percentage of the max score)
                if (isset($successCondition['score'])) {
                    // the condition has been set for the workspace, we need to check it

                    $successScore = ($successCondition['score'] * $scoreMax) / 100;
                    if ($score < $successScore) {
                        // condition is not met
                        $status = AbstractEvaluation::STATUS_FAILED;
                    }
                }

                // check user success resources
                if (array_key_exists('minSuccess', $successCondition) && is_numeric($successCondition['minSuccess'])) {
                    // the condition has been set for the workspace, we need to check it
                    $minSuccess = $successCondition['minSuccess'] > $progressionMax ? $progressionMax : $successCondition['minSuccess'];
                    if ($minSuccess > $statusCount[AbstractEvaluation::STATUS_PASSED]) {
                        // condition is not met
                        $status = AbstractEvaluation::STATUS_FAILED;
                    }
                }

                // check user failed resources
                if (array_key_exists('maxFailed', $successCondition) && is_numeric($successCondition['maxFailed'])) {
                    // the condition has been set for the workspace, we need to check it
                    $maxFailed = $successCondition['maxFailed'] > $progressionMax ? $progressionMax : $successCondition['maxFailed'];
                    if ($maxFailed < $statusCount[AbstractEvaluation::STATUS_FAILED]) {
                        // condition is not met
                        $status = AbstractEvaluation::STATUS_FAILED;
                    }
                }
            } else {
                $status = AbstractEvaluation::STATUS_COMPLETED;
            }
        } elseif ((0 !== $progression && $progression < $progressionMax) || 0 < $statusCount[AbstractEvaluation::STATUS_INCOMPLETE]) {
            $status = AbstractEvaluation::STATUS_INCOMPLETE;
        }

        $evaluationData['status'] = $status;
        $evaluationData['progression'] = $progressionMax ? ($progression / $progressionMax) * 100 : 0;

        $hasChanged = $this->updateEvaluation($evaluation, $evaluationData);

        $this->om->persist($evaluation);
        $this->om->flush();

        if ($hasChanged['status'] || $hasChanged['progression'] || $hasChanged['score']) {
            $this->eventDispatcher->dispatch(new WorkspaceEvaluationEvent($evaluation, $hasChanged), EvaluationEvents::WORKSPACE_EVALUATION);
        }

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
