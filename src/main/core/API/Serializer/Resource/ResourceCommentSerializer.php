<?php

namespace Claroline\CoreBundle\API\Serializer\Resource;

use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\API\Serializer\User\UserSerializer;
use Claroline\CoreBundle\Entity\Resource\ResourceComment;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Library\Normalizer\DateNormalizer;

class ResourceCommentSerializer
{
    use SerializerTrait;

    /** @var UserSerializer */
    private $userSerializer;

    private $resourceNodeRepo;
    private $userRepo;

    public function __construct(ObjectManager $om, UserSerializer $userSerializer)
    {
        $this->userSerializer = $userSerializer;

        $this->resourceNodeRepo = $om->getRepository(ResourceNode::class);
        $this->userRepo = $om->getRepository(User::class);
    }

    public function getName()
    {
        return 'resource_comment';
    }

    public function serialize(ResourceComment $comment): array
    {
        return [
            'id' => $comment->getUuid(),
            'content' => $comment->getContent(),
            'user' => $comment->getUser() ? $this->userSerializer->serialize($comment->getUser(), [Options::SERIALIZE_MINIMAL]) : null,
            'creationDate' => DateNormalizer::normalize($comment->getCreationDate()),
            'editionDate' => DateNormalizer::normalize($comment->getEditionDate()),
        ];
    }

    public function deserialize(array $data, ResourceComment $comment): ResourceComment
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
