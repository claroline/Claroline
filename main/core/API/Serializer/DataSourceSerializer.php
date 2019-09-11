<?php

namespace Claroline\CoreBundle\API\Serializer;

use Claroline\CoreBundle\Entity\DataSource;

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
