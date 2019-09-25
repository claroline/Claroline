<?php

namespace Icap\BlogBundle\Serializer;

use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\API\Serializer\Resource\ResourceNodeSerializer;
use Claroline\CoreBundle\API\Serializer\User\UserSerializer;
use Claroline\CoreBundle\Event\GenericDataEvent;
use Claroline\CoreBundle\Library\Normalizer\DateNormalizer;
use Icap\BlogBundle\Entity\Post;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class PostSerializer
{
    use SerializerTrait;

    private $userSerializer;
    private $commentSerializer;
    private $userRepo;
    private $tagRepo;
    private $om;
    private $eventDispatcher;
    private $nodeSerializer;

    /**
     * PostSerializer constructor.
     *
     * @param UserSerializer           $userSerializer
     * @param CommentSerializer        $commentSerializer
     * @param ObjectManager            $om
     * @param EventDispatcherInterface $eventDispatcher
     * @param ResourceNodeSerializer   $nodeSerializer
     */
    public function __construct(
        UserSerializer $userSerializer,
        CommentSerializer $commentSerializer,
        ObjectManager $om,
        EventDispatcherInterface $eventDispatcher,
        ResourceNodeSerializer $nodeSerializer
    ) {
        $this->userSerializer = $userSerializer;
        $this->commentSerializer = $commentSerializer;
        $this->userRepo = $om->getRepository('Claroline\CoreBundle\Entity\User');
        $this->tagRepo = $om->getRepository('Icap\BlogBundle\Entity\Tag');
        $this->om = $om;
        $this->eventDispatcher = $eventDispatcher;
        $this->nodeSerializer = $nodeSerializer;
    }

    /**
     * @return string
     */
    public function getClass()
    {
        return Post::class;
    }

    /**
     * @return string
     */
    public function getSchema()
    {
        return '#/plugin/blog/post.json';
    }

    /**
     * Checks if an option has been passed to the serializer.
     *
     * @param $option
     * @param array $options
     *
     * @return bool
     */
    private function hasOption($option, array $options = [])
    {
        return in_array($option, $options);
    }

    /**
     * @param Post  $post
     * @param array $comments
     * @param array $options
     *
     * @return array - The serialized representation of a post
     */
    public function serialize(Post $post, array $options = [], array $comments = [])
    {
        //serialize comments
        if (isset($options[CommentSerializer::INCLUDE_COMMENTS])) {
            if ($this->hasOption(CommentSerializer::FETCH_COMMENTS, $options)) {
                $comments = $this->serializePostComments($post, $options);
            }
        }
        $commentsNumber = $post->countComments();
        $commentsNumberUnpublished = $post->countUnpublishedComments();

        return [
            'id' => $post->getUuid(),
            'slug' => $post->getSlug(),
            'title' => $post->getTitle(),
            'content' => isset($options['abstract']) && $options['abstract'] ? $post->getAbstract() : $post->getContent(),
            'abstract' => $this->isAbstract($post, $options),
            'meta' => [
                'resource' => $post->getBlog() && $post->getBlog()->getResourceNode() ?
                    $this->nodeSerializer->serialize($post->getBlog()->getResourceNode(), [Options::SERIALIZE_MINIMAL])
                    :
                    null,
            ],
            'creationDate' => $post->getCreationDate() ? DateNormalizer::normalize($post->getCreationDate()) : new \DateTime(),
            'modificationDate' => $post->getModificationDate() ? DateNormalizer::normalize($post->getModificationDate()) : null,
            'publicationDate' => $post->getPublicationDate() ? DateNormalizer::normalize($post->getPublicationDate()) : DateNormalizer::normalize($post->getCreationDate()),
            'viewCounter' => $post->getViewCounter(),
            'author' => $post->getAuthor() ? $this->userSerializer->serialize($post->getAuthor(), [Options::SERIALIZE_MINIMAL]) : null,
            'authorName' => $post->getAuthor() ? $post->getAuthor()->getFullName() : null,
            'authorPicture' => $post->getAuthor()->getPicture(),
            'tags' => $this->serializeTags($post),
            'comments' => $comments,
            'commentsNumber' => $commentsNumber,
            'commentsNumberUnpublished' => $commentsNumberUnpublished,
            'isPublished' => $post->isPublished(),
            'status' => $post->isPublished(false),
            'pinned' => $post->isPinned(),
        ];
    }

    /**
     * @param Post  $post
     * @param array $options
     *
     * @return bool - Check if post content is truncated
     */
    private function isAbstract(Post $post, array $options = [])
    {
        if (isset($options['abstract']) && $options['abstract']) {
            if ($post->getAbstract() !== $post->getContent()) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param array       $data
     * @param Post | null $post
     * @param array       $options
     *
     * @return Post - The deserialized post entity
     */
    public function deserialize($data, Post $post = null, array $options = [])
    {
        if (empty($post)) {
            $post = new Post();
        }

        if (!in_array(Options::REFRESH_UUID, $options)) {
            $this->sipe('id', 'setUuid', $data, $post);
        }

        if (isset($data['title'])) {
            $post->setTitle($data['title']);
        }
        if (isset($data['content'])) {
            $post->setContent($data['content']);
        }
        if (isset($data['creationDate'])) {
            $post->setCreationDate(DateNormalizer::denormalize($data['creationDate']));
        }
        if (isset($data['publicationDate'])) {
            $post->setPublicationDate(DateNormalizer::denormalize($data['publicationDate']));
        }
        if (isset($data['modificationDate'])) {
            $post->setModificationDate(DateNormalizer::denormalize($data['modificationDate']));
        }
        if (isset($data['viewCounter'])) {
            $post->setViewCounter($data['viewCounter']);
        }
        if (isset($data['user'])) {
            $user = isset($data['user']['id']) ? $this->userRepo->findOneBy(['id' => $data['user']['id']]) : null;
            $post->setAuthor($user);
        }
        if (isset($data['tags'])) {
            $this->deserializeTags($post, $data['tags']);
        }
        if (isset($data['published']) && true === $data['published']) {
            $post->publish();
        }
        if (isset($data['commentModerationMode'])) {
            $post->setCommentModerationMode($data['commentModerationMode']);
        }

        return $post;
    }

    /**
     * Serialize post comments.
     *
     * @param Post  $post
     * @param array $options
     *
     * @return array - The serialized representation comments
     */
    public function serializePostComments(Post $post, array $options = [])
    {
        $comments = [];
        foreach ($post->getComments() as $comment) {
            $comments[] = $this->commentSerializer->serialize($comment);
        }

        return $comments;
    }

    /**
     * Serializes Item tags.
     * Forwards the tag serialization to ItemTagSerializer.
     *
     * @param post $post
     *
     * @return array
     */
    public function serializeTags(Post $post)
    {
        $event = new GenericDataEvent([
            'class' => 'Icap\BlogBundle\Entity\Post',
            'ids' => [$post->getUuid()],
        ]);
        $this->eventDispatcher->dispatch('claroline_retrieve_used_tags_by_class_and_ids', $event);

        return implode(', ', $event->getResponse());
    }

    /**
     * Deserializes Item tags.
     *
     * @param Post   $post
     * @param string $tags
     * @param array  $options
     */
    public function deserializeTags(Post $post, $tags, array $options = [])
    {
        $array = array_map('trim', explode(',', $tags));

        $event = new GenericDataEvent([
            'tags' => $array,
            'data' => [
                [
                    'class' => 'Icap\BlogBundle\Entity\Post',
                    'id' => $post->getUuid(),
                    'name' => $post->getTitle(),
                ],
            ],
            'replace' => true,
        ]);

        $this->eventDispatcher->dispatch('claroline_tag_multiple_data', $event);
    }
}
