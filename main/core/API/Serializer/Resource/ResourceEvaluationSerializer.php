<?php

namespace Claroline\CoreBundle\API\Serializer\Resource;

use Claroline\CoreBundle\Entity\Resource\ResourceEvaluation;

class ResourceEvaluationSerializer
{
    private $resourceUserEvaluationSerializer;

    /**
     * ResourceUserEvaluationSerializer constructor.
     *
     * @param ResourceUserEvaluationSerializer $resourceUserEvaluationSerializer
     */
    public function __construct(ResourceUserEvaluationSerializer $resourceUserEvaluationSerializer)
    {
        $this->resourceUserEvaluationSerializer = $resourceUserEvaluationSerializer;
    }

    public function getName()
    {
        return 'resource_evaluation';
    }

    /**
     * Serializes a ResourceEvaluation entity for the JSON api.
     *
     * @param ResourceEvaluation $resourceEvaluation
     *
     * @return array - the serialized representation of the resource evaluation
     */
    public function serialize(ResourceEvaluation $resourceEvaluation)
    {
        $score = $resourceEvaluation->getScore();
        if ($score) {
            $score = round($score, 2);
        }

        $serialized = [
            'id' => $resourceEvaluation->getId(),
            'date' => $resourceEvaluation->getDate() ? $resourceEvaluation->getDate()->format('Y-m-d H:i') : null,
            'status' => $resourceEvaluation->getStatus(),
            'duration' => $resourceEvaluation->getDuration(),
            'score' => $score,
            'scoreMin' => $resourceEvaluation->getScoreMin(),
            'scoreMax' => $resourceEvaluation->getScoreMax(),
            'customScore' => $resourceEvaluation->getCustomScore(),
            'progression' => $resourceEvaluation->getProgression(),
            'progressionMax' => $resourceEvaluation->getProgressionMax(),
            'comment' => $resourceEvaluation->getComment(),
            'data' => $resourceEvaluation->getData(),
            'resourceUserEvaluation' => $this->resourceUserEvaluationSerializer->serialize($resourceEvaluation->getResourceUserEvaluation()),
        ];

        return $serialized;
    }
}
