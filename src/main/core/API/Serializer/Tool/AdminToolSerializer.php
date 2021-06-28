<?php

namespace Claroline\CoreBundle\API\Serializer\Tool;

use Claroline\CoreBundle\Entity\Tool\AdminTool;

class AdminToolSerializer
{
    public function getClass()
    {
        return AdminTool::class;
    }

    /**
     * @return array
     */
    public function serialize(AdminTool $tool, array $options = [])
    {
        $serialized = [
            'id' => $tool->getUuid(),
            'icon' => $tool->getClass(),
            'name' => $tool->getName(),
        ];

        return $serialized;
    }
}
