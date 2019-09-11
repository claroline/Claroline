<?php

namespace Claroline\CoreBundle\API\Serializer\Widget\Type;

use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Claroline\CoreBundle\Entity\Widget\Type\ListWidget;

class ListWidgetSerializer
{
    use SerializerTrait;

    public function getClass()
    {
        return ListWidget::class;
    }

    public function serialize(ListWidget $widget, array $options = []): array
    {
        // todo : find a way to merge with directory serializer
        return [
            'maxResults' => $widget->getMaxResults(),

            'actions' => $widget->hasActions(),
            'count' => $widget->hasCount(),

            // display feature
            'display' => $widget->getDisplay(),
            'availableDisplays' => $widget->getAvailableDisplays(),

            // sort feature
            'sorting' => $widget->getSortBy(),
            'availableSort' => $widget->getAvailableSort(),

            // filter feature
            'searchMode' => $widget->getSearchMode(),
            'filters' => $widget->getFilters(),
            'availableFilters' => $widget->getAvailableFilters(),

            // pagination feature
            'paginated' => $widget->isPaginated(),
            'pageSize' => $widget->getPageSize(),
            'availablePageSizes' => $widget->getAvailablePageSizes(),

            // table config
            'columns' => $widget->getDisplayedColumns(),
            'availableColumns' => $widget->getAvailableColumns(),

            // grid config
            'card' => [
                'display' => $widget->getCard(),
                'mapping' => [], // TODO
            ],
        ];
    }

    public function deserialize($data, ListWidget $widget, array $options = []): ListWidget
    {
        $this->sipe('maxResults', 'setMaxResults', $data, $widget);

        // todo : find a way to merge with directory serializer
        $this->sipe('count', 'setCount', $data, $widget);
        $this->sipe('actions', 'setActions', $data, $widget);

        // display feature
        $this->sipe('display', 'setDisplay', $data, $widget);
        $this->sipe('availableDisplays', 'setAvailableDisplays', $data, $widget);

        // sort feature
        $this->sipe('sorting', 'setSortBy', $data, $widget);
        $this->sipe('availableSort', 'setAvailableSort', $data, $widget);

        // filter feature
        $this->sipe('searchMode', 'setSearchMode', $data, $widget);
        $this->sipe('filters', 'setFilters', $data, $widget);
        $this->sipe('availableFilters', 'setAvailableFilters', $data, $widget);

        // pagination feature
        $this->sipe('paginated', 'setPaginated', $data, $widget);
        $this->sipe('pageSize', 'setPageSize', $data, $widget);
        $this->sipe('availablePageSizes', 'setAvailablePageSizes', $data, $widget);

        // table config
        $this->sipe('columns', 'setDisplayedColumns', $data, $widget);
        $this->sipe('availableColumns', 'setAvailableColumns', $data, $widget);

        // grid config
        $this->sipe('card.display', 'setCard', $data, $widget);

        return $widget;
    }
}
