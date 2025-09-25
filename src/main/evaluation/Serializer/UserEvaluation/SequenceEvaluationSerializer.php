<?php

namespace Claroline\EvaluationBundle\Serializer\UserEvaluation;

use Claroline\AppBundle\API\Serializer\SerializerInterface;
use Claroline\CommunityBundle\Serializer\UserSerializer;
use Claroline\CoreBundle\Library\Normalizer\DateNormalizer;
use Claroline\EvaluationBundle\Entity\UserEvaluation\SequenceEvaluation;
use Claroline\EvaluationBundle\Library\EvaluationOptions;
use Claroline\EvaluationBundle\Serializer\Sequence\SequenceSerializer;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class SequenceEvaluationSerializer
{
    public function __construct(
        private readonly AuthorizationCheckerInterface $authorization,
        private readonly SequenceSerializer $sequenceSerializer,
        private readonly UserSerializer $userSerializer
    ) {
    }

    public function getName(): string
    {
        return 'resource_user_evaluation';
    }

    public function getClass(): string
    {
        return SequenceEvaluation::class;
    }

    public function serialize(SequenceEvaluation $evaluation, ?array $options = []): array
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
            'duration' => $evaluation->getDuration(),
            'estimatedDuration' => $evaluation->getEstimatedDuration() * 60,
            'certified' => $evaluation->isCertified(),
        ];

        // evaluation has a score, expose it
        if ($evaluation->getScoreMax()) {
            $serialized['rawScore'] = [
                'current' => $evaluation->getScore(),
                'total' => $evaluation->getScoreMax(),
            ];

            $score = $evaluation->getScore();
            $total = $evaluation->getScoreMax();
            if ($score && $evaluation->getSequence() && $evaluation->getSequence()->getScoreTotal()) {
                $score = ($score / $total) * $evaluation->getSequence()->getScoreTotal();
                $total = $evaluation->getSequence()->getScoreTotal();
            }

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

            $serialized['sequence'] = null;
            if ($evaluation->getSequence()) {
                $serialized['sequence'] = $this->sequenceSerializer->serialize($evaluation->getSequence(), [SerializerInterface::SERIALIZE_MINIMAL]);
            }

            $serialized['user'] = null;
            if ($evaluation->getUser()) {
                $serialized['user'] = $this->userSerializer->serialize($evaluation->getUser(), [SerializerInterface::SERIALIZE_MINIMAL]);
            }
        }

        return $serialized;
    }
}
