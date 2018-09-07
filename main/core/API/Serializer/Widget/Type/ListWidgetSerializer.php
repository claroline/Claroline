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
            'maxResults' => $widget->getMaxResults(),

            'count' => $widget->getCount(),

            // display feature
            'display' => $widget->getDisplay(),
            'availableDisplays' => $widget->getAvailableDisplays(),

            // sort feature
            'sorting' => $widget->getSortBy(),
            'availableSort' => $widget->getAvailableSort(),

            // filter feature
            'filters' => $widget->getFilters(),
            'availableFilters' => $widget->getAvailableFilters(),

            // pagination feature
            'paginated' => $widget->isPaginated(),
            'pageSize' => $widget->getPageSize(),
            'availablePageSizes' => $widget->getAvailablePageSizes(),

            // table config
            'columns' => $widget->getDisplayedColumns(),
            'availableColumns' => $widget->getAvailableColumns(),

            // grid config (todo)
        ];
    }

    public function deserialize($data, ListWidget $widget, array $options = []): ListWidget
    {
        $this->sipe('maxResults', 'setMaxResults', $data, $widget);

        $this->sipe('count', 'setCount', $data, $widget);

        // display feature
        $this->sipe('display', 'setDisplay', $data, $widget);
        $this->sipe('availableDisplays', 'setAvailableDisplays', $data, $widget);

        // sort feature
        $this->sipe('sorting', 'setSortBy', $data, $widget);
        $this->sipe('availableSort', 'setAvailableSort', $data, $widget);

        // filter feature
        $this->sipe('filters', 'setFilters', $data, $widget);
        $this->sipe('availableFilters', 'setAvailableFilters', $data, $widget);

        // pagination feature
        $this->sipe('paginated', 'setPaginated', $data, $widget);
        $this->sipe('pageSize', 'setPageSize', $data, $widget);
        $this->sipe('availablePageSizes', 'setAvailablePageSizes', $data, $widget);

        // table config
        $this->sipe('columns', 'setDisplayedColumns', $data, $widget);
        $this->sipe('availableColumns', 'setAvailableColumns', $data, $widget);

        // grid config (todo)

        return $widget;
    }
}
