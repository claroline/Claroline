<?php

namespace Claroline\CoreBundle\API\Serializer\Resource;

use Claroline\CoreBundle\Entity\Resource\ResourceEvaluation;
use Claroline\CoreBundle\Library\Normalizer\DateNormalizer;

class ResourceEvaluationSerializer
{
    private $resourceUserEvaluationSerializer;

    /**
     * ResourceUserEvaluationSerializer constructor.
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
     * @return array - the serialized representation of the resource evaluation
     */
    public function serialize(ResourceEvaluation $resourceEvaluation)
    {
        $score = $resourceEvaluation->getScore();
        if ($score) {
            $score = round($score, 2);
        }

        return [
            'id' => $resourceEvaluation->getId(),
            'date' => DateNormalizer::normalize($resourceEvaluation->getDate()),
            'status' => $resourceEvaluation->getStatus(),
            'duration' => $resourceEvaluation->getDuration(),
            'score' => $score,
            'scoreMin' => $resourceEvaluation->getScoreMin(),
            'scoreMax' => $resourceEvaluation->getScoreMax(),
            'progression' => $resourceEvaluation->getProgression(),
            'progressionMax' => $resourceEvaluation->getProgressionMax(),
            'comment' => $resourceEvaluation->getComment(),
            'data' => $resourceEvaluation->getData(),
            'resourceUserEvaluation' => $this->resourceUserEvaluationSerializer->serialize($resourceEvaluation->getResourceUserEvaluation()),
        ];
    }
}
