<?php

namespace Claroline\CoreBundle\API\Serializer;

use Claroline\CoreBundle\Entity\Tool\AdminTool;

class AdminToolSerializer
{
    public function getClass()
    {
        return AdminTool::class;
    }

    /**
     * @param AdminTool $tool
     * @param array     $options
     *
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
