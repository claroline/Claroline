<?php

namespace Claroline\OpenBadgeBundle\Serializer;

use Claroline\AppBundle\API\Serializer\SerializerInterface;
use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Claroline\CoreBundle\API\Serializer\Resource\ResourceNodeSerializer;
use Claroline\CoreBundle\API\Serializer\Workspace\WorkspaceSerializer;
use Claroline\OpenBadgeBundle\Entity\Evidence;

class EvidenceSerializer
{
    use SerializerTrait;

    public function __construct(
        private readonly ResourceNodeSerializer $resourceNodeSerializer,
        private readonly WorkspaceSerializer $workspaceSerializer
    ) {
    }

    public function getName(): string
    {
        return 'open_badge_evidence';
    }

    public function getClass(): string
    {
        return Evidence::class;
    }

    public function serialize(Evidence $evidence, array $options = []): array
    {
        return [
            'id' => $evidence->getUuid(),
            'name' => $evidence->getName(),
            'description' => $evidence->getDescription(),
            'workspace' => $evidence->getWorkspaceEvidence() ? $this->workspaceSerializer->serialize($evidence->getWorkspaceEvidence()->getWorkspace(), [SerializerInterface::SERIALIZE_MINIMAL]) : null,
            'resource' => $evidence->getResourceEvidence() ? $this->resourceNodeSerializer->serialize($evidence->getResourceEvidence()->getResourceNode(), [SerializerInterface::SERIALIZE_MINIMAL]) : null,
        ];
    }

    public function deserialize(array $data, Evidence $evidence = null, array $options = []): Evidence
    {
        $this->sipe('id', 'setUuid', $data, $evidence);
        $this->sipe('name', 'setName', $data, $evidence);
        $this->sipe('description', 'setDescription', $data, $evidence);

        return $evidence;
    }
}
