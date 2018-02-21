<?php

namespace FormaLibre\ReservationBundle\Serializer;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\API\Serializer\User\RoleSerializer;
use FormaLibre\ReservationBundle\Entity\ResourceRights;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.serializer.reservation.resource_rights")
 * @DI\Tag("claroline.serializer")
 */
class ResourceRightsSerializer
{
    private $roleSerializer;

    private $roleRepo;
    private $resourceRepo;

    /**
     * ResourceSerializer constructor.
     *
     * @DI\InjectParams({
     *     "om"             = @DI\Inject("claroline.persistence.object_manager"),
     *     "roleSerializer" = @DI\Inject("claroline.serializer.role")
     * })
     *
     * @param ObjectManager  $om
     * @param RoleSerializer $roleSerializer
     */
    public function __construct(
        ObjectManager $om,
        RoleSerializer $roleSerializer
    ) {
        $this->roleSerializer = $roleSerializer;

        $this->roleRepo = $om->getRepository('ClarolineCoreBundle:Role');
        $this->resourceRepo = $om->getRepository('FormaLibreReservationBundle:Resource');
    }

    /**
     * @param ResourceRights $resourceRights
     *
     * @return array
     */
    public function serialize(ResourceRights $resourceRights)
    {
        return [
            'id' => $resourceRights->getUuid(),
            'mask' => $resourceRights->getMask(),
            'resource' => [
                'id' => $resourceRights->getResource()->getUuid(),
            ],
            'role' => $this->roleSerializer->serialize($resourceRights->getRole()),
        ];
    }

    /**
     * Deserializes data into a ResourceRights entity.
     *
     * @param \stdClass      $data
     * @param ResourceRights $resourceRights
     *
     * @return ResourceRights
     */
    public function deserialize($data, ResourceRights $resourceRights = null)
    {
        if (empty($resourceRights)) {
            $resourceRights = new ResourceRights();
        }
        if (isset($data['mask'])) {
            $resourceRights->setMask($data['mask']);
        }
        if (isset($data['resource'])) {
            $resource = $this->resourceRepo->findOneBy(['uuid' => $data['resource']['id']]);
            $resourceRights->setResource($resource);
        }
        if (isset($data['role'])) {
            $role = $this->roleRepo->findOneBy(['uuid' => $data['role']['id']]);
            $resourceRights->setRole($role);
        }

        return $resourceRights;
    }
}
