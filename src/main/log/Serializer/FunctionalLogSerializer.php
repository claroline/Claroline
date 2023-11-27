<?php

namespace Claroline\LogBundle\Serializer;

use Claroline\AppBundle\API\Serializer\SerializerInterface;
use Claroline\CommunityBundle\Serializer\UserSerializer;
use Claroline\CoreBundle\API\Serializer\Resource\ResourceNodeSerializer;
use Claroline\CoreBundle\API\Serializer\Workspace\WorkspaceSerializer;
use Claroline\LogBundle\Entity\FunctionalLog;

class FunctionalLogSerializer extends AbstractLogSerializer
{
    public function __construct(
        UserSerializer $userSerializer,
        private readonly ResourceNodeSerializer $resourceNodeSerializer,
        private readonly WorkspaceSerializer $workspaceSerializer
    ) {
        parent::__construct($userSerializer);
    }

    public function getClass(): string
    {
        return FunctionalLog::class;
    }

    public function serialize(FunctionalLog $functionalLog, array $options = []): array
    {
        $serialized = $this->serializeCommon($functionalLog, $options);
        if (in_array(SerializerInterface::SERIALIZE_MINIMAL, $options)) {
            return $serialized;
        }

        $resourceNode = null;
        if ($functionalLog->getResource()) {
            $resourceNode = $this->resourceNodeSerializer->serialize($functionalLog->getResource(), [SerializerInterface::SERIALIZE_MINIMAL]);
        }
        $workspace = null;
        if ($functionalLog->getWorkspace()) {
            $workspace = $this->workspaceSerializer->serialize($functionalLog->getWorkspace(), [SerializerInterface::SERIALIZE_MINIMAL]);
        }

        return array_merge($serialized, [
            'resource' => $resourceNode,
            'workspace' => $workspace,
        ]);
    }
}
