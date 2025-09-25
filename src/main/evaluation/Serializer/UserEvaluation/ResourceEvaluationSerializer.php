<?php

namespace Claroline\EvaluationBundle\Serializer\UserEvaluation;

use Claroline\AppBundle\API\Serializer\SerializerInterface;
use Claroline\CommunityBundle\Serializer\UserSerializer;
use Claroline\CoreBundle\API\Serializer\Resource\ResourceNodeSerializer;
use Claroline\CoreBundle\Library\Normalizer\DateNormalizer;
use Claroline\EvaluationBundle\Entity\UserEvaluation\ResourceEvaluation;
use Claroline\EvaluationBundle\Library\EvaluationOptions;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class ResourceEvaluationSerializer
{
    public function __construct(
        private readonly AuthorizationCheckerInterface $authorization,
        private readonly ResourceNodeSerializer $resourceNodeSerializer,
        private readonly UserSerializer $userSerializer
    ) {
    }

    public function getName(): string
    {
        return 'resource_user_evaluation';
    }

    public function getClass(): string
    {
        return ResourceEvaluation::class;
    }

    public function serialize(ResourceEvaluation $evaluation, ?array $options = []): array
    {
        $progression = $evaluation->getProgression();
        if ($progression) {
            $progression = round($progression, EvaluationOptions::PROGRESSION_PRECISION);
        }

        $serialized = [
            'id' => $evaluation->getUuid(),
            'meta' => [
                'archived' => $evaluation->isArchived(),
                'archivedAt' => DateNormalizer::normalize($evaluation->getArchivedAt()),
            ],
            'lastActivityAt' => DateNormalizer::normalize($evaluation->getLastActivityAt()),
            'startedAt' => DateNormalizer::normalize($evaluation->getStartedAt()),
            'endedAt' => DateNormalizer::normalize($evaluation->getEndedAt()),
            'status' => $evaluation->getStatus(),
            'progression' => $progression,
            'nbAttempts' => $evaluation->getNbAttempts(),
            'duration' => $evaluation->getDuration(),
            'estimatedDuration' => $evaluation->getEstimatedDuration() * 60,
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
            if (!in_array(SerializerInterface::SERIALIZE_TRANSFER, $options)) {
                $isAdmin = $this->authorization->isGranted('ADMINISTRATE', $evaluation);
                $serialized['permissions'] = [
                    'open' => $isAdmin || $this->authorization->isGranted('OPEN', $evaluation),
                    'administrate' => $isAdmin,
                ];
            }

            $serialized['resourceNode'] = $this->resourceNodeSerializer->serialize($evaluation->getResourceNode(), [SerializerInterface::SERIALIZE_MINIMAL]);
            $serialized['user'] = $this->userSerializer->serialize($evaluation->getUser(), [SerializerInterface::SERIALIZE_MINIMAL]);
        }

        return $serialized;
    }
}
