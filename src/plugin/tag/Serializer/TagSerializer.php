<?php

namespace Claroline\TagBundle\Serializer;

use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\TagBundle\Entity\Tag;
use Claroline\TagBundle\Entity\TaggedObject;
use Claroline\TagBundle\Repository\TaggedObjectRepository;

class TagSerializer
{
    use SerializerTrait;

    /** @var ObjectManager */
    private $om;

    /** @var TaggedObjectRepository */
    private $taggedObjectRepo;

    public function __construct(ObjectManager $om)
    {
        $this->om = $om;

        $this->taggedObjectRepo = $om->getRepository(TaggedObject::class);
    }

    public function getName()
    {
        return 'tag';
    }

    public function getClass()
    {
        return Tag::class;
    }

    /**
     * Serializes a Tag entity into a serializable array.
     */
    public function serialize(Tag $tag, array $options = []): array
    {
        $serialized = [
            'id' => $tag->getUuid(),
            'name' => $tag->getName(),
            'color' => $tag->getColor(),
        ];

        if (!in_array(Options::SERIALIZE_MINIMAL, $options)) {
            $serialized = array_merge($serialized, [
                'meta' => [
                    'description' => $tag->getDescription(),
                ],
                'elements' => $this->taggedObjectRepo->countByTag($tag),
            ]);
        }

        return $serialized;
    }

    /**
     * Deserializes tag data into an Entity.
     */
    public function deserialize(array $data, Tag $tag, ?array $options): Tag
    {
        if (!in_array(Options::REFRESH_UUID, $options)) {
            $this->sipe('id', 'setUuid', $data, $tag);
        } else {
            $tag->refreshUuid();
        }

        $this->sipe('name', 'setName', $data, $tag);
        $this->sipe('color', 'setColor', $data, $tag);
        $this->sipe('meta.description', 'setDescription', $data, $tag);

        return $tag;
    }
}
