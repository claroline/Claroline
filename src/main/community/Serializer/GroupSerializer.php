<?php

namespace Claroline\CommunityBundle\Serializer;

use Claroline\AppBundle\API\Serializer\SerializerInterface;
use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Group;
use Claroline\CoreBundle\Entity\Organization\Organization;
use Claroline\CoreBundle\Entity\Role;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class GroupSerializer
{
    use SerializerTrait;

    /** @var AuthorizationCheckerInterface */
    private $authorization;
    /** @var ObjectManager */
    private $om;
    /** @var OrganizationSerializer */
    private $organizationSerializer;
    /** @var RoleSerializer */
    private $roleSerializer;

    public function __construct(
        AuthorizationCheckerInterface $authorization,
        ObjectManager $om,
        OrganizationSerializer $organizationSerializer,
        RoleSerializer $roleSerializer
    ) {
        $this->authorization = $authorization;
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
        if (in_array(SerializerInterface::SERIALIZE_MINIMAL, $options)) {
            return [
                'id' => $group->getUuid(),
                'name' => $group->getName(),
                'thumbnail' => $group->getThumbnail(),
            ];
        }

        $serialized = [
            'id' => $group->getUuid(),
            'autoId' => $group->getId(),
            'name' => $group->getName(),
            'thumbnail' => $group->getThumbnail(),
            'poster' => $group->getPoster(),
            'meta' => [
                'description' => $group->getDescription(),
                'readOnly' => $group->isLocked(), // TODO : to remove. for retro compatibility
            ],
            'roles' => array_map(function (Role $role) use ($options) {
                return $this->roleSerializer->serialize($role, $options);
            }, $group->getEntityRoles()->toArray()),
        ];

        if (!in_array(SerializerInterface::SERIALIZE_TRANSFER, $options)) {
            $serialized['permissions'] = [
                'open' => $this->authorization->isGranted('OPEN', $group),
                'edit' => $this->authorization->isGranted('EDIT', $group),
                'administrate' => $this->authorization->isGranted('ADMINISTRATE', $group),
                'delete' => $this->authorization->isGranted('DELETE', $group),
            ];
        }

        if (!in_array(SerializerInterface::SERIALIZE_LIST, $options)) {
            $serialized['organizations'] = array_map(function (Organization $organization) use ($options) {
                return $this->organizationSerializer->serialize($organization, $options);
            }, $group->getOrganizations()->toArray());
        }

        return $serialized;
    }

    /**
     * Deserializes data into a Group entity.
     */
    public function deserialize(array $data, Group $group, ?array $options = []): Group
    {
        $this->sipe('name', 'setName', $data, $group);
        $this->sipe('poster', 'setPoster', $data, $group);
        $this->sipe('thumbnail', 'setThumbnail', $data, $group);
        $this->sipe('meta.description', 'setDescription', $data, $group);

        if (array_key_exists('organizations', $data)) {
            $organizations = [];
            if (!empty($data['organizations'])) {
                $organizations = array_map(function ($organization) {
                    return $this->om->getObject($organization, Organization::class);
                }, $data['organizations']);
            }

            $group->setOrganizations($organizations);
        }

        return $group;
    }
}
