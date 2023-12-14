<?php

namespace Claroline\EvaluationBundle\Event;

use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\Resource\ResourceUserEvaluation;
use Claroline\CoreBundle\Entity\User;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * Event dispatched when a resource evaluation is created or updated.
 */
class ResourceEvaluationEvent extends Event
{
    public function __construct(
        private readonly ResourceUserEvaluation $evaluation,
        private readonly array $changes
    ) {
    }

    /**
     * Get the current evaluation.
     */
    public function getEvaluation(): ResourceUserEvaluation
    {
        return $this->evaluation;
    }

    public function getUser(): User
    {
        return $this->evaluation->getUser();
    }

    public function getResourceNode(): ResourceNode
    {
        return $this->evaluation->getResourceNode();
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
