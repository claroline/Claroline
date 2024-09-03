<?php

namespace Claroline\CommunityBundle\Serializer;

use Claroline\AppBundle\API\Serializer\SerializerInterface;
use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CommunityBundle\Repository\RoleRepository;
use Claroline\CoreBundle\Entity\Group;
use Claroline\CoreBundle\Entity\Role;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class GroupSerializer
{
    use SerializerTrait;

    public function __construct(
        private readonly AuthorizationCheckerInterface $authorization,
        private readonly RoleSerializer $roleSerializer
    ) {
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
                'meta' => [
                    'description' => $group->getDescription(),
                ],
            ];
        }

        $serialized = [
            'id' => $group->getUuid(),
            'autoId' => $group->getId(),
            'name' => $group->getName(),
            'code' => $group->getCode(),
            'thumbnail' => $group->getThumbnail(),
            'poster' => $group->getPoster(),
            'meta' => [
                'description' => $group->getDescription(),
                'readOnly' => $group->isLocked(),
            ],
            'roles' => array_map(function (Role $role) {
                return $this->roleSerializer->serialize($role, [SerializerInterface::SERIALIZE_MINIMAL]);
            }, $group->getEntityRoles()->toArray()),
        ];

        if (!in_array(SerializerInterface::SERIALIZE_TRANSFER, $options)) {
            $isAdmin = $this->authorization->isGranted('ADMINISTRATE', $group);
            $serialized['permissions'] = [
                'open' => $this->authorization->isGranted('OPEN', $group),
                'edit' => $isAdmin,
                'administrate' => $isAdmin,
                'delete' => $isAdmin,
            ];
        }

        return $serialized;
    }

    /**
     * Deserializes data into a Group entity.
     */
    public function deserialize(array $data, Group $group): Group
    {
        $this->sipe('name', 'setName', $data, $group);
        $this->sipe('code', 'setCode', $data, $group);
        $this->sipe('poster', 'setPoster', $data, $group);
        $this->sipe('thumbnail', 'setThumbnail', $data, $group);
        $this->sipe('meta.description', 'setDescription', $data, $group);

        return $group;
    }
}
