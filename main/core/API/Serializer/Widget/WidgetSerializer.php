<?php

namespace Claroline\CoreBundle\API\Serializer\Widget;

use Claroline\CoreBundle\Entity\Widget\Widget;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.serializer.widget")
 * @DI\Tag("claroline.serializer")
 */
class WidgetSerializer
{
    public function getClass()
    {
        return 'Claroline\CoreBundle\Entity\Widget\Widget';
    }

    public function serialize(Widget $widget)
    {
        return [
            'id' => $widget->getUuid(),
            'name' => $widget->getName(),
            'meta' => [
                'context' => $widget->getContext(),
                'exportable' => $widget->isExportable(),
            ],
            'sources' => $widget->getSources(),
            'tags' => $widget->getTags(),
        ];
    }
}
