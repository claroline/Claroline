<?php

namespace Claroline\ImagePlayerBundle\Manager;

use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\User;
use Claroline\EvaluationBundle\Entity\UserEvaluation\ResourceEvaluation;
use Claroline\EvaluationBundle\Library\EvaluationStatus;
use Claroline\EvaluationBundle\Manager\ResourceEvaluationManager;

/**
 * Manages the user evaluations for the image resource.
 */
class EvaluationManager
{
    public function __construct(
        private readonly ResourceEvaluationManager $resourceEvalManager
    ) {
    }

    /**
     * Marks the image resource as completed for the user.
     */
    public function update(ResourceNode $node, User $user): ResourceEvaluation
    {
        return $this->resourceEvalManager->updateUserEvaluation($node, $user, [
            'status' => EvaluationStatus::COMPLETED,
            'progression' => 100,
        ]);
    }
}
