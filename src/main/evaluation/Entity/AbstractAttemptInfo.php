<?php

namespace Claroline\EvaluationBundle\Entity;

use Claroline\AppBundle\Entity\Identifier\Id;
use Claroline\CoreBundle\Entity\Resource\ResourceEvaluation;
use Doctrine\ORM\Mapping as ORM;

/**
 * Base class used to store additional information about a ResourceAttempt.
 * For example, individual progression for steps in Path or Quiz answers.
 */
#[ORM\MappedSuperclass]
abstract class AbstractAttemptInfo
{
    use Id;

    private ResourceEvaluation $attempt;

    public function getAttempt(): ResourceEvaluation
    {
        return $this->attempt;
    }

    public function setAttempt(ResourceEvaluation $attempt): void
    {
        $this->attempt = $attempt;
    }
}
