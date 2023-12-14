<?php

namespace Claroline\CoreBundle\API\Serializer\Tool;

use Claroline\AppBundle\API\Serializer\SerializerInterface;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CommunityBundle\Serializer\RoleSerializer;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\Tool\OrderedTool;
use Claroline\CoreBundle\Entity\Tool\ToolRights;
use Claroline\CoreBundle\Manager\Tool\ToolMaskDecoderManager;

class ToolRightsSerializer
{
    public function __construct(
        private readonly ObjectManager $om,
        private readonly RoleSerializer $roleSerializer,
        private readonly ToolMaskDecoderManager $maskManager
    ) {
    }

    public function getClass(): string
    {
        return ToolRights::class;
    }

    public function getName(): string
    {
        return 'tool_rights';
    }

    public function serialize(ToolRights $toolRights): array
    {
        $role = $toolRights->getRole();
        $orderedTool = $toolRights->getOrderedTool();

        $serialized = [
            'id' => $toolRights->getId(),
            'orderedToolId' => $orderedTool->getUuid(), // TODO : to remove
            'role' => $this->roleSerializer->serialize($role, [SerializerInterface::SERIALIZE_MINIMAL]),
            'permissions' => $this->maskManager->decodeMask($toolRights->getMask(), $orderedTool->getName()),

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

    public function deserialize(array $data, ToolRights $toolRights): ToolRights
    {
        if (!empty($data['role'])) {
            $role = $this->om->getObject($data['role'], Role::class);
        } else {
            // retro-compatibility for UI
            $role = $this->om->getRepository(Role::class)->findOneBy(['name' => $data['name']]);
        }

        if ($role) {
            $toolRights->setRole($role);
        }

        if (!empty($data['orderedTool'])) {
            /** @var OrderedTool $orderedTool */
            $orderedTool = $this->om->getObject($data['orderedTool'], OrderedTool::class);
            if ($orderedTool) {
                $toolRights->setOrderedTool($orderedTool);
            }
        }

        if ($toolRights->getOrderedTool()) {
            $toolRights->setMask($this->maskManager->encodeMask($data['permissions'], $toolRights->getOrderedTool()->getName()));
        }

        return $toolRights;
    }
}
