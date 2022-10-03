<?php

namespace Claroline\VideoPlayerBundle\Manager;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Resource\ResourceEvaluation;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\Resource\ResourceUserEvaluation;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Repository\Resource\ResourceEvaluationRepository;
use Claroline\EvaluationBundle\Entity\AbstractEvaluation;
use Claroline\EvaluationBundle\Manager\ResourceEvaluationManager;

class EvaluationManager
{
    /** @var ObjectManager */
    private $om;

    /** @var ResourceEvaluationManager */
    private $resourceEvalManager;

    /** @var ResourceEvaluationRepository */
    private $resourceEvalRepo;

    public function __construct(
        ObjectManager $om,
        ResourceEvaluationManager $resourceEvalManager
    ) {
        $this->om = $om;
        $this->resourceEvalManager = $resourceEvalManager;

        $this->resourceEvalRepo = $this->om->getRepository(ResourceEvaluation::class);
    }

    /**
     * Fetch or create resource user evaluation.
     */
    public function getResourceUserEvaluation(ResourceNode $node, User $user): ResourceUserEvaluation
    {
        return $this->resourceEvalManager->getUserEvaluation($node, $user);
    }

    public function update(ResourceNode $node, User $user, float $currentTime = 0, float $totalTime = 0)
    {
        $evaluation = $this->resourceEvalRepo->findOneInProgress($node, $user);

        $progression = $currentTime;
        $progressionMax = $totalTime;

        $status = AbstractEvaluation::STATUS_OPENED;
        if ($progressionMax) {
            if ($progression >= $progressionMax) {
                $status = AbstractEvaluation::STATUS_COMPLETED;
            } else {
                $status = AbstractEvaluation::STATUS_INCOMPLETE;
            }
        }

        $evaluationData = [
            'status' => $status,
            'progression' => $progressionMax ? ($progression / $progressionMax) * 100 : $progression,
            'progressionMax' => 100,
        ];

        if ($evaluation) {
            return $this->resourceEvalManager->updateResourceEvaluation($evaluation, $evaluationData);
        }

        return $this->resourceEvalManager->createResourceEvaluation(
            $node,
            $user,
            $evaluationData
        );
    }
}
