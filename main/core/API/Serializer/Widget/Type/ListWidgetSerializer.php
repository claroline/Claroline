<?php

namespace Claroline\CoreBundle\API\Serializer\Widget\Type;

use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Claroline\CoreBundle\Entity\Widget\Type\ListWidget;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.serializer.widget_list")
 * @DI\Tag("claroline.serializer")
 */
class ListWidgetSerializer
{
    use SerializerTrait;

    public function getClass()
    {
        return ListWidget::class;
    }

    public function serialize(ListWidget $widget, array $options = []): array
    {
        return [
            'filterable' => $widget->isFilterable(),
            'sortable' => $widget->isSortable(),
            'paginated' => $widget->isPaginated(),
            'pageSize' => $widget->getPageSize(),
            'display' => $widget->getDisplay(),
            'availableDisplays' => $widget->getAvailableDisplays(),
            'defaultFilters' => $widget->getFilters(),
            'availableColumns' => $widget->getAvailableColumns(),
        ];
    }

    public function deserialize($data, ListWidget $widget, array $options = []): ListWidget
    {
        $this->sipe('display', 'setDisplay', $data, $widget);
        $this->sipe('availableDisplays', 'setAvailableDisplays', $data, $widget);

        return $widget;
    }
}
