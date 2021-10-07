<?php

namespace Claroline\EvaluationBundle\Serializer;

use Claroline\AppBundle\API\Options;
use Claroline\CoreBundle\API\Serializer\Resource\ResourceNodeSerializer;
use Claroline\CoreBundle\API\Serializer\User\UserSerializer;
use Claroline\CoreBundle\Entity\Resource\ResourceUserEvaluation;
use Claroline\CoreBundle\Library\Normalizer\DateNormalizer;

class ResourceUserEvaluationSerializer
{
    /** @var ResourceNodeSerializer */
    private $resourceNodeSerializer;
    /** @var UserSerializer */
    private $userSerializer;

    public function __construct(ResourceNodeSerializer $resourceNodeSerializer, UserSerializer $userSerializer)
    {
        $this->resourceNodeSerializer = $resourceNodeSerializer;
        $this->userSerializer = $userSerializer;
    }

    public function getName(): string
    {
        return 'resource_user_evaluation';
    }

    public function getClass(): string
    {
        return ResourceUserEvaluation::class;
    }

    public function serialize(ResourceUserEvaluation $resourceUserEvaluation, ?array $options = []): array
    {
        $score = $resourceUserEvaluation->getScore();
        if ($score) {
            $score = round($score, 2);
        }

        $serialized = [
            'id' => $resourceUserEvaluation->getId(),
            'date' => DateNormalizer::normalize($resourceUserEvaluation->getDate()),
            'status' => $resourceUserEvaluation->getStatus(),
            'duration' => $resourceUserEvaluation->getDuration(),
            'score' => $score,
            'scoreMin' => $resourceUserEvaluation->getScoreMin(),
            'scoreMax' => $resourceUserEvaluation->getScoreMax(),
            'progression' => $resourceUserEvaluation->getProgression(),
            'progressionMax' => $resourceUserEvaluation->getProgressionMax(),
            'nbAttempts' => $resourceUserEvaluation->getNbAttempts(),
            'nbOpenings' => $resourceUserEvaluation->getNbOpenings(),
            'required' => $resourceUserEvaluation->isRequired(),
        ];

        if (!in_array(Options::SERIALIZE_MINIMAL, $options)) {
            $serialized['resourceNode'] = $this->resourceNodeSerializer->serialize($resourceUserEvaluation->getResourceNode(), [Options::SERIALIZE_MINIMAL]);
            $serialized['user'] = $this->userSerializer->serialize($resourceUserEvaluation->getUser(), [Options::SERIALIZE_MINIMAL]);
        }

        return $serialized;
    }
}
