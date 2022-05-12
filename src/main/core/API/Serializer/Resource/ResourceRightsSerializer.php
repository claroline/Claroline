<?php

namespace Claroline\CoreBundle\API\Serializer\Resource;

use Claroline\AppBundle\API\Serializer\SerializerInterface;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\API\Serializer\User\RoleSerializer;
use Claroline\CoreBundle\Entity\Resource\ResourceRights;
use Claroline\CoreBundle\Entity\Resource\ResourceType;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Manager\Resource\MaskManager;

class ResourceRightsSerializer
{
    /** @var ObjectManager */
    private $om;
    /** @var RoleSerializer */
    private $roleSerializer;
    /** @var MaskManager */
    private $maskManager;

    public function __construct(
        ObjectManager $om,
        RoleSerializer $roleSerializer,
        MaskManager $maskManager
    ) {
        $this->om = $om;
        $this->roleSerializer = $roleSerializer;
        $this->maskManager = $maskManager;
    }

    public function getClass()
    {
        return ResourceRights::class;
    }

    public function getName()
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
            $permissions = array_merge($permissions, [
                'create' => array_map(function (ResourceType $creatableType) {
                    return $creatableType->getName();
                }, $resourceRights->getCreatableResourceTypes()->toArray()),
            ]);
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
                // ugly hack to only get create rights for directories (it's the only one that can handle it).
                $creatableTypes = [];
                if (!empty($data['permissions']) && !empty($data['permissions']['create'])) {
                    $creatableTypes = array_filter(array_map(function (string $typeName) {
                        return $this->om->getRepository(ResourceType::class)->findOneBy(['name' => $typeName]);
                    }, $data['permissions']['create']), function ($type) {
                        return !empty($type);
                    });
                }

                $resourceRights->setCreatableResourceTypes($creatableTypes);
            }
        }

        return $resourceRights;
    }
}
