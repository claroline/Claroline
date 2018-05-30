<?php

namespace Claroline\CoreBundle\API\Serializer\Resource;

use Claroline\CoreBundle\Entity\Resource\ResourceEvaluation;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.serializer.resource_evaluation")
 * @DI\Tag("claroline.serializer")
 */
class ResourceEvaluationSerializer
{
    private $resourceUserEvaluationSerializer;

    /**
     * ResourceUserEvaluationSerializer constructor.
     *
     * @DI\InjectParams({
     *     "resourceUserEvaluationSerializer" = @DI\Inject("claroline.serializer.resource_user_evaluation")
     * })
     *
     * @param ResourceUserEvaluationSerializer $resourceUserEvaluationSerializer
     */
    public function __construct(ResourceUserEvaluationSerializer $resourceUserEvaluationSerializer)
    {
        $this->resourceUserEvaluationSerializer = $resourceUserEvaluationSerializer;
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
        $serialized = [
            'id' => $resourceEvaluation->getId(),
            'date' => $resourceEvaluation->getDate() ? $resourceEvaluation->getDate()->format('Y-m-d H:i') : null,
            'status' => $resourceEvaluation->getStatus(),
            'duration' => $resourceEvaluation->getDuration(),
            'score' => $resourceEvaluation->getScore(),
            'scoreMin' => $resourceEvaluation->getScoreMin(),
            'scoreMax' => $resourceEvaluation->getScoreMax(),
            'customScore' => $resourceEvaluation->getCustomScore(),
            'progression' => $resourceEvaluation->getProgression(),
            'comment' => $resourceEvaluation->getComment(),
            'data' => $resourceEvaluation->getData(),
            'resourceUserEvaluation' => $this->resourceUserEvaluationSerializer->serialize($resourceEvaluation->getResourceUserEvaluation()),
        ];

        return $serialized;
    }
}
