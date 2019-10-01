<?php

namespace Icap\BlogBundle\Controller\API;

use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Security\PermissionCheckerTrait;
use Icap\BlogBundle\Entity\Blog;
use Icap\BlogBundle\Entity\Post;
use Icap\BlogBundle\Manager\BlogTrackingManager;
use Icap\BlogBundle\Manager\PostManager;
use Icap\BlogBundle\Serializer\PostSerializer;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @EXT\Route("blog/{blogId}/posts", options={"expose"=true})
 * @EXT\ParamConverter("blog", class="IcapBlogBundle:Blog", options={"mapping": {"blogId": "uuid"}})
 */
class PostController
{
    use PermissionCheckerTrait;

    private $postSerializer;
    private $postManager;
    private $trackingManager;
    private $logThreshold;

    /**
     * postController constructor.
     *
     * @param PostSerializer      $postSerializer
     * @param PostManager         $postManager
     * @param BlogTrackingManager $trackingManager
     * @param $logThreshold
     */
    public function __construct(
        PostSerializer $postSerializer,
        PostManager $postManager,
        BlogTrackingManager $trackingManager,
        $logThreshold,
        AuthorizationCheckerInterface $authorization)
    {
        $this->postSerializer = $postSerializer;
        $this->postManager = $postManager;
        $this->trackingManager = $trackingManager;
        $this->logThreshold = $logThreshold;
        $this->authorization = $authorization;
    }

    /**
     * Get the name of the managed entity.
     *
     * @return string
     */
    public function getName()
    {
        return 'post';
    }

    /**
     * Get unpublished blog posts.
     *
     * @EXT\Route("/moderation", name="apiv2_blog_post_list_unpublished")
     * @EXT\Method("GET")
     *
     * @param Blog $blog
     *
     * @return array
     */
    public function listUnpublishedAction(Request $request, Blog $blog)
    {
        $this->checkPermission('OPEN', $blog->getResourceNode(), [], true);
        if ($this->checkPermission('MODERATE', $blog->getResourceNode())
            || $this->checkPermission('EDIT', $blog->getResourceNode())) {
            $parameters = $request->query->all();

            //if no edit rights, list only published posts
            $posts = $this->postManager->getPosts(
                $blog->getId(),
                $parameters,
                PostManager::GET_UNPUBLISHED_POSTS,
                true);

            return new JsonResponse($posts);
        } else {
            throw new AccessDeniedException();
        }
    }

    /**
     * Get blog posts.
     *
     * @EXT\Route("", name="apiv2_blog_post_list")
     * @EXT\Method("GET")
     *
     * @param Blog $blog
     *
     * @return array
     */
    public function listAction(Request $request, Blog $blog)
    {
        $this->checkPermission('OPEN', $blog->getResourceNode(), [], true);

        $parameters = $request->query->all();

        //if no edit rights, list only published posts
        $posts = $this->postManager->getPosts(
            $blog->getId(),
            $parameters,
            $this->checkPermission('ADMINISTRATE', $blog->getResourceNode())
            || $this->checkPermission('EDIT', $blog->getResourceNode())
            || $this->checkPermission('MODERATE', $blog->getResourceNode())
                ? PostManager::GET_ALL_POSTS
                : PostManager::GET_PUBLISHED_POSTS,
            !$blog->getOptions()->getDisplayFullPosts());

        return new JsonResponse($posts);
    }

    /**
     * Get blog post.
     *
     * @EXT\Route("/{postId}", name="apiv2_blog_post_get")
     * @EXT\Method("GET")
     * @EXT\ParamConverter("blog", options={"mapping": {"blogId": "uuid"}})
     *
     * @param Blog $blog
     * @param Post $post
     *
     * @return array
     */
    public function getAction(Request $request, Blog $blog, $postId)
    {
        $this->checkPermission('OPEN', $blog->getResourceNode(), [], true);
        $post = $this->postManager->getPostByIdOrSlug($blog, $postId);

        if (is_null($post)) {
            throw new NotFoundHttpException('Post not found');
        }

        $this->trackingManager->dispatchPostReadEvent($post);

        $session = $request->getSession();
        $sessionViewCounterKey = 'blog_post_view_counter_'.$post->getId();
        $now = time();

        if ($now >= ($session->get($sessionViewCounterKey) + $this->logThreshold)) {
            $session->set($sessionViewCounterKey, $now);
            $this->postManager->updatePostViewCount($post);
        }

        return new JsonResponse($this->postSerializer->serialize($post));
    }

