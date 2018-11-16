<?php

namespace Claroline\TagBundle\Serializer;

use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\API\Serializer\User\UserSerializer;
use Claroline\TagBundle\Entity\Tag;
use Claroline\TagBundle\Entity\TaggedObject;
use Claroline\TagBundle\Repository\TaggedObjectRepository;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.serializer.tag")
 * @DI\Tag("claroline.serializer")
 */
class TagSerializer
{
    use SerializerTrait;

    /** @var UserSerializer */
    private $userSerializer;

    /** @var TaggedObjectRepository */
    private $taggedObjectRepo;

    /**
     * TagSerializer constructor.
     *
     * @DI\InjectParams({
     *     "om"             = @DI\Inject("claroline.persistence.object_manager"),
     *     "userSerializer" = @DI\Inject("claroline.serializer.user")
     * })
     *
     * @param ObjectManager  $om
     * @param UserSerializer $userSerializer
     */
    public function __construct(
        ObjectManager $om,
        UserSerializer $userSerializer)
    {
        $this->userSerializer = $userSerializer;
        $this->taggedObjectRepo = $om->getRepository(TaggedObject::class);
    }

    public function getClass()
    {
        return Tag::class;
    }

    /**
     * Serializes a Tag entity into a serializable array.
     *
     * @param Tag   $tag
     * @param array $options
     *
     * @return array
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
                    'creator' => $tag->getUser() ?
                        $this->userSerializer->serialize($tag->getUser(), [Options::SERIALIZE_MINIMAL]) :
                        null,
                ],
                'elements' => $this->taggedObjectRepo->countByTag($tag),
            ]);
        }

        return $serialized;
    }

    /**
     * Deserializes tag data into an Entity.
     *
     * @param array $data
     * @param Tag   $tag
     *
     * @return Tag
     */
    public function deserialize(array $data, Tag $tag): Tag
    {
        $this->sipe('name', 'setName', $data, $tag);
        $this->sipe('color', 'setColor', $data, $tag);
        $this->sipe('meta.description', 'setDescription', $data, $tag);

        return $tag;
    }
}
