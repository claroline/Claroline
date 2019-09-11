<?php

namespace Claroline\CoreBundle\API\Serializer\Widget;

use Claroline\CoreBundle\Entity\Widget\Widget;

class WidgetSerializer
{
    public function getClass()
    {
        return Widget::class;
    }

    public function serialize(Widget $widget): array
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
