<?php

namespace Claroline\CoreBundle\API\Serializer\Tool;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\Tool\OrderedTool;
use Claroline\CoreBundle\Entity\Tool\ToolRights;
use Claroline\CoreBundle\Manager\Tool\ToolMaskDecoderManager;

class ToolRightsSerializer
{
    /** @var ObjectManager */
    private $om;
    /** @var ToolMaskDecoderManager */
    private $maskManager;

    public function __construct(
        ObjectManager $om,
        ToolMaskDecoderManager $maskManager
    ) {
        $this->om = $om;
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

        // TODO : do not flatten role data. Use RoleSerializer instead
        $serialized = [
            'id' => $toolRights->getId(),
            'translationKey' => $role->getTranslationKey(),
            'name' => $role->getName(),
            'permissions' => $this->maskManager->decodeMask($toolRights->getMask(), $orderedTool->getTool()),
            'workspace' => null,
            'orderedToolId' => $orderedTool->getUuid(),
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
        $role = $this->om->getRepository(Role::class)->findOneBy(['name' => $data['name']]);
        if ($role) {
            $toolRights->setRole($role);
        }

        /** @var OrderedTool $orderedTool */
        $orderedTool = $this->om->getRepository(OrderedTool::class)->findOneBy(['uuid' => $data['orderedToolId']]);
        if ($orderedTool) {
            $toolRights->setOrderedTool($orderedTool);
            $toolRights->setMask($this->maskManager->encodeMask($data['permissions'], $orderedTool->getTool()));
        }

        return $toolRights;
    }
}
