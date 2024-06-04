<?php

namespace Claroline\EvaluationBundle\Serializer;

use Claroline\AppBundle\API\Serializer\SerializerInterface;
use Claroline\CommunityBundle\Serializer\UserSerializer;
use Claroline\CoreBundle\API\Serializer\Workspace\WorkspaceSerializer;
use Claroline\CoreBundle\Entity\Workspace\Evaluation;
use Claroline\CoreBundle\Library\Normalizer\DateNormalizer;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class WorkspaceEvaluationSerializer
{
    private AuthorizationCheckerInterface $authorization;
    private UserSerializer $userSerializer;
    private WorkspaceSerializer $workspaceSerializer;

    public function __construct(
        AuthorizationCheckerInterface $authorization,
        UserSerializer $userSerializer,
        WorkspaceSerializer $workspaceSerializer
    ) {
        $this->authorization = $authorization;
        $this->userSerializer = $userSerializer;
        $this->workspaceSerializer = $workspaceSerializer;
    }

    public function getName(): string
    {
        return 'workspace_evaluation';
    }

    public function getClass(): string
    {
        return Evaluation::class;
    }

    public function serialize(Evaluation $evaluation, ?array $options = []): array
    {
        $serialized = [
            'id' => $evaluation->getUuid(),
            'date' => DateNormalizer::normalize($evaluation->getDate()),
            'status' => $evaluation->getStatus(),
            'duration' => $evaluation->getDuration(),
            'progression' => $evaluation->getProgression(),
            'estimatedDuration' => $evaluation->getEstimatedDuration(),
        ];

        // evaluation has a score, expose it
        if ($evaluation->getScoreMax()) {
            $serialized['rawScore'] = [
                'current' => $evaluation->getScore(),
                'total' => $evaluation->getScoreMax(),
            ];

            if ($evaluation->getWorkspace() && $evaluation->getWorkspace()->getScoreTotal()) {
                $serialized['displayScore'] = [
                    'current' => ($evaluation->getScore() / $evaluation->getScoreMax()) * $evaluation->getWorkspace()->getScoreTotal(),
                    'total' => $evaluation->getWorkspace()->getScoreTotal(),
                ];
            } else {
                $serialized['displayScore'] = [
                    'current' => $evaluation->getScore(),
                    'total' => $evaluation->getScoreMax(),
                ];
            }
        }

        if (!in_array(SerializerInterface::SERIALIZE_MINIMAL, $options)) {
            if (!in_array(SerializerInterface::SERIALIZE_TRANSFER, $options)) {
                $isAdmin = $this->authorization->isGranted('ADMINISTRATE', $evaluation);
                $serialized['permissions'] = [
                    'open' => $isAdmin || $this->authorization->isGranted('OPEN', $evaluation),
                    'administrate' => $isAdmin,
                    'delete' => $isAdmin,
                ];
            }

            $serialized['user'] = null;
            if ($evaluation->getUser()) {
                $serialized['user'] = $this->userSerializer->serialize($evaluation->getUser(), [SerializerInterface::SERIALIZE_MINIMAL]);
            }

            $serialized['workspace'] = null;
            if ($evaluation->getWorkspace()) {
                $serialized['workspace'] = $this->workspaceSerializer->serialize($evaluation->getWorkspace(), [SerializerInterface::SERIALIZE_MINIMAL]);
            }
        }

        return $serialized;
    }
}
