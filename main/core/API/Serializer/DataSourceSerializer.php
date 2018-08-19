<?php

namespace Claroline\CoreBundle\API\Serializer;

use Claroline\CoreBundle\Entity\DataSource;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.serializer.data_source")
 * @DI\Tag("claroline.serializer")
 */
class DataSourceSerializer
{
    public function getClass()
    {
        return DataSource::class;
    }

    public function serialize(DataSource $dataSource): array
    {
        return [
            'id' => $dataSource->getUuid(),
            'name' => $dataSource->getName(),
            'meta' => [
                'context' => $dataSource->getContext(),
            ],
            'tags' => $dataSource->getTags(),
        ];
    }
}
