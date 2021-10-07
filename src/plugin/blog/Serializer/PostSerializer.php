<?php

namespace Icap\BlogBundle\Serializer;

use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\API\Serializer\File\PublicFileSerializer;
use Claroline\CoreBundle\API\Serializer\Resource\ResourceNodeSerializer;
use Claroline\CoreBundle\API\Serializer\User\UserSerializer;
use Claroline\CoreBundle\Entity\File\PublicFile;
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
    private $om;
    private $eventDispatcher;
    private $nodeSerializer;
    private $publicFileSerializer;

    public function __construct(
        UserSerializer $userSerializer,
        CommentSerializer $commentSerializer,
        ObjectManager $om,
        EventDispatcherInterface $eventDispatcher,
        ResourceNodeSerializer $nodeSerializer,
        PublicFileSerializer $publicFileSerializer
    ) {
        $this->userSerializer = $userSerializer;
        $this->commentSerializer = $commentSerializer;
        $this->userRepo = $om->getRepository('Claroline\CoreBundle\Entity\User');
        $this->om = $om;
        $this->eventDispatcher = $eventDispatcher;
        $this->nodeSerializer = $nodeSerializer;
        $this->publicFileSerializer = $publicFileSerializer;
    }

    public function getName()
    {
        return 'blog_post';
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
     *
     * @return bool
     */
    private function hasOption($option, array $options = [])
    {
        return in_array($option, $options);
    }

    public function serialize(Post $post, array $options = [], array $comments = []): array
    {
        $thumbnail = null;
        if ($post->getThumbnail()) {
            /** @var PublicFile $thumbnail */
            $thumbnail = $this->om->getRepository(PublicFile::class)->findOneBy([
                'url' => $post->getThumbnail(),
            ]);
        }

        $poster = null;
        if ($post->getPoster()) {
            /** @var PublicFile $poster */
            $poster = $this->om->getRepository(PublicFile::class)->findOneBy([
                'url' => $post->getPoster(),
            ]);
        }

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
            'thumbnail' => $thumbnail ? $this->publicFileSerializer->serialize($thumbnail) : null,
            'poster' => $poster ? $this->publicFileSerializer->serialize($poster) : null,
            'content' => isset($options['abstract']) && $options['abstract'] ? $post->getAbstract() : $post->getContent(),
            'abstract' => $this->isAbstract($post, $options),
            'meta' => [
                'creator' => $post->getCreator() ? $this->userSerializer->serialize($post->getCreator(), [Options::SERIALIZE_MINIMAL]) : null,
                'author' => $post->getAuthor(),
                'resource' => $post->getBlog() && $post->getBlog()->getResourceNode() ?
                    $this->nodeSerializer->serialize($post->getBlog()->getResourceNode(), [Options::SERIALIZE_MINIMAL])
                    :
                    null,
            ],
            'creationDate' => DateNormalizer::normalize($post->getCreationDate()),
            'modificationDate' => DateNormalizer::normalize($post->getModificationDate()),
            'publicationDate' => DateNormalizer::normalize($post->getPublicationDate()),
            'viewCounter' => $post->getViewCounter(),
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

    public function deserialize(array $data, Post $post = null, array $options = []): Post
    {
        if (empty($post)) {
            $post = new Post();
        }

        if (!in_array(Options::REFRESH_UUID, $options)) {
            $this->sipe('id', 'setUuid', $data, $post);
        }

        $this->sipe('title', 'setTitle', $data, $post);
        $this->sipe('content', 'setContent', $data, $post);
        $this->sipe('viewCounter', 'setViewCounter', $data, $post);
        $this->sipe('meta.author', 'setAuthor', $data, $post);

        if (isset($data['creationDate'])) {
            $post->setCreationDate(DateNormalizer::denormalize($data['creationDate']));
        }
        if (isset($data['publicationDate'])) {
            $post->setPublicationDate(DateNormalizer::denormalize($data['publicationDate']));
        }
        if (isset($data['modificationDate'])) {
            $post->setModificationDate(DateNormalizer::denormalize($data['modificationDate']));
        }

        if (isset($data['meta']) && isset($data['meta']['creator'])) {
            $user = isset($data['meta']['creator']['id']) ? $this->userRepo->findOneBy(['uuid' => $data['meta']['creator']['id']]) : null;
            $post->setCreator($user);
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

        if (isset($data['poster']) && isset($data['poster']['url'])) {
            $post->setPoster($data['poster']['url']);
        }

        if (isset($data['thumbnail']) && isset($data['thumbnail']['url'])) {
            $post->setThumbnail($data['thumbnail']['url']);
        }

        return $post;
    }

    /**
     * Serialize post comments.
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
     * @return array
     */
    public function serializeTags(Post $post)
    {
        $event = new GenericDataEvent([
            'class' => 'Icap\BlogBundle\Entity\Post',
            'ids' => [$post->getUuid()],
        ]);
        $this->eventDispatcher->dispatch($event, 'claroline_retrieve_used_tags_by_class_and_ids');

        return $event->getResponse() ?? [];
    }

    /**
     * Deserializes Item tags.
     *
     * @param string $tags
     */
    public function deserializeTags(Post $post, array $tags, array $options = [])
    {
        $event = new GenericDataEvent([
            'tags' => $tags,
            'data' => [
                [
                    'class' => 'Icap\BlogBundle\Entity\Post',
                    'id' => $post->getUuid(),
                    'name' => $post->getTitle(),
                ],
            ],
            'replace' => true,
        ]);

        $this->eventDispatcher->dispatch($event, 'claroline_tag_multiple_data');
    }
}
