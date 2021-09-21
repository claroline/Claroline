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

    public function __construct(Evaluation $evaluation)
    {
        $this->evaluation = $evaluation;
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
}
