<?php

namespace Claroline\ClacoFormBundle\Serializer;

use Claroline\AppBundle\API\Serializer\SerializerInterface;
use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Claroline\ClacoFormBundle\Entity\Keyword;

class KeywordSerializer
{
    use SerializerTrait;

    public function getName(): string
    {
        return 'clacoform_keyword';
    }

    public function getClass(): string
    {
        return Keyword::class;
    }

    public function serialize(Keyword $keyword, array $options = []): array
    {
        return [
            'id' => $keyword->getUuid(),
            'name' => $keyword->getName(),
        ];
    }

    public function deserialize(array $data, Keyword $keyword, array $options = []): Keyword
    {
        if (!in_array(SerializerInterface::REFRESH_UUID, $options)) {
            $this->sipe('id', 'setUuid', $data, $keyword);
        } else {
            $keyword->refreshUuid();
        }

        $this->sipe('name', 'setName', $data, $keyword);

        return $keyword;
    }
}
