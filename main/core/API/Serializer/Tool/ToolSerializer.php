<?php

namespace Claroline\CoreBundle\API\Serializer\Tool;

use Claroline\CoreBundle\Entity\Tool\Tool;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.serializer.tool")
 * @DI\Tag("claroline.serializer")
 */
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
