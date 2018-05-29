<?php

namespace Claroline\CoreBundle\API\Serializer\Resource;

use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Claroline\CoreBundle\Entity\Resource\ResourceRights;
use Claroline\CoreBundle\Manager\Resource\MaskManager;
use Claroline\CoreBundle\Manager\Resource\RightsManager;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.serializer.resource_rights")
 * @DI\Tag("claroline.serializer")
 */
class ResourceRightsSerializer
{
    use SerializerTrait;

    /** @var MaskManager */
    private $maskManager;

    /** @var RightsManager */
    private $rightsManager;

    /**
     * ResourceNodeManager constructor.
     *
     * @DI\InjectParams({
     *     "maskManager"   = @DI\Inject("claroline.manager.mask_manager"),
     *     "rightsManager" = @DI\Inject("claroline.manager.rights_manager")
     * })
     *
     * @param MaskManager   $maskManager
     * @param RightsManager $rightsManager
     */
    public function __construct(
        MaskManager $maskManager,
        RightsManager $rightsManager
    ) {
        $this->maskManager = $maskManager;
        $this->rightsManager = $rightsManager;
    }

    public function getClass()
    {
        return 'Claroline\CoreBundle\Entity\Resource\ResourceRights';
    }

    /**
     * Serializes a ResourceRights entity for the JSON api.
     *
     * @param ResourceRights $rights - the resource rights to serialize
     *
     * @return array - the serialized representation of the rights
     */
    public function serialize(ResourceRights $rights)
    {
        $resourceNode = $rights->getResourceNode();
        $role = $rights->getRole();

        return [
            'role' => [
                'id' => $role->getUuid(),
                'name' => $role->getName(),
                'translationKey' => $role->getTranslationKey(),
            ],
            'authorizations' => array_merge(
                $this->maskManager->decodeMask($rights->getMask(), $resourceNode->getResourceType()),
                ['create' => $this->rightsManager->getCreatableTypes([$role->getName()], $resourceNode)]
            ),
        ];;
    }

    public function deserialize(array $data, ResourceRights $rights, array $options = [])
    {
        // TODO : implement
    }
}
