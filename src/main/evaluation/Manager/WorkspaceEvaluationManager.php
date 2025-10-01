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
use Claroline\AuthenticationBundle\Messenger\Stamp\AuthenticationStamp;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\EvaluationBundle\Entity\Sequence\Assignment;
use Claroline\EvaluationBundle\Entity\Sequence\Sequence;
use Claroline\EvaluationBundle\Entity\UserEvaluation\SequenceEvaluation;
use Claroline\EvaluationBundle\Entity\UserEvaluation\WorkspaceEvaluation;
use Claroline\EvaluationBundle\Event\EvaluationEvents;
use Claroline\EvaluationBundle\Event\WorkspaceEvaluationEvent;
use Claroline\EvaluationBundle\Library\Checker\MaxFailedChecker;
use Claroline\EvaluationBundle\Library\Checker\MinSuccessChecker;
use Claroline\EvaluationBundle\Library\Checker\ProgressionChecker;
use Claroline\EvaluationBundle\Library\Checker\ScoreChecker;
use Claroline\EvaluationBundle\Library\EvaluationAggregator;
use Claroline\EvaluationBundle\Library\GenericEvaluation;
use Claroline\EvaluationBundle\Messenger\Message\InitializeWorkspaceEvaluations;
use Claroline\EvaluationBundle\Messenger\Message\PurgeWorkspaceEvaluations;
use Claroline\EvaluationBundle\Messenger\Message\RecomputeWorkspaceEvaluations;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class WorkspaceEvaluationManager extends AbstractEvaluationManager
{
    public function __construct(
        private readonly TokenStorageInterface $tokenStorage,
        private readonly MessageBusInterface $messageBus,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly ObjectManager $om,
        private readonly SequenceEvaluationManager $sequenceEvaluationManager
    ) {
    }

    public function isEvaluated(Workspace $workspace): bool
    {
        return true;
    }

    /**
     * Retrieve or create evaluation for a workspace and a user.
     */
    public function getUserEvaluation(Workspace $workspace, User $user, ?bool $withCreation = true): ?WorkspaceEvaluation
    {
        $evaluation = $this->om->getRepository(WorkspaceEvaluation::class)->findOneBy([
            'workspace' => $workspace,
            'user' => $user,
            'archived' => false,
        ]);

        if ($withCreation && empty($evaluation)) {
            $evaluation = new WorkspaceEvaluation();
            $evaluation->setWorkspace($workspace);
            $evaluation->setUser($user);

            $this->om->persist($evaluation);
            $this->om->flush();
        }

        return $evaluation;
    }

    public function updateUserEvaluation(WorkspaceEvaluation $evaluation, SequenceEvaluation $sequenceEvaluation): WorkspaceEvaluation
    {
        $evaluation->addSequenceEvaluation($sequenceEvaluation);

        $this->om->persist($evaluation);
        $this->om->flush();

        // we only need to recompute the workspace evaluation if the sequence is required
        $recompute = $this->om->getRepository(Sequence::class)->isRequired($sequenceEvaluation->getSequence(), $sequenceEvaluation->getUser());
        if ($recompute) {
            $this->refreshEvaluation($evaluation);
        }

        return $evaluation;
    }

    /**
     * Compute evaluation status and progression of a user in a workspace.
     */
    public function computeEvaluation(Workspace $workspace, User $user): WorkspaceEvaluation
    {
        $evaluation = $this->getUserEvaluation($workspace, $user);

        $this->refreshEvaluation($evaluation);

        return $evaluation;
    }

    public function refreshEvaluation(WorkspaceEvaluation $evaluation): void
    {
        $workspace = $evaluation->getWorkspace();
        $user = $evaluation->getUser();

        // get the list of sequence the user must do to progress in the workspace
        $assignments = $this->om->getRepository(Assignment::class)->findByWorkspaceAndUser($workspace, $user, true);
        if (empty($assignments)) {
            // nothing to do if there is no required sequence in the workspace
            return;
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

        // the workspace evaluation aggregates the progression/score of all its required/scored sequences
        $aggregator = new EvaluationAggregator($conditionCheckers);

        foreach ($assignments as $assignment) {
            $sequenceEvaluation = $evaluation->getSequenceEvaluation($assignment->getSequence()->getUuid());
            if (!$sequenceEvaluation) {
                // no evaluation, adds an empty evaluation for correct progression check
                $sequenceEvaluation = new GenericEvaluation(0);
            }

            $aggregator->addEvaluation($sequenceEvaluation, $assignment->isScored());
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
    }

    public function archiveEvaluation(WorkspaceEvaluation $evaluation): void
    {
        $this->om->startFlushSuite();

        $evaluation->setArchived(true);
        $evaluation->setArchivedAt(new \DateTime());

        $this->om->persist($evaluation);

        foreach ($evaluation->getSequenceEvaluations() as $sequenceEvaluation) {
            $this->sequenceEvaluationManager->archiveEvaluation($sequenceEvaluation);
        }

        $this->om->endFlushSuite();
    }

    /**
     * Recomputes all the evaluations of a workspace.
     * This is called when required sequences are added/removed to update the user progression and score.
     */
    public function recomputeEvaluations(Workspace $workspace): void
    {
        $this->messageBus->dispatch(
            new RecomputeWorkspaceEvaluations($workspace->getId()),
            [new AuthenticationStamp($this->tokenStorage->getToken()?->getUser()->getId())]
        );
    }

    /**
     * Create missing links between the WorkspaceEvaluation and the evaluation for sequences
     * and then refreshes the WorkspaceEvaluation scores, status, etc.
     */
    public function recomputeEvaluation(WorkspaceEvaluation $evaluation): void
    {
        $workspace = $evaluation->getWorkspace();
        $user = $evaluation->getUser();

        // get the list of sequence the user must do to progress in the workspace
        $assignments = $this->om->getRepository(Assignment::class)->findByWorkspaceAndUser($workspace, $user, true);
        if (empty($assignments)) {
            // nothing to do if there is no required sequence in the workspace
            return;
        }

        foreach ($assignments as $assignment) {
            $sequenceEvaluation = $this->sequenceEvaluationManager->getUserEvaluation($assignment->getSequence(), $user, false);
            if ($sequenceEvaluation && empty($evaluation->getSequenceEvaluation($assignment->getSequence()->getUuid()))) {
                $evaluation->addSequenceEvaluation($sequenceEvaluation);
            }
        }

        $this->refreshEvaluation($evaluation);
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
                }, $users)), [new AuthenticationStamp($this->tokenStorage->getToken()?->getUser()->getId())]
            );
        }
    }

    public function purgeEvaluations(Workspace $workspace): void
    {
        $this->messageBus->dispatch(
            new PurgeWorkspaceEvaluations($workspace->getId()),
            [new AuthenticationStamp($this->tokenStorage->getToken()?->getUser()->getId())]
        );
    }
}
