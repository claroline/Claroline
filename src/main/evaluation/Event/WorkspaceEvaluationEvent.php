<?php

namespace Claroline\EvaluationBundle\Event;

use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\EvaluationBundle\Entity\UserEvaluation\WorkspaceEvaluation;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * Event dispatched when a workspace evaluation is created or updated.
 */
class WorkspaceEvaluationEvent extends Event
{
    public function __construct(
        private readonly WorkspaceEvaluation $evaluation,
        private readonly ?array $changes = []
    ) {
    }

    /**
     * Get the current evaluation.
     */
    public function getEvaluation(): WorkspaceEvaluation
    {
        return $this->evaluation;
    }

    public function getWorkspace(): Workspace
    {
        return $this->evaluation->getWorkspace();
    }

    public function getUser(): User
    {
        return $this->evaluation->getUser();
    }

    public function hasStatusChanged(): bool
    {
        return $this->changes['status'];
    }

    public function hasProgressionChanged(): bool
    {
        return $this->changes['progression'];
    }

    public function hasScoreChanged(): bool
    {
        return $this->changes['score'];
    }
}
