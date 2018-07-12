<?php

namespace Icap\BlogBundle\Serializer;

use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\API\Serializer\User\UserSerializer;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Library\Normalizer\DateNormalizer;
use Icap\BlogBundle\Entity\Comment;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.serializer.blog.comment")
 * @DI\Tag("claroline.serializer")
 */
class CommentSerializer
{
    use SerializerTrait;

    /**
     * serialize post comments.
     *
     * @var string
     */
    const INCLUDE_COMMENTS = 'includeComments';
    /**
     * fetch post comments from database, otherwise use provided comments array.
     *
     * @var string
     */
    const FETCH_COMMENTS = 'fetchComments';
    const PRELOADED_COMMENTS = 'loadComments';

    private $userSerializer;
    private $userRepo;
    private $om;

    /**
     * PostSerializer constructor.
     *
     * @DI\InjectParams({
     *     "userSerializer"       = @DI\Inject("claroline.serializer.user"),
     *     "om"                   = @DI\Inject("claroline.persistence.object_manager")
     * })
     *
     * @param UserSerializer $userSerializer
     * @param ObjectManager  $om
     */
    public function __construct(
        UserSerializer $userSerializer,
        ObjectManager $om
        ) {
        $this->userSerializer = $userSerializer;
        $this->userRepo = $om->getRepository('Claroline\CoreBundle\Entity\User');
        $this->tagRepo = $om->getRepository('Icap\BlogBundle\Entity\Tag');
        $this->om = $om;
    }

    /**
     * @return string
     */
    public function getClass()
    {
        return 'Icap\BlogBundle\Entity\Comment';
    }

    /**
     * @return string
     */
    public function getSchema()
    {
        return '#/plugin/blog/comment.json';
    }

    /**
     * Serialize post comments.
     *
     * @param array $comments
     * @param array $options
     *
     * @return array - The serialized representation comments
     */
    public function serializeComments(array $comments, array $options = [])
    {
        foreach ($comments as $comment) {
            $comments[] = $this->serialize($comment);
        }

        return $comments;
    }

    /**
     * Serialize a post comment.
     *
     * @param Comment $comment
     * @param array   $options
     *
     * @return array - The serialized representation of a comment
     */
    public function serialize(Comment $comment, array $options = [])
    {
        return [
            'id' => $comment->getUuid(),
            'message' => $comment->getMessage(),
            'creationDate' => $comment->getCreationDate() ? DateNormalizer::normalize($comment->getCreationDate()) : null,
            'updateDate' => $comment->getUpdateDate() ? DateNormalizer::normalize($comment->getUpdateDate()) : null,
            'publicationDate' => $comment->getPublicationDate() ? DateNormalizer::normalize($comment->getPublicationDate()) : null,
            'author' => $comment->getAuthor() ? $this->userSerializer->serialize($comment->getAuthor()) : null,
            'authorName' => null !== $comment->getAuthor() ? $comment->getAuthor()->getFullName() : null,
            'authorPicture' => null !== $comment->getAuthor() ? $comment->getAuthor()->getPicture() : null,
            'isPublished' => $comment->isPublished(),
            'reported' => $comment->getReported(),
        ];
    }

    /**
     * @param array          $data
     * @param Comment | null $comment
     * @param array          $options
     *
     * @return Comment - The deserialized comment entity
     */
    public function deserialize($data, Comment $comment = null, User $user = null, array $options = [])
    {
        if (empty($comment)) {
            $comment = new Comment();
        }
        $this->sipe('id', 'setUuid', $data, $comment);
        if (isset($data['message'])) {
            $comment->setMessage($data['message']);
        }

        if (isset($data['creationDate'])) {
            $comment->setCreationDate(DateNormalizer::denormalize($data['creationDate']));
        } else {
            $comment->setCreationDate(new \DateTime());
        }

        if (isset($data['isPublished'])) {
            $comment->publish();
            if (isset($data['publicationDate'])) {
                $comment->setPublicationDate(DateNormalizer::denormalize($data['publicationDate']));
            } else {
                $comment->setPublicationDate(new \DateTime());
            }
        }

        if (isset($data['updateDate'])) {
            $comment->setUpdateDate(DateNormalizer::denormalize($data['updateDate']));
        }

        if (isset($data['reported'])) {
            $comment->setReported($data['reported']);
        }

        if ($user) {
            $comment->setAuthor($user);
        } elseif (isset($data['user'])) {
            $user = isset($data['user']['id']) ? $this->userRepo->findOneBy(['id' => $data['user']['id']]) : null;
            $comment->setAuthor($user);
        }

        return $comment;
    }
}
