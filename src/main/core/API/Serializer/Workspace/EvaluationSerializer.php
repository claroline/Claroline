<?php

namespace Claroline\CoreBundle\API\Serializer\Workspace;

use Claroline\AppBundle\API\Options;
use Claroline\CoreBundle\API\Serializer\User\UserSerializer;
use Claroline\CoreBundle\Entity\Workspace\Evaluation;
use Claroline\CoreBundle\Library\Normalizer\DateNormalizer;

class EvaluationSerializer
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

    public function getName()
    {
        return 'workspace_evaluation';
    }

    public function serialize(Evaluation $evaluation): array
    {
        return [
            'id' => $evaluation->getUuid(),
            'date' => DateNormalizer::normalize($evaluation->getDate()),
            'status' => $evaluation->getStatus(),
            'duration' => $evaluation->getDuration(),
            'score' => $evaluation->getScore(),
            'scoreMin' => $evaluation->getScoreMin(),
            'scoreMax' => $evaluation->getScoreMax(),
            'progression' => $evaluation->getProgression(),
            'progressionMax' => $evaluation->getProgressionMax(),
            'user' => $evaluation->getUser() ?
                $this->userSerializer->serialize($evaluation->getUser(), [Options::SERIALIZE_MINIMAL]) :
                ['userName' => $evaluation->getUserName()],
            'workspace' => $evaluation->getWorkspace() ?
                $this->workspaceSerializer->serialize($evaluation->getWorkspace(), [Options::SERIALIZE_MINIMAL]) :
                ['code' => $evaluation->getWorkspaceCode()],
        ];
    }
}
