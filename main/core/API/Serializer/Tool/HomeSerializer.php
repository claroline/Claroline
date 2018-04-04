<?php

namespace Claroline\CoreBundle\API\Serializer\Tool;

use Claroline\CoreBundle\Entity\Widget\Widget;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.serializer.tool_home")
 * @DI\Tag("claroline.serializer")
 */
class HomeSerializer
{
    public function getClass()
    {
        return 'Claroline\CoreBundle\Entity\Tool\Home\HomeTab';
    }

    public function serialize(Widget $widget, array $options = [])
    {
        return [

        ];
    }

    public function deserialize(array $data, Widget $widget, array $options = [])
    {
        // todo : implement

        return $widget;
    }
}
