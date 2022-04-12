<?php

namespace Claroline\CoreBundle\API\Serializer\Tool;

use Claroline\AppBundle\API\Serializer\SerializerInterface;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\API\Serializer\User\RoleSerializer;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\Tool\OrderedTool;
use Claroline\CoreBundle\Entity\Tool\ToolRights;
use Claroline\CoreBundle\Manager\Tool\ToolMaskDecoderManager;

class ToolRightsSerializer
{
    /** @var ObjectManager */
    private $om;
    /** @var RoleSerializer */
    private $roleSerializer;
    /** @var ToolMaskDecoderManager */
    private $maskManager;

    public function __construct(
        ObjectManager $om,
        RoleSerializer $roleSerializer,
        ToolMaskDecoderManager $maskManager
    ) {
        $this->om = $om;
        $this->roleSerializer = $roleSerializer;
        $this->maskManager = $maskManager;
    }

    public function getClass()
    {
        return ToolRights::class;
    }

    public function getName()
    {
        return 'tool_rights';
    }

    public function serialize(ToolRights $toolRights): array
    {
        $role = $toolRights->getRole();
        $orderedTool = $toolRights->getOrderedTool();

        $serialized = [
            'id' => $toolRights->getId(),
            'orderedToolId' => $orderedTool->getUuid(),
            'role' => $this->roleSerializer->serialize($role, [SerializerInterface::SERIALIZE_MINIMAL]),
            'permissions' => $this->maskManager->decodeMask($toolRights->getMask(), $orderedTool->getTool()),

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

        if (!empty($data['orderedToolId'])) {
            /** @var OrderedTool $orderedTool */
            $orderedTool = $this->om->getRepository(OrderedTool::class)->findOneBy(['uuid' => $data['orderedToolId']]);
            if ($orderedTool) {
                $toolRights->setOrderedTool($orderedTool);
            }
        }

        if ($toolRights->getOrderedTool()) {
            $toolRights->setMask($this->maskManager->encodeMask($data['permissions'], $toolRights->getOrderedTool()->getTool()));
        }

        return $toolRights;
    }
}
