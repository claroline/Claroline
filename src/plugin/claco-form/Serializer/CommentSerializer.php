<?php

namespace Claroline\ClacoFormBundle\Serializer;

use Claroline\AppBundle\API\Options;
use Claroline\ClacoFormBundle\Entity\Comment;
use Claroline\CommunityBundle\Serializer\UserSerializer;

class CommentSerializer
{
    /** @var UserSerializer */
    private $userSerializer;

    /**
     * CommentSerializer constructor.
     */
    public function __construct(UserSerializer $userSerializer)
    {
        $this->userSerializer = $userSerializer;
    }

    public function getName()
    {
        return 'clacoform_comment';
    }

    /**
     * Serializes a Comment entity for the JSON api.
     *
     * @param Comment $comment - the comment to serialize
     * @param array   $options - a list of serialization options
     *
     * @return array - the serialized representation of the comment
     */
    public function serialize(Comment $comment, array $options = [])
    {
        $user = $comment->getUser();

        $serialized = [
            'id' => $comment->getUuid(),
            'content' => $comment->getContent(),
            'status' => $comment->getStatus(),
            'creationDate' => $comment->getCreationDate() ? $comment->getCreationDate()->format('Y-m-d H:i:s') : null,
            'editionDate' => $comment->getEditionDate() ? $comment->getEditionDate()->format('Y-m-d H:i:s') : null,
            'user' => $user ? $this->userSerializer->serialize($user, [Options::SERIALIZE_MINIMAL]) : null,
        ];

        return $serialized;
    }
}
