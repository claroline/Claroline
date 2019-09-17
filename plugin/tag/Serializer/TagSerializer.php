<?php

namespace Claroline\TagBundle\Serializer;

use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\API\Serializer\User\UserSerializer;
use Claroline\CoreBundle\Entity\User;
use Claroline\TagBundle\Entity\Tag;
use Claroline\TagBundle\Entity\TaggedObject;
use Claroline\TagBundle\Repository\TaggedObjectRepository;

class TagSerializer
{
    use SerializerTrait;

    /** @var ObjectManager */
    private $om;

    /** @var UserSerializer */
    private $userSerializer;

    /** @var TaggedObjectRepository */
    private $taggedObjectRepo;

    /**
     * TagSerializer constructor.
     *
     * @param ObjectManager  $om
     * @param UserSerializer $userSerializer
     */
    public function __construct(
        ObjectManager $om,
        UserSerializer $userSerializer)
    {
        $this->om = $om;
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

        if (isset($data['meta']) && isset($data['meta']['creator'])) {
            $user = $this->om->getRepository(User::class)->findBy(['uuid' => $data['meta']['creator']['id']]);
            if ($user) {
                $tag->setUser($user);
            }
        }

        return $tag;
    }
}
