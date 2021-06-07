<?php

namespace Claroline\CoreBundle\API\Serializer\Resource;

use Claroline\AppBundle\API\Options;
use Claroline\CoreBundle\API\Serializer\User\UserSerializer;
use Claroline\CoreBundle\Entity\Resource\ResourceUserEvaluation;
use Claroline\CoreBundle\Library\Normalizer\DateNormalizer;

class ResourceUserEvaluationSerializer
{
    private $resourceNodeSerializer;
    private $userSerializer;

    public function __construct(ResourceNodeSerializer $resourceNodeSerializer, UserSerializer $userSerializer)
    {
        $this->resourceNodeSerializer = $resourceNodeSerializer;
        $this->userSerializer = $userSerializer;
    }

    public function getName()
    {
        return 'resource_user_evaluation';
    }

    /**
     * Serializes a ResourceUserEvaluation entity for the JSON api.
     *
     * @return array - the serialized representation of the resource evaluation
     */
    public function serialize(ResourceUserEvaluation $resourceUserEvaluation)
    {
        $score = $resourceUserEvaluation->getScore();
        if ($score) {
            $score = round($score, 2);
        }

        return [
            'id' => $resourceUserEvaluation->getId(),
            'date' => DateNormalizer::normalize($resourceUserEvaluation->getDate()),
            'status' => $resourceUserEvaluation->getStatus(),
            'duration' => $resourceUserEvaluation->getDuration(),
            'score' => $score,
            'scoreMin' => $resourceUserEvaluation->getScoreMin(),
            'scoreMax' => $resourceUserEvaluation->getScoreMax(),
            'progression' => $resourceUserEvaluation->getProgression(),
            'progressionMax' => $resourceUserEvaluation->getProgressionMax(),
            'resourceNode' => $this->resourceNodeSerializer->serialize($resourceUserEvaluation->getResourceNode(), [Options::SERIALIZE_MINIMAL]), // TODO : remove me or add an option
            'user' => $this->userSerializer->serialize($resourceUserEvaluation->getUser(), [Options::SERIALIZE_MINIMAL]),
            'nbAttempts' => $resourceUserEvaluation->getNbAttempts(),
            'nbOpenings' => $resourceUserEvaluation->getNbOpenings(),
            'required' => $resourceUserEvaluation->isRequired(),
        ];
    }
}
