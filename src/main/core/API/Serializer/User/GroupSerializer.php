<?php

namespace Claroline\CoreBundle\API\Serializer\User;

use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Group;
use Claroline\CoreBundle\Entity\Organization\Organization;
use Claroline\CoreBundle\Entity\Role;

class GroupSerializer
{
    use SerializerTrait;

    /** @var ObjectManager */
    private $om;
    /** @var OrganizationSerializer */
    private $organizationSerializer;
    /** @var RoleSerializer */
    private $roleSerializer;

    public function __construct(
        ObjectManager $om,
        OrganizationSerializer $organizationSerializer,
        RoleSerializer $roleSerializer
    ) {
        $this->om = $om;
        $this->organizationSerializer = $organizationSerializer;
        $this->roleSerializer = $roleSerializer;
    }

    public function getClass(): string
    {
        return Group::class;
    }

    public function getName(): string
    {
        return 'group';
    }

    public function getSchema(): string
    {
        return '#/main/core/group.json';
    }

    public function getSamples(): string
    {
        return '#/main/core/group';
    }

    /**
     * Serializes a Group entity.
     */
    public function serialize(Group $group, array $options = []): array
    {
        return [
            'id' => $group->getUuid(),
            'name' => $group->getName(),
            'roles' => array_map(function (Role $role) use ($options) {
                return $this->roleSerializer->serialize($role, $options);
            }, $group->getEntityRoles()->toArray()),
            'organizations' => array_map(function (Organization $organization) use ($options) {
                return $this->organizationSerializer->serialize($organization, $options);
            }, $group->getOrganizations()->toArray()),
        ];
    }

    /**
     * Deserializes data into a Group entity.
     */
    public function deserialize(array $data, Group $group, ?array $options = []): Group
    {
        $this->sipe('name', 'setName', $data, $group);

        if (isset($data['organizations'])) {
            $group->setOrganizations(
                array_map(function ($organization) {
                    return $this->om->getObject($organization, Organization::class);
                }, $data['organizations'])
            );
        }

        // only add role here. If we want to remove them, use the crud remove method instead
        // it's useful if we want to create a user with a list of roles
        if (isset($data['roles'])) {
            foreach ($data['roles'] as $roleData) {
                /** @var Role $role */
                $role = $this->om->getObject($roleData, Role::class);
                if ($role) {
                    $group->addRole($role);
                }
            }
        }

        return $group;
    }
}
