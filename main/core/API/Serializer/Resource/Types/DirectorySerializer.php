<?php

namespace Claroline\CoreBundle\API\Serializer\Resource\Types;

use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Claroline\CoreBundle\Entity\Resource\Directory;
use Claroline\CoreBundle\Entity\Resource\Text;
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
    public function serialize(Directory $directory)
    {
        return [
            'display' => [
                'showSummary' => $directory->getShowSummary(),
                'openSummary' => $directory->getOpenSummary(),
            ],
            'list' => [
                'columns' => [
                    'default' => $directory->getDisplayedColumns(),
                    'available' => $directory->getAvailableColumns(),
                ],

                'card' => [],

                'display' => [
                    'default' => $directory->getDisplay(),
                    'available' => $directory->getAvailableDisplays(),
                ],

                'filters' => [
                    'enabled' => $directory->isFilterable(),
                    'default' => $directory->getFilters(),
                ],

                'pagination' => [
                    'enabled' => $directory->isPaginated(),
                    'default' => $directory->getPageSize(),
                ],

                'sorting' => [
                    'enabled' => $directory->isSortable(),
                    'default' => $directory->getSortBy(),
                ],
            ],
        ];
    }

    /**
     * Deserializes directory data into an Entity.
     *
     * @param array     $data
     * @param Directory $directory
     *
     * @return Text
     */
    public function deserialize(array $data, Directory $directory)
    {
    }
}
