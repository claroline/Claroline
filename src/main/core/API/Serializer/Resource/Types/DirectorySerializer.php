<?php

namespace Claroline\CoreBundle\API\Serializer\Resource\Types;

use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Claroline\CoreBundle\Entity\Resource\Directory;

class DirectorySerializer
{
    use SerializerTrait;

    /**
     * Serializes a Directory entity for the JSON api.
     */
    public function serialize(Directory $directory): array
    {
        return [
            'id' => $directory->getUuid(),
            'uploadDestination' => $directory->isUploadDestination(),

            // resource list config
            // todo : big c/c from Claroline\CoreBundle\API\Serializer\Widget\Type\ListWidgetSerializer
            'list' => [
                'actions' => $directory->hasActions(),
                'count' => $directory->hasCount(),
                // display feature
                'display' => $directory->getDisplay(),
                'availableDisplays' => $directory->getAvailableDisplays(),

                // sort feature
                'sorting' => $directory->getSortBy(),
                'availableSort' => $directory->getAvailableSort(),

                // filter feature
                'searchMode' => $directory->getSearchMode(),
                'filters' => $directory->getFilters(),
                'availableFilters' => $directory->getAvailableFilters(),

                // pagination feature
                'paginated' => $directory->isPaginated(),
                'pageSize' => $directory->getPageSize(),
                'availablePageSizes' => $directory->getAvailablePageSizes(),

                // table config
                'columns' => $directory->getDisplayedColumns(),
                'availableColumns' => $directory->getAvailableColumns(),

                // grid config
                'card' => [
                    'display' => $directory->getCard(),
                    'mapping' => [], // TODO
                ],
            ],
        ];
    }

    public function getName()
    {
        return 'directory';
    }

    /**
     * Deserializes directory data into an Entity.
     */
    public function deserialize(array $data, Directory $directory, array $options = []): Directory
    {
        if (!in_array(Options::REFRESH_UUID, $options)) {
            $this->sipe('id', 'setUuid', $data, $directory);
        } else {
            $directory->refreshUuid();
        }

        $this->sipe('uploadDestination', 'setUploadDestination', $data, $directory);

        // resource list config
        // todo : big c/c from Claroline\CoreBundle\API\Serializer\Widget\Type\ListWidgetSerializer
        $this->sipe('list.count', 'setCount', $data, $directory);
        $this->sipe('list.actions', 'setActions', $data, $directory);

        // display feature
        $this->sipe('list.display', 'setDisplay', $data, $directory);
        $this->sipe('list.availableDisplays', 'setAvailableDisplays', $data, $directory);

        // sort feature
        $this->sipe('list.sorting', 'setSortBy', $data, $directory);
        $this->sipe('list.availableSort', 'setAvailableSort', $data, $directory);

        // filter feature
        $this->sipe('list.searchMode', 'setSearchMode', $data, $directory);
        $this->sipe('list.filters', 'setFilters', $data, $directory);
        $this->sipe('list.availableFilters', 'setAvailableFilters', $data, $directory);

        // pagination feature
        $this->sipe('list.paginated', 'setPaginated', $data, $directory);
        $this->sipe('list.pageSize', 'setPageSize', $data, $directory);
        $this->sipe('list.availablePageSizes', 'setAvailablePageSizes', $data, $directory);

        // table config
        $this->sipe('list.columns', 'setDisplayedColumns', $data, $directory);
        $this->sipe('list.availableColumns', 'setAvailableColumns', $data, $directory);

        // grid config
        $this->sipe('list.card.display', 'setCard', $data, $directory);

        return $directory;
    }

    /**
     * @return string
     */
    public function getSchema()
    {
        return '#/main/core/resource/types/directory.json';
    }
}
