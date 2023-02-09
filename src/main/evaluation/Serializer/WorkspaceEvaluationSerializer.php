<?php

namespace Claroline\EvaluationBundle\Serializer;

use Claroline\AppBundle\API\Serializer\SerializerInterface;
use Claroline\CommunityBundle\Serializer\UserSerializer;
use Claroline\CoreBundle\API\Serializer\Workspace\WorkspaceSerializer;
use Claroline\CoreBundle\Entity\Workspace\Evaluation;
use Claroline\CoreBundle\Library\Normalizer\DateNormalizer;

class WorkspaceEvaluationSerializer
{
    /** @var UserSerializer */
    private $userSerializer;

    /** @var WorkspaceSerializer */
    private $workspaceSerializer;

    public function __construct(
        UserSerializer $userSerializer,
        WorkspaceSerializer $workspaceSerializer
    ) {
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
            'score' => $evaluation->getScore(),
            'scoreMin' => $evaluation->getScoreMin(),
            'scoreMax' => $evaluation->getScoreMax(),
            'progression' => $evaluation->getProgression(),
        ];

        if (!in_array(SerializerInterface::SERIALIZE_MINIMAL, $options)) {
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
