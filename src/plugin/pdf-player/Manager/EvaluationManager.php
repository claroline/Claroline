<?php

namespace Claroline\PdfPlayerBundle\Manager;

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

    public function getResourceUserEvaluation(ResourceNode $node, User $user): ResourceUserEvaluation
    {
        return $this->resourceEvalManager->getUserEvaluation($node, $user);
    }

    public function update(ResourceNode $node, User $user, $page, $total)
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
            'progressionMax' => $statusData['progressionMax'],
            'data' => $data,
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

    /**
     * Compute current resource evaluation status.
     *
     * @param int $total
     *
     * @return array
     */
    private function computeResourceUserEvaluation($total, array $data = [])
    {
        $progression = 0;
        $progressionMax = $total;

        $status = AbstractEvaluation::STATUS_OPENED;
        // only compute progression if pdf is not empty
        if ($progressionMax) {
            $rest = $total - count($data['done']);

            $progression = $progressionMax - $rest;

            if ($progression >= $progressionMax) {
                $status = AbstractEvaluation::STATUS_COMPLETED;
            } else {
                $status = AbstractEvaluation::STATUS_INCOMPLETE;
            }
        }

        return [
            'progression' => $progressionMax ? ($progression / $progressionMax) * 100 : $progression,
            'progressionMax' => $progressionMax, // TODO : for retro compatibility
            'status' => $status,
        ];
    }
}
