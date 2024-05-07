<?php

namespace Claroline\CoreBundle\API\Serializer\Resource;

use Claroline\CoreBundle\Entity\Resource\MenuAction;
use Claroline\CoreBundle\Entity\Resource\ResourceType;
use Claroline\CoreBundle\Manager\Resource\ResourceActionManager;

class ResourceTypeSerializer
{
    public function __construct(
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
        return [
            'id' => $resourceType->getId(),
            'name' => $resourceType->getName(),
            'class' => $resourceType->getClass(),
            'tags' => $resourceType->getTags(),
            'enabled' => $resourceType->isEnabled(),
            'evaluation' => true,
            'downloadable' => true,
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
