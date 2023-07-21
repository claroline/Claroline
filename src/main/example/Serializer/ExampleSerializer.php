<?php

namespace Claroline\ExampleBundle\Serializer;

use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\Serializer\SerializerInterface;
use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Claroline\CommunityBundle\Serializer\UserSerializer;
use Claroline\CoreBundle\Library\Normalizer\DateNormalizer;
use Claroline\ExampleBundle\Entity\Example;

class ExampleSerializer
{
    use SerializerTrait;

    private UserSerializer $userSerializer;

    public function __construct(UserSerializer $userSerializer)
    {
        $this->userSerializer = $userSerializer;
    }

    public function getClass(): string
    {
        return Example::class;
    }

    public function serialize(Example $example, ?array $options = []): array
    {
        if (in_array(SerializerInterface::SERIALIZE_MINIMAL, $options)) {
            return [
                'id' => $example->getUuid(),
                'name' => $example->getName(),
                'thumbnail' => $example->getThumbnail(),
            ];
        }

        return [
            'id' => $example->getUuid(),
            'autoId' => $example->getId(), // only exposed for debug purpose, should not be used (use id instead)
            'name' => $example->getName(),
            'thumbnail' => $example->getThumbnail(),
            'poster' => $example->getPoster(),
            'meta' => [
                'description' => $example->getDescription(),
                'createAt' => DateNormalizer::normalize($example->getCreatedAt()),
                'updatedAt' => DateNormalizer::normalize($example->getUpdatedAt()),
            ],
        ];
    }

    public function deserialize(array $data, Example $example, ?array $options = []): Example
    {
        if (!in_array(Options::REFRESH_UUID, $options)) {
            $this->sipe('id', 'setUuid', $data, $example);
        }

        $this->sipe('name', 'setName', $data, $example);
        $this->sipe('thumbnail', 'setThumbnail', $data, $example);
        $this->sipe('poster', 'setPoster', $data, $example);

        if (isset($data['meta'])) {
            $this->sipe('meta.description', 'setDescription', $data, $example);

            if (isset($data['meta']['createdAt'])) {
                $example->setCreatedAt(DateNormalizer::denormalize($data['meta']['createdAt']));
            }

            if (isset($data['meta']['updatedAt'])) {
                $example->setUpdatedAt(DateNormalizer::denormalize($data['meta']['updatedAt']));
            }
        }

        return $example;
    }
}
