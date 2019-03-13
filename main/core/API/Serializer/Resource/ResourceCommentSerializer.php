<?php

namespace Claroline\CoreBundle\API\Serializer\Resource;

use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Claroline\AppBundle\API\SerializerProvider;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Resource\ResourceComment;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Library\Normalizer\DateNormalizer;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.serializer.resource_node.comment")
 * @DI\Tag("claroline.serializer")
 */
class ResourceCommentSerializer
{
    use SerializerTrait;

    /** @var SerializerProvider */
    private $serializer;

    private $resourceNodeRepo;
    private $userRepo;

    /**
     * ResourceCommentSerializer constructor.
     *
     * @DI\InjectParams({
     *     "om"         = @DI\Inject("claroline.persistence.object_manager"),
     *     "serializer" = @DI\Inject("claroline.api.serializer")
     * })
     *
     * @param ObjectManager      $om
     * @param SerializerProvider $serializer
     */
    public function __construct(ObjectManager $om, SerializerProvider $serializer)
    {
        $this->serializer = $serializer;

        $this->resourceNodeRepo = $om->getRepository(ResourceNode::class);
        $this->userRepo = $om->getRepository(User::class);
    }

    /**
     * Serializes a ResourceComment entity for the JSON api.
     *
     * @param ResourceComment $comment - the comment to serialize
     * @param array           $options
     *
     * @return array - the serialized representation of the comment
     */
    public function serialize(ResourceComment $comment, array $options = [])
    {
        $serialized = [
            'id' => $comment->getUuid(),
            'content' => $comment->getContent(),
            'user' => $comment->getUser() ? $this->serializer->serialize($comment->getUser(), [Options::SERIALIZE_MINIMAL]) : null,
            'creationDate' => $comment->getCreationDate() ? DateNormalizer::normalize($comment->getCreationDate()) : null,
            'editionDate' => $comment->getEditionDate() ? DateNormalizer::normalize($comment->getEditionDate()) : null,
        ];

        return $serialized;
    }

    /**
     * @param array           $data
     * @param ResourceComment $comment
     *
     * @return ResourceComment $comment
     */
    public function deserialize($data, ResourceComment $comment)
    {
        $this->sipe('id', 'setUuid', $data, $comment);
        $this->sipe('content', 'setContent', $data, $comment);

        if (isset($data['user']['id']) && !$comment->getUser()) {
            $user = $this->userRepo->findOneBy(['uuid' => $data['user']['id']]);

            if ($user) {
                $comment->setUser($user);
            }
        }
        if (isset($data['resourceNode']['id']) && !$comment->getResourceNode()) {
            $resourceNode = $this->resourceNodeRepo->findOneBy(['uuid' => $data['resourceNode']['id']]);

            if ($resourceNode) {
                $comment->setResourceNode($resourceNode);
            }
        }

        return $comment;
    }
}
