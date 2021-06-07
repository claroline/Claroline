<?php

namespace Icap\BlogBundle\Listener\Resource;

use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\SerializerProvider;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Event\ExportObjectEvent;
use Claroline\CoreBundle\Event\GenericDataEvent;
use Claroline\CoreBundle\Event\ImportObjectEvent;
use Claroline\CoreBundle\Event\Resource\CopyResourceEvent;
use Claroline\CoreBundle\Event\Resource\LoadResourceEvent;
use Claroline\CoreBundle\Library\Normalizer\DateNormalizer;
use Claroline\CoreBundle\Manager\Resource\ResourceEvaluationManager;
use Claroline\CoreBundle\Security\PermissionCheckerTrait;
use Claroline\EvaluationBundle\Entity\AbstractEvaluation;
use Icap\BlogBundle\Entity\Blog;
use Icap\BlogBundle\Entity\Comment;
use Icap\BlogBundle\Entity\Post;
use Icap\BlogBundle\Manager\BlogManager;
use Icap\BlogBundle\Manager\CommentManager;
use Icap\BlogBundle\Manager\PostManager;
use Icap\BlogBundle\Serializer\CommentSerializer;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class BlogListener
{
    use PermissionCheckerTrait;

    /** @var TokenStorageInterface */
    private $tokenStorage;
    /** @var ObjectManager */
    private $om;
    /** @var SerializerProvider */
    private $serializer;
    /** @var ResourceEvaluationManager */
    private $evaluationManager;
    /** @var BlogManager */
    private $blogManager;
    /** @var PostManager */
    private $postManager;
    /** @var CommentManager */
    private $commentManager;

    public function __construct(
        AuthorizationCheckerInterface $authorization,
        TokenStorageInterface $tokenStorage,
        ObjectManager $om,
        SerializerProvider $serializer,
        ResourceEvaluationManager $evaluationManager,
        BlogManager $blogManager,
        PostManager $postManager,
        CommentManager $commentManager
    ) {
        $this->authorization = $authorization;
        $this->tokenStorage = $tokenStorage;
        $this->om = $om;
        $this->serializer = $serializer;
        $this->evaluationManager = $evaluationManager;
        $this->blogManager = $blogManager;
        $this->postManager = $postManager;
        $this->commentManager = $commentManager;
    }

    public function onLoad(LoadResourceEvent $event)
    {
        /** @var Blog $blog */
        $blog = $event->getResource();

        $posts = $this->postManager->getPosts(
            $blog->getId(),
            ['limit' => -1],
            $this->checkPermission('ADMINISTRATE', $blog->getResourceNode())
            || $this->checkPermission('EDIT', $blog->getResourceNode())
            || $this->checkPermission('MODERATE', $blog->getResourceNode())
                ? PostManager::GET_ALL_POSTS
                : PostManager::GET_PUBLISHED_POSTS,
            !$blog->getOptions()->getDisplayFullPosts());

        $postsData = [];
        if (!empty($posts)) {
            $postsData = $posts['data'];
        }

        $event->setData([
            'authors' => $this->postManager->getAuthors($blog),
            'archives' => $this->postManager->getArchives($blog),
            'tags' => $this->blogManager->getTags($blog, $postsData),
            'blog' => $this->serializer->serialize($blog),
        ]);

        $event->stopPropagation();
    }

    public function onExport(ExportObjectEvent $exportEvent)
    {
        /** @var Blog $blog */
        $blog = $exportEvent->getObject();

        $data = [
            'posts' => array_map(function (Post $post) {
                return $this->serializer->serialize($post, [
                    CommentSerializer::INCLUDE_COMMENTS, CommentSerializer::FETCH_COMMENTS,
                ]);
            }, $blog->getPosts()->toArray()),
        ];
        $exportEvent->overwrite('_data', $data);
    }

    public function onImport(ImportObjectEvent $event)
    {
        $data = $event->getData();
        $blog = $event->getObject();

        foreach ($data['_data']['posts'] as $postData) {
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

            $post->setBlog($blog)
                ->setAuthor($this->tokenStorage->getToken()->getUser());

            foreach ($postData['comments'] as $commentData) {
                /** @var Comment $comment */
                $comment = $this->serializer->deserialize($commentData, new Comment(), [Options::REFRESH_UUID]);

                $this->commentManager->createComment($blog, $post, $this->serializer->deserialize($data, null), $comment['isPublished']);

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

            $post->setBlog($blog);
            $this->om->persist($post);
        }
    }

    public function onCopy(CopyResourceEvent $event)
    {
        /** @var Blog $blog */
        $blog = $event->getResource();
        /** @var Blog $newBlog */
        $newBlog = $event->getCopy();

        $this->blogManager->updateOptions($newBlog, $blog->getOptions(), $blog->getInfos());

        foreach ($blog->getPosts() as $post) {
            $newPost = new Post();
            $newPost
                ->setTitle($post->getTitle())
                ->setContent($post->getContent())
                ->setAuthor($post->getAuthor())
                ->setStatus($post->getStatus())
                ->setPinned($post->isPinned())
                ->setCreationDate($post->getCreationDate())
                ->setPublicationDate($post->getPublicationDate())
                ->setModificationDate($post->getModificationDate())
                ->setBlog($newBlog)
            ;

            $this->om->persist($newPost);
            $this->om->flush();

            //get existing tags
            $tags = $this->postManager->getTags($post->getUuid());
            //add tags to copy
            $this->postManager->setTags($newPost, $tags);

            foreach ($post->getComments() as $comment) {
                $newComment = new Comment();
                $newComment
                    ->setCreationDate($comment->getCreationDate())
                    ->setPublicationDate($comment->getPublicationDate())
                    ->setUpdateDate($comment->getUpdateDate())
                    ->setAuthor($comment->getAuthor())
                    ->setMessage($comment->getMessage())
                    ->setPost($newPost)
                ;
            }
        }

        $this->om->persist($newBlog);

        $event->setCopy($newBlog);
        $event->stopPropagation();
    }

    public function onGenerateResourceTracking(GenericDataEvent $event)
    {
        $data = $event->getData();
        $node = $data['resourceNode'];
        $user = $data['user'];
        $startDate = $data['startDate'];

        $logs = $this->evaluationManager->getLogsForResourceTracking(
            $node,
            $user,
            ['resource-read', 'resource-icap_blog-post_create', 'resource-icap_blog-post_update', 'resource-icap_blog-comment_create'],
            $startDate
        );
        $nbLogs = count($logs);

        if ($nbLogs > 0) {
            $this->om->startFlushSuite();
            $tracking = $this->evaluationManager->getResourceUserEvaluation($node, $user);
            $tracking->setDate($logs[0]->getDateLog());
            $status = AbstractEvaluation::STATUS_UNKNOWN;
            $nbAttempts = 0;
            $nbOpenings = 0;

            foreach ($logs as $log) {
                switch ($log->getAction()) {
                    case 'resource-read':
                        ++$nbOpenings;

                        if (AbstractEvaluation::STATUS_UNKNOWN === $status) {
                            $status = AbstractEvaluation::STATUS_OPENED;
                        }
                        break;
                    case 'resource-icap_blog-post_create':
                    case 'resource-icap_blog-post_update':
                    case 'resource-icap_blog-comment_create':
                        ++$nbAttempts;
                        $status = AbstractEvaluation::STATUS_PARTICIPATED;
                        break;
                }
            }
            $tracking->setStatus($status);
            $tracking->setNbAttempts($nbAttempts);
            $tracking->setNbOpenings($nbOpenings);

            $this->om->persist($tracking);
            $this->om->endFlushSuite();
        }

        $event->stopPropagation();
    }
}
