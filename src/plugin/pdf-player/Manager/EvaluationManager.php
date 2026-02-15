<?php

namespace Claroline\PdfPlayerBundle\Manager;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\User;
use Claroline\EvaluationBundle\Entity\UserEvaluation\ResourceAttempt;
use Claroline\EvaluationBundle\Entity\UserEvaluation\ResourceEvaluation;
use Claroline\EvaluationBundle\Library\EvaluationStatus;
use Claroline\EvaluationBundle\Manager\ResourceEvaluationManager;
use Claroline\EvaluationBundle\Repository\UserEvaluation\ResourceAttemptRepository;

class EvaluationManager
{
    private ResourceEvaluationManager $resourceEvalManager;
    private ResourceAttemptRepository $resourceEvalRepo;

    public function __construct(
        ObjectManager $om,
        ResourceEvaluationManager $resourceEvalManager
    ) {
        $this->resourceEvalManager = $resourceEvalManager;

        $this->resourceEvalRepo = $om->getRepository(ResourceAttempt::class);
    }

    public function getResourceUserEvaluation(ResourceNode $node, User $user): ResourceEvaluation
    {
        return $this->resourceEvalManager->getUserEvaluation($node, $user);
    }

    public function update(ResourceNode $node, User $user, $page, $total): ResourceAttempt
    {
        $evaluation = $this->resourceEvalRepo->findOneInProgress($node, $user);

        $data = ['done' => []];
        if ($evaluation) {
            $data = array_merge($data, $evaluation->getData() ?? []);
        }

        if (!in_array($page, $data['done'])) {
            // mark the step as done if it has the correct status
            $data['done'][] = $page;
        } else {
            // mark the step as not done
            array_splice($data['done'], array_search($page, $data['done']), 1);
        }

        $statusData = $this->computeResourceUserEvaluation($total, $data);

        $evaluationData = [
            'status' => $statusData['status'],
            'progression' => $statusData['progression'],
            'data' => $data,
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

    /**
     * Compute current resource evaluation status.
     */
    private function computeResourceUserEvaluation(int $total, array $data = []): array
    {
        $progression = 0;
        $progressionMax = $total;

        $status = EvaluationStatus::NOT_ATTEMPTED;
        // only compute progression if pdf is not empty
        if ($progressionMax) {
            $rest = $total - count($data['done']);

            $progression = $progressionMax - $rest;

            if ($progression >= $progressionMax) {
                $status = EvaluationStatus::COMPLETED;
            } else {
                $status = EvaluationStatus::INCOMPLETE;
            }
        }

        return [
            'progression' => $progressionMax ? ($progression / $progressionMax) * 100 : $progression,
            'status' => $status,
        ];
    }
}
