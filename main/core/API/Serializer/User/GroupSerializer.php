<?php

namespace Claroline\CoreBundle\API\Serializer\User;

use Claroline\CoreBundle\API\SerializerProvider;
use Claroline\CoreBundle\Entity\Group;
use Claroline\CoreBundle\Entity\Organization\Organization;
use Claroline\CoreBundle\Entity\Role;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.serializer.group")
 * @DI\Tag("claroline.serializer")
 */
class GroupSerializer
{
    /**
     * GroupSerializer constructor.
     *
     * @DI\InjectParams({
     *     "serializer" = @DI\Inject("claroline.api.serializer")
     * })
     *
     * @param SerializerProvider $serializer
     */
    public function __construct(SerializerProvider $serializer)
    {
        $this->serializer = $serializer;
    }

    public function getClass()
    {
        return 'Claroline\CoreBundle\Entity\Group';
    }

    /**
     * Serializes a Group entity.
     *
     * @param Group $group
     * @param array $options
     *
     * @return array
     */
    public function serialize(Group $group, array $options = [])
    {
        return [
            'id' => $group->getUuid(),
            'name' => $group->getName(),
            'roles' => array_map(function (Role $role) use ($options) {
                return $this->serializer->serialize($role, $options);
            }, $group->getEntityRoles()->toArray()),
            'organizations' => array_map(function (Organization $organization) use ($options) {
                return $this->serializer->serialize($organization, $options);
            }, $group->getOrganizations()->toArray()),
        ];
    }

    /**
     * Deserializes data into a Group entity.
     *
     * @param \stdClass $data
     * @param Group     $group
     * @param array     $options
     *
     * @return Group
     */
    public function deserialize($data, Group $group = null, array $options = [])
    {
        if (isset($data['name'])) {
            $group->setName($data['name']);
        }

        if (isset($data['organizations'])) {
            $group->setOrganizations(
                array_map(function ($organization) use ($options) {
                    return $this->serializer->deserialize(
                        'Claroline\CoreBundle\Entity\Organization\Organization',
                        $organization,
                        $options
                    );
                }, $data['organizations'])
            );
        }

        return $group;
    }

    public function getSchema()
    {
        return '#/main/core/group.json';
    }

    /**
     * @return string
     */
    public function getSamples()
    {
        return '#/main/core/group';
    }
}
