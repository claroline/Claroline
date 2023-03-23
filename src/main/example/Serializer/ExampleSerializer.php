<?php

namespace Claroline\ExampleBundle\Serializer;

use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Claroline\CoreBundle\Library\Normalizer\DateNormalizer;
use Claroline\ExampleBundle\Entity\Example;

class ExampleSerializer
{
    use SerializerTrait;

    public function getClass(): string
    {
        return Example::class;
    }

    public function serialize(Example $example, ?array $options = []): array
    {
        return [
            'id' => $example->getUuid(),
            'autoId' => $example->getId(),
            'name' => $example->getName(),
            'meta' => [
                'description' => $example->getDescription(),
                'createAt' => DateNormalizer::normalize($example->getCreatedAt()),
                'updatedAt' => DateNormalizer::normalize($example->getUpdatedAt()),
            ],
        ];
    }

    public function deserialize(array $data, Example $example, ?array $options = []): Example
    {
        $this->sipe('name', 'setName', $data, $example);

        if (isset($data['meta'])) {
            $this->sipe('meta.description', 'setDescription', $data, $example);

            /*$example->setCreatedAt(DateNormalizer::denormalize($data['meta']['createdAt']));
            $example->setUpdatedAt(DateNormalizer::denormalize($data['meta']['updatedAt']));*/
        }

        return $example;
    }
}
