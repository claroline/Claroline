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

    public function serialize(Widget $widget, array $options = [])
    {
        return [
            'id' => $widget->getUuid(),
            'name' => $widget->getName(),
            'meta' => [
                'abstract' => $widget->isAbstract(),
                'parent' => !empty($widget->getParent()) ? $this->serialize($widget->getParent()) : null,
                'context' => $widget->getContext(),
                'exportable' => $widget->isExportable(),
            ],
            'tags' => $widget->getTags(),
        ];
    }

    public function deserialize(array $data, Widget $widget, array $options = [])
    {
        // todo : implement

        return $widget;
    }
}
