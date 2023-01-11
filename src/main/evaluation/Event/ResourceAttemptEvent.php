<?php

namespace Claroline\EvaluationBundle\Event;

use Claroline\CoreBundle\Entity\Resource\ResourceEvaluation;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\Resource\ResourceUserEvaluation;
use Claroline\CoreBundle\Entity\User;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * Event dispatched when a resource attempt is created or updated.
 */
class ResourceAttemptEvent extends Event
{
    /** @var ResourceEvaluation */
    private $attempt;
    /** @var array */
    private $changes;

    public function __construct(ResourceEvaluation $attempt, array $changes)
    {
        $this->attempt = $attempt;
        $this->changes = $changes;
    }

    public function getAttempt(): ResourceEvaluation
    {
        return $this->attempt;
    }

    public function getEvaluation(): ResourceUserEvaluation
    {
        return $this->attempt->getResourceUserEvaluation();
    }

    public function getUser(): User
    {
        return $this->getEvaluation()->getUser();
    }

    public function getResourceNode(): ResourceNode
    {
        return $this->getEvaluation()->getResourceNode();
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
