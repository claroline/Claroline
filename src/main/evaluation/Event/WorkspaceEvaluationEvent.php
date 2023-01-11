<?php

namespace Claroline\EvaluationBundle\Event;

use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Evaluation;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * Event dispatched when a workspace evaluation is created or updated.
 */
class WorkspaceEvaluationEvent extends Event
{
    /** @var Evaluation */
    private $evaluation;
    /** @var array */
    private $changes;

    public function __construct(Evaluation $evaluation, array $changes)
    {
        $this->evaluation = $evaluation;
        $this->changes = $changes;
    }

    /**
     * Get the current evaluation.
     */
    public function getEvaluation(): Evaluation
    {
        return $this->evaluation;
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
