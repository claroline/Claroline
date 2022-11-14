<?php

namespace Claroline\LogBundle\Serializer;

use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Claroline\CommunityBundle\Serializer\UserSerializer;
use Claroline\CoreBundle\API\Serializer\Resource\ResourceNodeSerializer;
use Claroline\CoreBundle\API\Serializer\Workspace\WorkspaceSerializer;
use Claroline\CoreBundle\Library\Normalizer\DateNormalizer;
use Claroline\LogBundle\Entity\FunctionalLog;

class FunctionalLogSerializer
{
    use SerializerTrait;

    private $userSerializer;
    private $resourceNodeSerializer;
    private $workspaceSerializer;

    public function __construct(
        UserSerializer $userSerializer,
        ResourceNodeSerializer $resourceNodeSerializer,
        WorkspaceSerializer $workspaceSerializer
    ) {
        $this->userSerializer = $userSerializer;
        $this->resourceNodeSerializer = $resourceNodeSerializer;
        $this->workspaceSerializer = $workspaceSerializer;
    }

    public function serialize(FunctionalLog $functionalLog): array
    {
        $user = null;
        if ($functionalLog->getUser()) {
            $user = $this->userSerializer->serialize($functionalLog->getUser(), [Options::SERIALIZE_MINIMAL]);
        }

        $resourceNode = null;
        if ($functionalLog->getResource()) {
            $resourceNode = $this->resourceNodeSerializer->serialize($functionalLog->getResource(), [Options::SERIALIZE_MINIMAL]);
        }
        $workspace = null;
        if ($functionalLog->getWorkspace()) {
            $workspace = $this->workspaceSerializer->serialize($functionalLog->getWorkspace(), [Options::SERIALIZE_MINIMAL]);
        }

        return [
            'user' => $user,
            'date' => DateNormalizer::normalize($functionalLog->getDate()),
            'details' => $functionalLog->getDetails(),
            'resource' => $resourceNode,
            'workspace' => $workspace,
            'event' => $functionalLog->getEvent(),
        ];
    }
}
