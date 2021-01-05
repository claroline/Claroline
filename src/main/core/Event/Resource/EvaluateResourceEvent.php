<?php

namespace Claroline\CoreBundle\Event\Resource;

use Claroline\CoreBundle\Entity\Resource\ResourceEvaluation;
use Claroline\CoreBundle\Entity\Resource\ResourceUserEvaluation;
use Claroline\CoreBundle\Event\UserEvaluationEvent;

/**
 * Event dispatched when a resource evaluation is created or updated.
 */
class EvaluateResourceEvent extends UserEvaluationEvent
{
    /** @var ResourceEvaluation */
    private $attempt;

    public function __construct(ResourceUserEvaluation $evaluation, ResourceEvaluation $attempt)
    {
        parent::__construct($evaluation);

        $this->attempt = $attempt;
    }

    /**
     * Get the current attempt.
     */
    public function getAttempt(): ResourceEvaluation
    {
        return $this->attempt;
    }
}
