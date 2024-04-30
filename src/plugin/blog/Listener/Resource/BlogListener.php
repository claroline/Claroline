<?php

namespace Icap\BlogBundle\Listener\Resource;

use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\SerializerProvider;
use Claroline\AppBundle\API\Utils\FileBag;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Component\Resource\ResourceComponent;
use Claroline\CoreBundle\Entity\Resource\AbstractResource;
use Claroline\CoreBundle\Library\Normalizer\DateNormalizer;
use Icap\BlogBundle\Entity\Blog;
use Icap\BlogBundle\Entity\Comment;
use Icap\BlogBundle\Entity\Post;
use Icap\BlogBundle\Manager\BlogManager;
use Icap\BlogBundle\Manager\CommentManager;
use Icap\BlogBundle\Manager\PostManager;
use Icap\BlogBundle\Serializer\CommentSerializer;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class BlogListener extends ResourceComponent
{
    public function __construct(
        private readonly AuthorizationCheckerInterface $authorization,
        private readonly TokenStorageInterface $tokenStorage,
        private readonly ObjectManager $om,
        private readonly SerializerProvider $serializer,
        private readonly BlogManager $blogManager,
        private readonly PostManager $postManager,
        private readonly CommentManager $commentManager
    ) {
    }

    public static function getName(): string
    {
        return 'icap_blog';
    }

    /** @var Blog $resource */
    public function open(AbstractResource $resource, bool $embedded = false): ?array
    {
        $posts = $this->postManager->getPosts(
            $resource->getId(),
            ['limit' => -1],
            $this->authorization->isGranted('ADMINISTRATE', $resource->getResourceNode())
            || $this->authorization->isGranted('EDIT', $resource->getResourceNode())
            || $this->authorization->isGranted('MODERATE', $resource->getResourceNode())
                ? PostManager::GET_ALL_POSTS
                : PostManager::GET_PUBLISHED_POSTS,
            !$resource->getOptions()->getDisplayFullPosts());

        $postsData = [];
        if (!empty($posts)) {
            $postsData = $posts['data'];
        }

        return [
            'authors' => $this->postManager->getAuthors($resource),
            'archives' => $this->postManager->getArchives($resource),
            'tags' => $this->blogManager->getTags($resource, $postsData),
            'blog' => $this->serializer->serialize($resource),
        ];
    }

    /** @var Blog $resource */
    public function export(AbstractResource $resource, FileBag $fileBag): ?array
    {
        return [
            'posts' => array_map(function (Post $post) {
                return $this->serializer->serialize($post, [CommentSerializer::INCLUDE_COMMENTS, CommentSerializer::FETCH_COMMENTS]);
            }, $resource->getPosts()->toArray()),
        ];
    }

    /** @var Blog $resource */
    public function import(AbstractResource $resource, FileBag $fileBag, array $data = []): void
    {
        if (empty($data['posts'])) {
            return;
        }

        foreach ($data['posts'] as $postData) {
            /** @var Post $post */
            $post = $this->serializer->deserialize($postData, new Post(), [Options::REFRESH_UUID]);

            if (isset($postData['creationDate'])) {
                $post->setCreationDate(DateNormalizer::denormalize($postData['creationDate']));
            }

            if (isset($commentData['publicationDate'])) {
                $post->setPublicationDate(DateNormalizer::denormalize($postData['publicationDate']));
            }

            if (isset($commentData['updateDate'])) {
                $post->setModificationDate(DateNormalizer::denormalize($postData['updateDate']));
            }

            $post->setBlog($resource);
            $post->setCreator($this->tokenStorage->getToken()->getUser());

            foreach ($postData['comments'] as $commentData) {
                /** @var Comment $comment */
                $comment = $this->serializer->deserialize($commentData, new Comment(), [Options::REFRESH_UUID]);

                $this->commentManager->createComment($resource, $post, $this->serializer->deserialize($data, null), $comment['isPublished']);

                if (isset($commentData['creationDate'])) {
                    $comment->setCreationDate(DateNormalizer::denormalize($commentData['creationDate']));
                }

                if (isset($commentData['publicationDate'])) {
                    $comment->setPublicationDate(DateNormalizer::denormalize($commentData['publicationDate']));
                }

                if (isset($commentData['updateDate'])) {
                    $comment->setUpdateDate(DateNormalizer::denormalize($commentData['updateDate']));
                }

                $this->om->persist($comment);
            }

            $post->setBlog($resource);
            $this->om->persist($post);
        }
    }

    /**
     * @param Blog $original
     * @param Blog $copy
     */
    public function copy(AbstractResource $original, AbstractResource $copy): void
    {
        $this->blogManager->updateOptions($copy, $original->getOptions(), $original->getInfos());

        foreach ($original->getPosts() as $post) {
            $newPost = new Post();
            $newPost->setTitle($post->getTitle());
            $newPost->setContent($post->getContent());
            $newPost->setAuthor($post->getAuthor());
            $newPost->setStatus($post->getStatus());
            $newPost->setPinned($post->isPinned());
            $newPost->setCreationDate($post->getCreationDate());
            $newPost->setPublicationDate($post->getPublicationDate());
            $newPost->setModificationDate($post->getModificationDate());
            $newPost->setBlog($copy);

            $newPost->setCreator($post->getCreator());

            $this->om->persist($newPost);

            //get existing tags
            $tags = $this->postManager->getTags($post->getUuid());
            //add tags to copy
            $this->postManager->setTags($newPost, $tags);

            foreach ($post->getComments() as $comment) {
                $newComment = new Comment();
                $newComment->setCreationDate($comment->getCreationDate());
                $newComment->setPublicationDate($comment->getPublicationDate());
                $newComment->setUpdateDate($comment->getUpdateDate());
                $newComment->setAuthor($comment->getAuthor());
                $newComment->setMessage($comment->getMessage());
                $newComment->setPost($newPost);
            }
        }

        $this->om->persist($copy);
        $this->om->flush();
    }
}
