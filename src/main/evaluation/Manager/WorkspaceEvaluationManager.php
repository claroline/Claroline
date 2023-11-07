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
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Evaluation;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\EvaluationBundle\Event\EvaluationEvents;
use Claroline\EvaluationBundle\Event\WorkspaceEvaluationEvent;
use Claroline\EvaluationBundle\Library\Checker\MaxFailedChecker;
use Claroline\EvaluationBundle\Library\Checker\MinSuccessChecker;
use Claroline\EvaluationBundle\Library\Checker\ProgressionChecker;
use Claroline\EvaluationBundle\Library\Checker\ScoreChecker;
use Claroline\EvaluationBundle\Library\EvaluationAggregator;
use Claroline\EvaluationBundle\Library\GenericEvaluation;
use Claroline\EvaluationBundle\Messenger\Message\InitializeWorkspaceEvaluations;
use Claroline\EvaluationBundle\Messenger\Message\RecomputeWorkspaceEvaluations;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class WorkspaceEvaluationManager extends AbstractEvaluationManager
{
    public function __construct(
        private readonly MessageBusInterface $messageBus,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly ObjectManager $om,
        private readonly ResourceEvaluationManager $resourceEvalManager
    ) {
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

            $this->om->persist($evaluation);
            $this->om->flush();
        }

        return $evaluation;
    }

    public function updateUserEvaluation(Workspace $workspace, User $user, ?array $data = [], \DateTimeInterface $date = null): Evaluation
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
     * Compute evaluation status and progression of a user in a workspace.
     */
    public function computeEvaluation(Workspace $workspace, User $user): Evaluation
    {
        $evaluation = $this->getUserEvaluation($workspace, $user);

        // get the list of resources which are configured to participate in the workspace evaluation.
        $resources = $this->getRequiredResources($workspace);
        if (empty($resources)) {
            // nothing to do if there is no required resources in the workspace
            return $evaluation;
        }

        $conditionCheckers = [
            new ProgressionChecker(),
        ];

        // get the success condition of the workspace if any
        $successCondition = $workspace->getSuccessCondition();
        if (!empty($successCondition)) {
            if (array_key_exists('score', $successCondition) && is_numeric($successCondition['score'])) {
                // check user score (the condition is a percentage of the max score)
                $conditionCheckers[] = new ScoreChecker($successCondition['score']);
            }

            if (array_key_exists('minSuccess', $successCondition) && is_numeric($successCondition['minSuccess'])) {
                // check user success resources
                $conditionCheckers[] = new MinSuccessChecker($successCondition['minSuccess']);
            }

            if (array_key_exists('maxFailed', $successCondition) && is_numeric($successCondition['maxFailed'])) {
                // check user failed resources
                $conditionCheckers[] = new MaxFailedChecker($successCondition['maxFailed']);
            }
        }

        // the workspace evaluation aggregates the progression/score of all its required/evaluated resources
        $aggregator = new EvaluationAggregator($conditionCheckers);

        foreach ($resources as $resource) {
            $resourceEvaluation = $this->resourceEvalManager->getUserEvaluation($resource, $user, false);
            if (!$resourceEvaluation) {
                // no evaluation, adds an empty evaluation for correct progression check
                $resourceEvaluation = new GenericEvaluation(0);
            }

            // we don't count Path score because it's calculated from the sum of all the scores of the embedded resources
            // which are already taken into account in the workspace score.
            $aggregator->addEvaluation($resourceEvaluation, $resource->isEvaluated() && 'custom/innova_path' !== $resource->getMimeType());
        }

        // update evaluation data
        $hasChanged = $this->updateEvaluation($evaluation, [
            'status' => $aggregator->getStatus(),
            'score' => $aggregator->getScore(),
            'scoreMax' => $aggregator->getScoreMax(),
            'progression' => $aggregator->getProgression(),
        ]);

        $this->om->persist($evaluation);
        $this->om->flush();

        if ($hasChanged['status'] || $hasChanged['progression'] || $hasChanged['score']) {
            $this->eventDispatcher->dispatch(new WorkspaceEvaluationEvent($evaluation, $hasChanged), EvaluationEvents::WORKSPACE_EVALUATION);
        }

        return $evaluation;
    }
}
