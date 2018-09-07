<?php

namespace Claroline\CoreBundle\API\Serializer\Resource\Types;

use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Claroline\CoreBundle\Entity\Resource\Directory;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.serializer.resource_directory")
 * @DI\Tag("claroline.serializer")
 */
class DirectorySerializer
{
    use SerializerTrait;

    /**
     * Serializes a Directory entity for the JSON api.
     *
     * @param Directory $directory
     *
     * @return array
     */
    public function serialize(Directory $directory): array
    {
        return [
            'id' => $directory->getId(),
            'uploadDestination' => $directory->isUploadDestination(),
            'display' => [
                'showSummary' => $directory->getShowSummary(),
                'openSummary' => $directory->getOpenSummary(),
            ],

            // resource list config
            // todo : big c/c from Claroline\CoreBundle\API\Serializer\Widget\Type\ListWidgetSerializer
            'list' => [
                'count' => $directory->getCount(),
                // display feature
                'display' => $directory->getDisplay(),
                'availableDisplays' => $directory->getAvailableDisplays(),

                // sort feature
                'sorting' => $directory->getSortBy(),
                'availableSort' => $directory->getAvailableSort(),

                // filter feature
                'filters' => $directory->getFilters(),
                'availableFilters' => $directory->getAvailableFilters(),

                // pagination feature
                'paginated' => $directory->isPaginated(),
                'pageSize' => $directory->getPageSize(),
                'availablePageSizes' => $directory->getAvailablePageSizes(),

                // table config
                'columns' => $directory->getDisplayedColumns(),
                'availableColumns' => $directory->getAvailableColumns(),

                // grid config (todo)
            ],
        ];
    }

    /**
     * Deserializes directory data into an Entity.
     *
     * @param array     $data
     * @param Directory $directory
     *
     * @return Directory
     */
    public function deserialize(array $data, Directory $directory): Directory
    {
        $this->sipe('uploadDestination', 'setUploadDestination', $data, $directory);

        $this->sipe('display.showSummary', 'setShowSummary', $data, $directory);
        $this->sipe('display.openSummary', 'setOpenSummary', $data, $directory);

        // resource list config
        // todo : big c/c from Claroline\CoreBundle\API\Serializer\Widget\Type\ListWidgetSerializer
        $this->sipe('list.count', 'setCount', $data, $directory);

        // display feature
        $this->sipe('list.display', 'setDisplay', $data, $directory);
        $this->sipe('list.availableDisplays', 'setAvailableDisplays', $data, $directory);

        // sort feature
        $this->sipe('list.sorting', 'setSortBy', $data, $directory);
        $this->sipe('list.availableSort', 'setAvailableSort', $data, $directory);

        // filter feature
        $this->sipe('list.filters', 'setFilters', $data, $directory);
        $this->sipe('list.availableFilters', 'setAvailableFilters', $data, $directory);

        // pagination feature
        $this->sipe('list.paginated', 'setPaginated', $data, $directory);
        $this->sipe('list.pageSize', 'setPageSize', $data, $directory);
        $this->sipe('list.availablePageSizes', 'setAvailablePageSizes', $data, $directory);

        // table config
        $this->sipe('list.columns', 'setDisplayedColumns', $data, $directory);
        $this->sipe('list.availableColumns', 'setAvailableColumns', $data, $directory);

        return $directory;
    }
}
