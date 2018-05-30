<?php

namespace Claroline\CoreBundle\API\Serializer\Resource;

use Claroline\CoreBundle\API\Serializer\User\UserSerializer;
use Claroline\CoreBundle\Entity\Resource\ResourceUserEvaluation;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.serializer.resource_user_evaluation")
 * @DI\Tag("claroline.serializer")
 */
class ResourceUserEvaluationSerializer
{
    private $resourceNodeSerializer;
    private $userSerializer;

    /**
     * ResourceUserEvaluationSerializer constructor.
     *
     * @DI\InjectParams({
     *     "resourceNodeSerializer" = @DI\Inject("claroline.serializer.resource_node"),
     *     "userSerializer"         = @DI\Inject("claroline.serializer.user")
     * })
     *
     * @param ResourceNodeSerializer $resourceNodeSerializer
     * @param UserSerializer         $userSerializer
     */
    public function __construct(ResourceNodeSerializer $resourceNodeSerializer, UserSerializer $userSerializer)
    {
        $this->resourceNodeSerializer = $resourceNodeSerializer;
        $this->userSerializer = $userSerializer;
    }

    /**
     * Serializes a ResourceUserEvaluation entity for the JSON api.
     *
     * @param ResourceUserEvaluation $resourceUserEvaluation
     *
     * @return array - the serialized representation of the resource evaluation
     */
    public function serialize(ResourceUserEvaluation $resourceUserEvaluation)
    {
        $serialized = [
            'id' => $resourceUserEvaluation->getId(),
            'date' => $resourceUserEvaluation->getDate() ? $resourceUserEvaluation->getDate()->format('Y-m-d H:i') : null,
            'status' => $resourceUserEvaluation->getStatus(),
            'duration' => $resourceUserEvaluation->getDuration(),
            'score' => $resourceUserEvaluation->getScore(),
            'scoreMin' => $resourceUserEvaluation->getScoreMin(),
            'scoreMax' => $resourceUserEvaluation->getScoreMax(),
            'customScore' => $resourceUserEvaluation->getCustomScore(),
            'progression' => $resourceUserEvaluation->getProgression(),
            'resourceNode' => $this->resourceNodeSerializer->serialize($resourceUserEvaluation->getResourceNode()),
            'user' => $this->userSerializer->serialize($resourceUserEvaluation->getUser()),
            'userName' => $resourceUserEvaluation->getUserName(),
            'nbAttempts' => $resourceUserEvaluation->getNbAttempts(),
            'nbOpenings' => $resourceUserEvaluation->getNbOpenings(),
        ];

        return $serialized;
    }
}
