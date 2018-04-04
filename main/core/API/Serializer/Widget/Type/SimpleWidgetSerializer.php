<?php

namespace Claroline\CoreBundle\API\Serializer\Widget\Type;

use Claroline\CoreBundle\Entity\Widget\Type\SimpleWidget;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.serializer.widget_simple")
 * @DI\Tag("claroline.serializer")
 */
class SimpleWidgetSerializer
{
    public function getClass()
    {
        return 'Claroline\CoreBundle\Entity\Widget\Type\SimpleWidget';
    }

    public function serialize(SimpleWidget $widget, array $options = [])
    {
        return [
            'content' => $widget->getContent(),
        ];
    }

    public function deserialize($data, SimpleWidget $widget, array $options = [])
    {

        return $widget;
    }
}
