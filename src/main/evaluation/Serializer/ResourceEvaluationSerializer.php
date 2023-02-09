<?php

namespace Claroline\EvaluationBundle\Serializer;

use Claroline\AppBundle\API\Serializer\SerializerInterface;
use Claroline\CommunityBundle\Serializer\UserSerializer;
use Claroline\CoreBundle\API\Serializer\Resource\ResourceNodeSerializer;
use Claroline\CoreBundle\Entity\Resource\ResourceEvaluation;
use Claroline\CoreBundle\Library\Normalizer\DateNormalizer;

class ResourceEvaluationSerializer
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
        return 'resource_evaluation';
    }

    public function getClass(): string
    {
        return ResourceEvaluation::class;
    }

    public function serialize(ResourceEvaluation $resourceEvaluation, array $options = []): array
    {
        $score = $resourceEvaluation->getScore();
        if ($score) {
            $score = round($score, 2);
        }

        $serialized = [
            'id' => $resourceEvaluation->getId(),
            'date' => DateNormalizer::normalize($resourceEvaluation->getDate()),
            'status' => $resourceEvaluation->getStatus(),
            'duration' => $resourceEvaluation->getDuration(),
            'score' => $score,
            'scoreMin' => $resourceEvaluation->getScoreMin(),
            'scoreMax' => $resourceEvaluation->getScoreMax(),
            'progression' => $resourceEvaluation->getProgression(),
        ];

        if (!in_array(SerializerInterface::SERIALIZE_MINIMAL, $options)) {
            $resourceUserEvaluation = $resourceEvaluation->getResourceUserEvaluation();

            $serialized = array_merge($serialized, [
                'comment' => $resourceEvaluation->getComment(),
                'data' => $resourceEvaluation->getData(),

                // used by data source, this may require another option to avoid getting it where we don't want it
                'resourceNode' => $this->resourceNodeSerializer->serialize($resourceUserEvaluation->getResourceNode(), [SerializerInterface::SERIALIZE_MINIMAL]),
                'user' => $this->userSerializer->serialize($resourceUserEvaluation->getUser(), [SerializerInterface::SERIALIZE_MINIMAL]),
            ]);
        }

        return $serialized;
    }
}
