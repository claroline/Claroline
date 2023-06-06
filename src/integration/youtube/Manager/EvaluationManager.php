<?php

namespace Claroline\YouTubeBundle\Manager;

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
    private ObjectManager $om;

    /** @var ResourceEvaluationManager */
    private ResourceEvaluationManager $resourceEvalManager;

    /** @var ResourceEvaluationRepository */
    private ResourceEvaluationRepository $resourceEvalRepo;

    public function __construct(
        ObjectManager $om,
        ResourceEvaluationManager $resourceEvalManager
    ) {
        $this->om = $om;
        $this->resourceEvalManager = $resourceEvalManager;

        $this->resourceEvalRepo = $this->om->getRepository(ResourceEvaluation::class);
    }

    public function getResourceUserEvaluation(ResourceNode $node, User $user): ResourceUserEvaluation
    {
        return $this->resourceEvalManager->getUserEvaluation($node, $user);
    }

    public function update(ResourceNode $node, User $user, float $currentTime = 0, float $totalTime = 0): ResourceEvaluation
    {
        $evaluation = $this->resourceEvalRepo->findOneInProgress($node, $user);

        $progression = $currentTime;
        $progressionMax = $totalTime;

        $status = AbstractEvaluation::STATUS_OPENED;
        if ($progressionMax) {
            $progression = ($progression / $progressionMax) * 100;

            // mark the video as finished if the user has watched over 90% of it
            if ($progression >= 90) {
                $progression = 100;
            }

            if ($progression >= 100) {
                $status = AbstractEvaluation::STATUS_COMPLETED;
            } else {
                $status = AbstractEvaluation::STATUS_INCOMPLETE;
            }
        }

        $evaluationData = [
            'status' => $status,
            'progression' => $progression,
        ];

        if ($evaluation) {
            return $this->resourceEvalManager->updateAttempt($evaluation, $evaluationData);
        }

        return $this->resourceEvalManager->createAttempt(
            $node,
            $user,
            $evaluationData
        );
    }
}
