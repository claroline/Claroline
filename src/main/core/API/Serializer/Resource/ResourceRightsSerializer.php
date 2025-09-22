<?php

namespace Claroline\CoreBundle\API\Serializer\Resource;

use Claroline\AppBundle\API\Serializer\SerializerInterface;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CommunityBundle\Serializer\RoleSerializer;
use Claroline\CoreBundle\Entity\Resource\ResourceRights;
use Claroline\CoreBundle\Entity\Resource\ResourceType;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Manager\Resource\MaskManager;

class ResourceRightsSerializer
{
    public function __construct(
        private readonly ObjectManager $om,
        private readonly RoleSerializer $roleSerializer,
        private readonly MaskManager $maskManager
    ) {
    }

    public function getClass(): string
    {
        return ResourceRights::class;
    }

    public function getName(): string
    {
        return 'resource_rights';
    }

    public function serialize(ResourceRights $resourceRights): array
    {
        $role = $resourceRights->getRole();
        $resourceNode = $resourceRights->getResourceNode();

        $permissions = $this->maskManager->decodeMask($resourceRights->getMask(), $resourceNode->getResourceType());
        if ('directory' === $resourceNode->getResourceType()->getName()) {
            // ugly hack to only get create rights for directories (it's the only one that can handle it).
            $permissions['create'] = $resourceRights->getCreatableResourceTypes();
        }

        $serialized = [
            'id' => $resourceRights->getId(),
            'role' => $this->roleSerializer->serialize($role, [SerializerInterface::SERIALIZE_MINIMAL]),
            'permissions' => $permissions,

            // TODO : do not flatten role data (UI expects this structure).
            'translationKey' => $role->getTranslationKey(),
            'name' => $role->getName(),
            'workspace' => null,
        ];

        if ($role->getWorkspace()) {
            $serialized['workspace'] = [
                'id' => $role->getWorkspace()->getUuid(),
                'code' => $role->getWorkspace()->getCode(),
                'name' => $role->getWorkspace()->getName(),
            ];
        }

        return $serialized;
    }

    public function deserialize(array $data, ResourceRights $resourceRights): ResourceRights
    {
        if (!empty($data['role'])) {
            $role = $this->om->getObject($data['role'], Role::class);
        } else {
            // retro-compatibility for UI
            $role = $this->om->getRepository(Role::class)->findOneBy(['name' => $data['name']]);
        }

        if ($role) {
            $resourceRights->setRole($role);
        }

        if ($resourceRights->getResourceNode()) {
            $nodeType = $resourceRights->getResourceNode()->getResourceType();

            $resourceRights->setMask($this->maskManager->encodeMask($data['permissions'], $nodeType));

            if ('directory' === $nodeType->getName()) {
                // ugly hack to only get the creation right for directories (it's the only one that can handle it).
                $creatableTypes = [];
                if (!empty($data['permissions']) && !empty($data['permissions']['create'])) {
                    $creatableTypes = array_filter($data['permissions']['create'], function (string $typeName) {
                        $resourceType = $this->om->getRepository(ResourceType::class)->findOneBy(['name' => $typeName]);

                        return !empty($resourceType);
                    });
                }

                $resourceRights->setCreatableResourceTypes($creatableTypes);
            }
        }

        return $resourceRights;
    }
}
