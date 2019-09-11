<?php

namespace Claroline\CoreBundle\API\Serializer\Resource;

use Claroline\CoreBundle\Entity\Resource\MenuAction;
use Claroline\CoreBundle\Entity\Resource\ResourceType;
use Claroline\CoreBundle\Manager\Resource\ResourceActionManager;

class ResourceTypeSerializer
{
    /** @var ResourceActionManager */
    private $actionManager;

    /**
     * ResourceTypeSerializer constructor.
     *
     * @param ResourceActionManager $actionManager
     */
    public function __construct(ResourceActionManager $actionManager)
    {
        $this->actionManager = $actionManager;
    }

    /**
     * Serializes a ResourceType entity for the JSON api.
     *
     * @param ResourceType $resourceType - the resource type to serialize
     *
     * @return array - the serialized representation of the resource type
     */
    public function serialize(ResourceType $resourceType)
    {
        return [
            'id' => $resourceType->getId(),
            'name' => $resourceType->getName(),
            'class' => $resourceType->getClass(),
            'tags' => $resourceType->getTags(),
            'enabled' => $resourceType->isEnabled(),
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
