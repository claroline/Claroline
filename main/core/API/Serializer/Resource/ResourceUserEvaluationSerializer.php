<?php

namespace Claroline\CoreBundle\API\Serializer\Resource;

use Claroline\CoreBundle\API\Serializer\User\UserSerializer;
use Claroline\CoreBundle\Entity\Resource\ResourceUserEvaluation;

class ResourceUserEvaluationSerializer
{
    private $resourceNodeSerializer;
    private $userSerializer;

    /**
     * ResourceUserEvaluationSerializer constructor.
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
            'progressionMax' => $resourceUserEvaluation->getProgressionMax(),
            'resourceNode' => $this->resourceNodeSerializer->serialize($resourceUserEvaluation->getResourceNode()), // TODO : remove me or add an option
            'user' => $this->userSerializer->serialize($resourceUserEvaluation->getUser()),
            'userName' => $resourceUserEvaluation->getUserName(),
            'nbAttempts' => $resourceUserEvaluation->getNbAttempts(),
            'nbOpenings' => $resourceUserEvaluation->getNbOpenings(),
            'required' => $resourceUserEvaluation->isRequired(),
        ];

        return $serialized;
    }
}
