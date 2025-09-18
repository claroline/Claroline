<?php

namespace Claroline\EvaluationBundle\Serializer\UserEvaluation;

use Claroline\AppBundle\API\Serializer\SerializerInterface;
use Claroline\CommunityBundle\Serializer\UserSerializer;
use Claroline\CoreBundle\API\Serializer\Resource\ResourceNodeSerializer;
use Claroline\CoreBundle\Library\Normalizer\DateNormalizer;
use Claroline\EvaluationBundle\Entity\UserEvaluation\ResourceAttempt;
use Claroline\EvaluationBundle\Library\EvaluationOptions;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class ResourceAttemptSerializer
{
    public function __construct(
        private readonly AuthorizationCheckerInterface $authorization,
        private readonly ResourceNodeSerializer $resourceNodeSerializer,
        private readonly UserSerializer $userSerializer
    ) {
    }

    public function getName(): string
    {
        return 'resource_evaluation';
    }

    public function getClass(): string
    {
        return ResourceAttempt::class;
    }

    public function serialize(ResourceAttempt $evaluation, array $options = []): array
    {
        $score = $evaluation->getScore();
        if ($score) {
            $score = round($score, EvaluationOptions::SCORE_PRECISION);
        }

        $progression = $evaluation->getProgression();
        if ($progression) {
            $progression = round($progression, EvaluationOptions::PROGRESSION_PRECISION);
        }

        $serialized = [
            'id' => $evaluation->getUuid(),
            'lastActivityAt' => DateNormalizer::normalize($evaluation->getLastActivityAt()),
            'startedAt' => DateNormalizer::normalize($evaluation->getStartedAt()),
            'endedAt' => DateNormalizer::normalize($evaluation->getEndedAt()),
            'status' => $evaluation->getStatus(),
            'duration' => $evaluation->getDuration(),
            'score' => $score, // deprecated
            'scoreMin' => $evaluation->getScoreMin(), // deprecated
            'scoreMax' => $evaluation->getScoreMax(), // deprecated
            'progression' => $progression,
        ];

        // evaluation has a score, expose it
        if ($evaluation->getScoreMax()) {
            $serialized['rawScore'] = [
                'current' => $evaluation->getScore(),
                'total' => $evaluation->getScoreMax(),
            ];

            $score = $evaluation->getScore();
            $total = $evaluation->getScoreMax();
            /*if ($evaluation->getResourceNode() && $evaluation->getResourceNode()->getScoreTotal()) {
                $score = ($evaluation->getScore() / $evaluation->getScoreMax()) * $evaluation->getResourceNode()->getScoreTotal();
                $total = $evaluation->getResourceNode()->getScoreTotal();
            }*/

            if ($score) {
                $score = round($score, EvaluationOptions::SCORE_PRECISION);
            }

            $serialized['displayScore'] = [
                'current' => $score,
                'total' => $total,
            ];
        }

        if (!in_array(SerializerInterface::SERIALIZE_MINIMAL, $options)) {
            $resourceUserEvaluation = $evaluation->getResourceUserEvaluation();

            if (!in_array(SerializerInterface::SERIALIZE_TRANSFER, $options)) {
                $isAdmin = $this->authorization->isGranted('ADMINISTRATE', $evaluation);
                $serialized['permissions'] = [
                    'open' => $isAdmin || $this->authorization->isGranted('OPEN', $evaluation),
                    'administrate' => $isAdmin,
                ];
            }

            $serialized = array_merge($serialized, [
                'comment' => $evaluation->getComment(),
                'data' => $evaluation->getData(),

                // used by data source, this may require another option to avoid getting it where we don't want it
                'resourceNode' => $this->resourceNodeSerializer->serialize($resourceUserEvaluation->getResourceNode(), [SerializerInterface::SERIALIZE_MINIMAL]),
                'user' => $this->userSerializer->serialize($resourceUserEvaluation->getUser(), [SerializerInterface::SERIALIZE_MINIMAL]),
            ]);
        }

        return $serialized;
    }
}
