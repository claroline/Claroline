<?php

namespace Claroline\CoreBundle\API\Serializer\Tool;

use Claroline\CoreBundle\Entity\Tool\Tool;

class ToolSerializer
{
    public function getClass()
    {
        return Tool::class;
    }

    public function serialize(Tool $tool): array
    {
        return [
          'id' => $tool->getUuid(),
          'name' => $tool->getName(),
          'icon' => $tool->getClass(),
        ];
    }
}
