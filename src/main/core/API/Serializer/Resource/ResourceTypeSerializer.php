<?php

namespace Claroline\CoreBundle\API\Serializer\Resource;

use Claroline\CoreBundle\Component\Resource\DownloadableResourceInterface;
use Claroline\CoreBundle\Component\Resource\ResourceProvider;
use Claroline\CoreBundle\Entity\Resource\MenuAction;
use Claroline\CoreBundle\Entity\Resource\ResourceType;
use Claroline\CoreBundle\Manager\Resource\ResourceActionManager;
use Claroline\EvaluationBundle\Component\Resource\EvaluatedResourceInterface;

class ResourceTypeSerializer
{
    public function __construct(
        private readonly ResourceProvider $resourceProvider,
        private readonly ResourceActionManager $actionManager
    ) {
    }

    public function getName(): string
    {
        return 'resource_type';
    }

    /**
     * Serializes a ResourceType entity for the JSON api.
     */
    public function serialize(ResourceType $resourceType): array
    {
        $resourceHandler = $this->resourceProvider->getComponent($resourceType->getName());

        $download = $resourceHandler instanceof DownloadableResourceInterface;
        $evaluation = $resourceHandler instanceof EvaluatedResourceInterface;

        return [
            'id' => $resourceType->getId(),
            'name' => $resourceType->getName(),
            'class' => $resourceType->getClass(),
            'tags' => $resourceType->getTags(),
            'enabled' => $resourceType->isEnabled(),
            'evaluation' => $evaluation,
            'downloadable' => $download,
            'actions' => array_map(function (MenuAction $resourceAction) {
                return [
                    'name' => $resourceAction->getName(),
                    'group' => $resourceAction->getGroup(),
                    'scope' => $resourceAction->getScope(),
                    'permission' => $resourceAction->getDecoder(),
                    'default' => $resourceAction->isDefault(),
                ];
            }, $this->actionManager->all($resourceType)),
        ];
    }
}
