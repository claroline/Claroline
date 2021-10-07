<?php

namespace Icap\BlogBundle\Manager;

use Claroline\AppBundle\API\FinderProvider;
use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\API\Serializer\User\UserSerializer;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Event\GenericDataEvent;
use Icap\BlogBundle\Entity\Blog;
use Icap\BlogBundle\Entity\Post;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class PostManager
{
    private $om;
    private $trackingManager;
    private $repo;
    private $finder;
    private $translator;
    private $userSerializer;
    private $userRepo;
    private $eventDispatcher;

    const GET_ALL_POSTS = 'GET_ALL_POSTS';
    const GET_PUBLISHED_POSTS = 'GET_PUBLISHED_POSTS';
    const GET_UNPUBLISHED_POSTS = 'GET_UNPUBLISHED_POSTS';

    public function __construct(
        FinderProvider $finder,
        ObjectManager $om,
        BlogTrackingManager $trackingManager,
        TranslatorInterface $translator,
        UserSerializer $userSerializer,
        EventDispatcherInterface $eventDispatcher)
    {
        $this->finder = $finder;
        $this->om = $om;
        $this->trackingManager = $trackingManager;
        $this->translator = $translator;
        $this->userSerializer = $userSerializer;
        $this->eventDispatcher = $eventDispatcher;

        $this->userRepo = $om->getRepository('Claroline\CoreBundle\Entity\User');
        $this->repo = $om->getRepository(Post::class);
    }

    /**
     * @param string $postId
     *
     * @return post
     */
    public function getPostByIdOrSlug(Blog $blog, $postId)
    {
        if (is_int($postId)) {
            $post = $this->repo->findOneBy([
                'blog' => $blog,
                'id' => $postId,
            ]);
        } else {
            $post = $this->repo->findOneBy([
                'blog' => $blog,
                'slug' => $postId,
            ]);
        }

        return $post;
    }

    /**
     * Find all content for a given user and the replace him by another.
     *
     * @return int
     */
    public function replacePostAuthor(User $from, User $to)
    {
        $posts = $this->repo->findBy(['creator' => $from]);

        if (count($posts) > 0) {
            foreach ($posts as $post) {
                $post->setCreator($to);
            }

            $this->om->flush();
        }

        return count($posts);
    }

    /**
     * Create a post.
     */
    public function createPost(Blog $blog, Post $post, User $user)
    {
        $post
            ->setBlog($blog)
            ->setStatus($blog->isAutoPublishPost() ? Post::STATUS_PUBLISHED : Post::STATUS_UNPUBLISHED)
            ->setCreationDate(new \DateTime())
            ->setModificationDate(new \DateTime());

        $post->setCreator($user);

        if (null === $post->getPublicationDate()) {
            $post->setPublicationDate(new \DateTime());
        }

        //tracking
        $this->trackingManager->dispatchPostCreateEvent($blog, $post);
        if ($user instanceof User) {
            $this->trackingManager->updateResourceTracking($blog->getResourceNode(), $user, new \DateTime());
        }

        $this->om->persist($post);
        $this->om->flush();

        return $post;
    }

    /**
     * Update a post.
     *
     * @return Post
     */
    public function updatePost(Blog $blog, Post $existingPost, Post $post, User $user)
    {
        $existingPost
        ->setTitle($post->getTitle())
        ->setContent($post->getContent());
        if (null !== $post->getPublicationDate()) {
            $existingPost->setPublicationDate($post->getPublicationDate());
        } else {
            $existingPost->setPublicationDate(null);
        }

        $this->om->flush();

        $unitOfWork = $this->om->getUnitOfWork();
        $unitOfWork->computeChangeSets();
        $changeSet = $unitOfWork->getEntityChangeSet($existingPost);

        $this->trackingManager->dispatchPostUpdateEvent($existingPost, $changeSet);

        if ($user instanceof User) {
            $this->trackingManager->updateResourceTracking($blog->getResourceNode(), $user, new \DateTime());
        }

        return $existingPost;
    }

    /**
     * Delete a post.
     *
     * @return Post
     */
    public function deletePost(Blog $blog, Post $post, User $user)
    {
        //remove tags beforehand
        $event = new GenericDataEvent([
            'tags' => [],
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

        $this->om->remove($post);
        $this->om->flush();

        $this->trackingManager->dispatchPostDeleteEvent($post);
    }

    /**
     * Update post view count.
     *
     * @param Blog $blog
     * @param Post $post
     * @param User $user
     */
    public function updatePostViewCount($post)
    {
        $post->increaseViewCounter();
        $this->om->persist($post);
        $this->om->flush();
    }

    /**
     * Pin/unpin post.
     *
     * @param Post $post
     */
    public function switchPinState($post)
    {
        $post->setPinned(!$post->isPinned());
        $this->om->persist($post);
        $this->om->flush();
    }

    /**
     * Switch post state.
     *
     * @param User $user
     */
    public function switchPublicationState(Post $post)
    {
        if (!$post->isPublished()) {
            $post->publish();
        } else {
            $post->unpublish();
        }

        $this->om->flush();

        $unitOfWork = $this->om->getUnitOfWork();
        $unitOfWork->computeChangeSets();
        $changeSet = $unitOfWork->getEntityChangeSet($post);

        $this->trackingManager->dispatchPostUpdateEvent($post, $changeSet);

        return $post;
    }

    /**
     * Get posts.
     *
     * @param $blogId
     * @param $filters
     * @param $publication
     * @param $abstract
     */
    public function getPosts($blogId, $filters, $publication = self::GET_PUBLISHED_POSTS, $abstract = true)
    {
        if (!isset($filters['hiddenFilters'])) {
            $filters['hiddenFilters'] = [];
        }
        //filter on current blog
        $filters['hiddenFilters'] = [
            'blog' => $blogId,
        ];
        $publicationOptions = [];
        if (self::GET_PUBLISHED_POSTS === $publication) {
            $publicationOptions = ['published' => true];
        } elseif (self::GET_UNPUBLISHED_POSTS === $publication) {
            $publicationOptions = ['published' => false];
        }
        $filters['hiddenFilters'] = array_merge($filters['hiddenFilters'], $publicationOptions);
        $filters['sortBy'] = '-publicationDate';

        return $this->finder->search('Icap\BlogBundle\Entity\Post', $filters, [
            'abstract' => $abstract,
        ]);
    }

    /**
     * Get blog post authors.
     *
     * @param Blog $blog
     *
     * @return array
     */
    public function getAuthors($blog)
    {
        $recordSet = $this->repo->findAuthorsByBlog($blog);
        $authorsIds = [];
        foreach ($recordSet as $value) {
            $authorsIds[] = $value['id'];
        }
        $authors = $this->userRepo->findBy(['id' => $authorsIds], ['lastName' => 'ASC']);
        $serializedAuthors = [];
        foreach ($authors as $author) {
            $serializedAuthors[] = $this->userSerializer->serialize($author, [Options::SERIALIZE_MINIMAL]);
        }

        return $serializedAuthors;
    }

    /**
     * Get blog posts archives.
     *
     * @param Blog $blog
     *
     * @return array
     */
    public function getArchives($blog)
    {
        $postDatas = $this->repo->findArchiveDataByBlog($blog);
        $archiveDatas = [];

        foreach ($postDatas as $postData) {
            $year = $postData['year'];
            $month = $postData['month'];
            $count = $postData['count'];

            $archiveDatas[$year][] = [
                'month' => $this->translator->trans('month.'.date('F', mktime(0, 0, 0, $month, 10)), [], 'platform'),
                'monthValue' => $month,
                'count' => $count,
            ];
        }

        return $archiveDatas;
    }

    /**
     * Get post tags.
     *
     * @param $postUuid
     *
     * @return array
     */
    public function getTags($postUuid)
    {
        $event = new GenericDataEvent([
            'class' => 'Icap\BlogBundle\Entity\Post',
            'ids' => [$postUuid],
        ]);

        $this->eventDispatcher->dispatch(
            $event,
            'claroline_retrieve_used_tags_by_class_and_ids'
        );
        $tags = $event->getResponse();

        return $tags;
    }

    /**
     * Get post tags.
     *
     * @param Post $post
     *
     * @return array
     */
    public function setTags($post, $tags = [])
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
