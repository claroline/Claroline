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
                'showSummary' => true,
                'defaultDisplay' => 'grid-sm',
                'availableDisplays' => ['grid-sm', 'table'],
            ]
        ];
    }

    /**
     * Deserializes directory data into an Entity.
     *
     * @param array $data
     * @param Directory $directory
     *
     * @return Text
     */
    public function deserialize(array $data, Directory $directory)
    {

    }
}