    /**
     * Create blog post.
     *
     * @EXT\Route("/new", name="apiv2_blog_post_new")
     * @EXT\Method({"POST", "PUT"})
     * @EXT\ParamConverter("user", converter="current_user", options={"allowAnonymous"=false})
     *
     * @param Blog $blog
     * @param User $user
     *
     * @return array
     */
    public function createPostAction(Request $request, Blog $blog, User $user)
    {
        if ($this->checkPermission('EDIT', $blog->getResourceNode())
            || $this->checkPermission('POST', $blog->getResourceNode())) {
            $data = json_decode($request->getContent(), true);
            $post = $this->postManager->createPost($blog, $this->postSerializer->deserialize($data), $user);
        } else {
            throw new AccessDeniedException();
        }

        return new JsonResponse($this->postSerializer->serialize($post));
    }

    /**
     * Update blog post.
     *
     * @EXT\Route("/update/{postId}", name="apiv2_blog_post_update")
     * @EXT\Method("PUT")
     * @EXT\ParamConverter("post", class="IcapBlogBundle:Post", options={"mapping": {"postId": "uuid"}})
     * @EXT\ParamConverter("user", converter="current_user", options={"allowAnonymous"=false})
     *
     * @param Blog $blog
     * @param Post $post
     * @param User $user
     *
     * @return array
     */
    public function updatePostAction(Request $request, Blog $blog, Post $post, User $user)
    {
        $this->checkPermission('EDIT', $blog->getResourceNode(), [], true);

        $data = json_decode($request->getContent(), true);
        $post = $this->postManager->updatePost($blog, $post, $this->postSerializer->deserialize($data), $user);

        return new JsonResponse($this->postSerializer->serialize($post));
    }

    /**
     * Delete blog post.
     *
     * @EXT\Route("/delete/{postId}", name="apiv2_blog_post_delete")
     * @EXT\Method("DELETE")
     * @EXT\ParamConverter("post", class="IcapBlogBundle:Post", options={"mapping": {"postId": "uuid"}})
     * @EXT\ParamConverter("user", converter="current_user", options={"allowAnonymous"=false})
     *
     * @param Blog $blog
     * @param Post $post
     * @param User $user
     *
     * @return array
     */
    public function deletePostAction(Request $request, Blog $blog, Post $post, User $user)
    {
        $this->checkPermission('EDIT', $blog->getResourceNode(), [], true);
        $this->postManager->deletePost($blog, $post, $user);

        return new JsonResponse($post->getId());
    }

    /**
     * Switch post publication state.
     *
     * @EXT\Route("/publish/{postId}", name="apiv2_blog_post_publish")
     * @EXT\Method("PUT")
     * @EXT\ParamConverter("post", class="IcapBlogBundle:Post", options={"mapping": {"postId": "uuid"}})
     * @EXT\ParamConverter("user", converter="current_user", options={"allowAnonymous"=false})
     *
     * @param Blog $blog
     * @param Post $post
     * @param User $user
     *
     * @return array
     */
    public function publishPostAction(Request $request, Blog $blog, Post $post, User $user)
    {
        if ($this->checkPermission('EDIT', $blog->getResourceNode())
            || $this->checkPermission('MODERATE', $blog->getResourceNode())) {
            $this->postManager->switchPublicationState($post);

            return new JsonResponse($this->postSerializer->serialize($post));
        } else {
            throw new AccessDeniedException();
        }
    }

    /**
     * Pin post.
     *
     * @EXT\Route("/pin/{postId}", name="apiv2_blog_post_pin")
     * @EXT\Method("PUT")
     * @EXT\ParamConverter("post", class="IcapBlogBundle:Post", options={"mapping": {"postId": "uuid"}})
     * @EXT\ParamConverter("user", converter="current_user", options={"allowAnonymous"=false})
     *
     * @param Blog $blog
     * @param Post $post
     * @param User $user
     *
     * @return array
     */
    public function pinPostAction(Request $request, Blog $blog, Post $post, User $user)
    {
        $this->checkPermission('EDIT', $blog->getResourceNode(), [], true);

        $this->postManager->switchPinState($post);

        return new JsonResponse($this->postSerializer->serialize($post));
    }

    /**
     * Get all authors for a given blog.
     *
     * @EXT\Route("/authors/get", name="apiv2_blog_post_authors")
     * @EXT\Method("GET")
     */
    public function getBlogAuthorsAction(Blog $blog)
    {
        $this->checkPermission('OPEN', $blog->getResourceNode(), [], true);

        return $this->postManager->getAuthors($blog);
    }
}
