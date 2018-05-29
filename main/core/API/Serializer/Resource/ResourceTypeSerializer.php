<?php

namespace Claroline\CoreBundle\API\Serializer\Resource;

use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Claroline\CoreBundle\Entity\Resource\MenuAction;
use Claroline\CoreBundle\Entity\Resource\ResourceType;
use Claroline\CoreBundle\Manager\Resource\ResourceActionManager;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.serializer.resource_type")
 * @DI\Tag("claroline.serializer")
 */
class ResourceTypeSerializer
{
    use SerializerTrait;

    /** @var ResourceActionManager */
    private $actionManager;

    /**
     * ResourceTypeSerializer constructor.
     *
     * @DI\InjectParams({
     *     "actionManager" = @DI\Inject("claroline.manager.resource_action")
     * })
     *
     * @param ResourceActionManager $actionManager
     */
    public function __construct(
        ResourceActionManager $actionManager
    ) {
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
            'icon' => null, // todo
            'enabled' => $resourceType->isEnabled(),
            'actions' => array_map(function (MenuAction $resourceAction) {
                return [
                    'name' => $resourceAction->getName(),
                    'group' => $resourceAction->getGroup(),
                    'scope' => $resourceAction->getScope(),
                    'permission' => $resourceAction->getDecoder(),
                ];
            }, $this->actionManager->all($resourceType)),
        ];
    }
}
