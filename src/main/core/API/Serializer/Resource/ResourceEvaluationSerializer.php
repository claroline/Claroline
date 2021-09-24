<?php

namespace Claroline\CoreBundle\API\Serializer\Resource;

use Claroline\AppBundle\API\Options;
use Claroline\CoreBundle\Entity\Resource\ResourceEvaluation;
use Claroline\CoreBundle\Library\Normalizer\DateNormalizer;

class ResourceEvaluationSerializer
{
    public function getName()
    {
        return 'resource_evaluation';
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
            'progressionMax' => $resourceEvaluation->getProgressionMax(),
        ];

        if (!in_array(Options::SERIALIZE_MINIMAL, $options)) {
            $serialized = array_merge($serialized, [
                'comment' => $resourceEvaluation->getComment(),
                'data' => $resourceEvaluation->getData(),
            ]);
        }

        return $serialized;
    }
}
