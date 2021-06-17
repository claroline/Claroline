<?php

namespace Icap\BlogBundle\Listener;

use Claroline\AppBundle\API\Options;
use Claroline\CoreBundle\Event\ExportObjectEvent;
use Claroline\CoreBundle\Event\GenericDataEvent;
use Claroline\CoreBundle\Event\ImportObjectEvent;
use Claroline\CoreBundle\Event\Resource\CopyResourceEvent;
use Claroline\CoreBundle\Event\Resource\LoadResourceEvent;
use Claroline\CoreBundle\Library\Normalizer\DateNormalizer;
use Claroline\CoreBundle\Security\PermissionCheckerTrait;
use Claroline\EvaluationBundle\Entity\Evaluation\AbstractEvaluation;
use Icap\BlogBundle\Entity\Blog;
use Icap\BlogBundle\Entity\Comment;
use Icap\BlogBundle\Entity\Post;
use Icap\BlogBundle\Manager\PostManager;
use Icap\BlogBundle\Serializer\CommentSerializer;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class BlogListener
{
    use PermissionCheckerTrait;

    /** @var HttpKernelInterface */
    private $httpKernel;
    /** @var Request */
    private $request;
    /** @var ContainerInterface */
    private $container;

    /**
     * BlogListener constructor.
     */
    public function __construct(
        HttpKernelInterface $httpKernel,
        RequestStack $requestStack,
        ContainerInterface $container,
        AuthorizationCheckerInterface $authorization
    ) {
        $this->httpKernel = $httpKernel;
        $this->request = $requestStack->getCurrentRequest();
        $this->container = $container;
        $this->authorization = $authorization;
    }

    public function onLoad(LoadResourceEvent $event)
    {
        /** @var Blog $blog */
        $blog = $event->getResource();
        $this->checkPermission('OPEN', $blog->getResourceNode(), [], true);

        $postManager = $this->container->get('Icap\BlogBundle\Manager\PostManager');
        $blogManager = $this->container->get('Icap\BlogBundle\Manager\BlogManager');

        $parameters = [];
        $parameters['limit'] = -1;

        $posts = $postManager->getPosts(
            $blog->getId(),
            $parameters,
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
          'authors' => $postManager->getAuthors($blog),
          'archives' => $postManager->getArchives($blog),
          'tags' => $blogManager->getTags($blog, $postsData),
          'blog' => $this->container->get('Claroline\AppBundle\API\SerializerProvider')->serialize($blog),
        ]);

        $event->stopPropagation();
    }

    public function onExport(ExportObjectEvent $exportEvent)
    {
        $blog = $exportEvent->getObject();
        $data = [
          'posts' => array_map(function (Post $post) {
              return $this->container->get('Icap\BlogBundle\Serializer\PostSerializer')->serialize($post, [
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
        $om = $this->container->get('Claroline\AppBundle\Persistence\ObjectManager');

        foreach ($data['_data']['posts'] as $postData) {
            /** @var Post $post */
            $post = $this->container->get('Icap\BlogBundle\Serializer\PostSerializer')->deserialize($postData, new Post(), [Options::REFRESH_UUID]);

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
              ->setAuthor($this->container->get('security.token_storage')->getToken()->getUser());

            foreach ($postData['comments'] as $commentData) {
                /** @var Comment $comment */
                $comment = $this->container->get('Icap\BlogBundle\Serializer\CommentSerializer')->deserialize($commentData, new Comment(), [Options::REFRESH_UUID]);

                $this->container->get('Icap\BlogBundle\Manager\CommentManager')
                  ->createComment($blog, $post, $this->commentSerializer->deserialize($data, null), $comment['isPublished']);

                if (isset($commentData['creationDate'])) {
                    $comment->setCreationDate(DateNormalizer::denormalize($commentData['creationDate']));
                }

                if (isset($commentData['publicationDate'])) {
                    $comment->setPublicationDate(DateNormalizer::denormalize($commentData['publicationDate']));
                }

                if (isset($commentData['updateDate'])) {
                    $comment->setUpdateDate(DateNormalizer::denormalize($commentData['updateDate']));
                }

                $om->persist($comment);
            }

            $post->setBlog($blog);
            $om->persist($post);
        }
    }

    public function onCopy(CopyResourceEvent $event)
    {
        $entityManager = $this->container->get('Claroline\AppBundle\Persistence\ObjectManager');
        $postManager = $this->container->get('Icap\BlogBundle\Manager\PostManager');
        /** @var \Icap\BlogBundle\Entity\Blog $blog */
        $blog = $event->getResource();

        $newBlog = $event->getCopy();

        $this->container->get('Icap\BlogBundle\Manager\BlogManager')->updateOptions($newBlog, $blog->getOptions(), $blog->getInfos());

        foreach ($blog->getPosts() as $post) {
            /** @var Post $newPost */
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

            $entityManager->persist($newPost);
            $entityManager->flush($newPost);

            //get existing tags
            $tags = $postManager->getTags($post->getUuid());
            //add tags to copy
            $postManager->setTags($newPost, $tags);

            foreach ($post->getComments() as $comment) {
                /** @var \Icap\BlogBundle\Entity\Comment $newComment */
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

        $entityManager->persist($newBlog);

        $event->setCopy($newBlog);
        $event->stopPropagation();
    }

    public function onGenerateResourceTracking(GenericDataEvent $event)
    {
        $om = $this->container->get('Claroline\AppBundle\Persistence\ObjectManager');
        $resourceEvalManager = $this->container->get('claroline.manager.resource_evaluation_manager');
        $data = $event->getData();
        $node = $data['resourceNode'];
        $user = $data['user'];
        $startDate = $data['startDate'];

        $logs = $resourceEvalManager->getLogsForResourceTracking(
            $node,
            $user,
            ['resource-read', 'resource-icap_blog-post_create', 'resource-icap_blog-post_update', 'resource-icap_blog-comment_create'],
            $startDate
        );
        $nbLogs = count($logs);

        if ($nbLogs > 0) {
            $om->startFlushSuite();
            $tracking = $resourceEvalManager->getResourceUserEvaluation($node, $user);
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
            $om->persist($tracking);
            $om->endFlushSuite();
        }
        $event->stopPropagation();
    }
}
