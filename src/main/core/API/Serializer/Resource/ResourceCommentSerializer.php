<?php

namespace Claroline\CoreBundle\API\Serializer\Resource;

use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Claroline\AppBundle\API\SerializerProvider;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\API\Serializer\User\UserSerializer;
use Claroline\CoreBundle\Entity\Resource\ResourceComment;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Library\Normalizer\DateNormalizer;

class ResourceCommentSerializer
{
    use SerializerTrait;

    private $resourceNodeRepo;
    private $userRepo;

    /**
     * ResourceCommentSerializer constructor.
     *
     * @param SerializerProvider $serializer
     */
    public function __construct(ObjectManager $om, UserSerializer $userSerializer)
    {
        $this->resourceNodeRepo = $om->getRepository(ResourceNode::class);
        $this->userRepo = $om->getRepository(User::class);
        $this->userSerializer = $userSerializer;
    }

    public function getName()
    {
        return 'resource_commment';
    }

    /**
     * Serializes a ResourceComment entity for the JSON api.
     *
     * @param ResourceComment $comment - the comment to serialize
     *
     * @return array - the serialized representation of the comment
     */
    public function serialize(ResourceComment $comment, array $options = [])
    {
        $serialized = [
            'id' => $comment->getUuid(),
            'content' => $comment->getContent(),
            'user' => $comment->getUser() ? $this->userSerializer->serialize($comment->getUser(), [Options::SERIALIZE_MINIMAL]) : null,
            'creationDate' => $comment->getCreationDate() ? DateNormalizer::normalize($comment->getCreationDate()) : null,
            'editionDate' => $comment->getEditionDate() ? DateNormalizer::normalize($comment->getEditionDate()) : null,
        ];

        return $serialized;
    }

    /**
     * @param array $data
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
