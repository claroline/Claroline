<?php

namespace Claroline\CoreBundle\API\Serializer;

use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Claroline\CoreBundle\Entity\Tool\AdminTool;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.serializer.admin_tool")
 * @DI\Tag("claroline.serializer")
 */
class AdminToolSerializer
{
    use SerializerTrait;

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
            'name' => $tool->getName(),
        ];

        return $serialized;
    }
}
