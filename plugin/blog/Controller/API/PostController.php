<?php

namespace Icap\BlogBundle\Controller\API;

use Claroline\AppBundle\Security\ObjectCollection;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Security\PermissionCheckerTrait;
use Claroline\CoreBundle\Validator\Exception\InvalidDataException;
use Icap\BlogBundle\Entity\Blog;
use Icap\BlogBundle\Entity\Comment;
use Icap\BlogBundle\Entity\Post;
use Icap\BlogBundle\Manager\BlogTrackingManager;
use Icap\BlogBundle\Manager\PostManager;
use Icap\BlogBundle\Serializer\CommentSerializer;
use Icap\BlogBundle\Serializer\PostSerializer;
use JMS\DiExtraBundle\Annotation as DI;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @EXT\Route("blog/{blogId}/posts", options={"expose"=true})
 * @EXT\ParamConverter("blog", class="IcapBlogBundle:Blog", options={"mapping": {"blogId": "uuid"}})
 */
class PostController
{
    use PermissionCheckerTrait;

    private $postSerializer;
    private $commentSerializer;
    private $postManager;
    private $trackingManager;
    private $logThreshold;

    /**
     * postController constructor.
     *
     * @DI\InjectParams({
     *     "postSerializer"    = @DI\Inject("claroline.serializer.blog.post"),
     *     "commentSerializer" = @DI\Inject("claroline.serializer.blog.comment"),
     *     "postManager"       = @DI\Inject("icap.blog.manager.post"),
     *     "trackingManager"   = @DI\Inject("icap.blog.manager.tracking"),
     *     "logThreshold"      = @DI\Inject("%non_repeatable_log_time_in_seconds%")

     * })
     *
     * @param PostSerializer      $postSerializer
     * @param commentSerializer   $commentSerializer
     * @param PostManager         $postManager
     * @param BlogTrackingManager $trackingManager
     * @param $logThreshold
     */
    public function __construct(
        PostSerializer $postSerializer,
        CommentSerializer $commentSerializer,
        PostManager $postManager,
        BlogTrackingManager $trackingManager,
        $logThreshold)
    {
        $this->postSerializer = $postSerializer;
        $this->commentSerializer = $commentSerializer;
        $this->postManager = $postManager;
        $this->trackingManager = $trackingManager;
        $this->logThreshold = $logThreshold;
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
            !$this->authorization->isGranted('EDIT', new ObjectCollection([$blog])),
            !$blog->getOptions()->getDisplayFullPosts());

        return new JsonResponse($posts);
    }

    /**
     * Get blog post.
     *
     * @EXT\Route("/{postId}", name="apiv2_blog_post_get")
     * @EXT\Method("GET")
     * @EXT\ParamConverter("user", converter="current_user", options={"allowAnonymous"=true})
     *
     * @param Blog $blog
     * @param Post $post
     *
     * @return array
     */
    public function getAction(Request $request, Blog $blog, $postId, User $user = null)
    {
        $this->checkPermission('OPEN', $blog->getResourceNode(), [], true);
        $post = $this->postManager->getPostByIdOrSlug($blog, $postId);
        if (is_null($post)) {
            throw new NotFoundHttpException();
        }

        $this->trackingManager->dispatchPostReadEvent($post);

        $session = $request->getSession();
        $sessionViewCounterKey = 'blog_post_view_counter_'.$post->getId();
        $now = time();

        if ($now >= ($session->get($sessionViewCounterKey) + $this->logThreshold)) {
            $session->set($sessionViewCounterKey, $now);
            $this->postManager->updatePostViewCount($post);
        }

        $userId = null !== $user ? $user->getId() : null;
        //if no edit rights, list only published comments and current user ones
        $canEdit = $this->authorization->isGranted('EDIT', new ObjectCollection([$blog]));
        $comments = [];
        if (!$canEdit) {
            $options = [CommentSerializer::INCLUDE_COMMENTS => CommentSerializer::PRELOADED_COMMENTS];
            $parameters = $request->query->all();
            $comments = $this->postManager->getComments(
                $blog->getId(),
                $post->getId(),
                $userId,
                $parameters,
                !$canEdit)['data'];
        } else {
            $options = [CommentSerializer::INCLUDE_COMMENTS => CommentSerializer::FETCH_COMMENTS];
        }

        return new JsonResponse($this->postSerializer->serialize($post, $options, $comments));
    }

    /**
     * Create blog post.
     *
     * @EXT\Route("/new", name="apiv2_blog_post_new")
     * @EXT\Method("POST")
     * @EXT\ParamConverter("user", converter="current_user", options={"allowAnonymous"=false})
     *
     * @param Blog $blog
     * @param User $user
     *
     * @return array
     */
    public function createPostAction(Request $request, Blog $blog, User $user)
    {
        $this->checkPermission('EDIT', $blog->getResourceNode(), [], true);
        $data = json_decode($request->getContent(), true);
        $post = $this->postManager->createPost($blog, $this->postSerializer->deserialize($data), $user);

        return new JsonResponse($this->postSerializer->serialize($post));
    }

    /**
     * Create a post comment.
     *
     * @EXT\Route("/comment/{postId}", name="apiv2_blog_comment_new")
     * @EXT\Method("POST")
     * @EXT\ParamConverter("post", class="IcapBlogBundle:Post", options={"mapping": {"postId": "uuid"}})
     * @EXT\ParamConverter("user", converter="current_user", options={"allowAnonymous"=true})
     *
     * @param Blog $blog
     * @param Post $post
     * @param User $user
     *
     * @return Comment
     */
    public function createCommentAction(Request $request, Blog $blog, Post $post, User $user = null)
    {
        if ($blog->isCommentsAuthorized() && ($blog->isAuthorizeAnonymousComment() || null !== $user)) {
            $data = [];
            $data['message'] = $request->get('comment', false);
            $forcePublication = $this->authorization->isGranted('EDIT', new ObjectCollection([$blog]));
            $comment = $this->postManager->createComment($blog, $post, $this->commentSerializer->deserialize($data, null, $user), $forcePublication);

            return new JsonResponse($this->commentSerializer->serialize($comment));
        } else {
            throw new AccessDeniedException();
        }
    }

    /**
     * Is the user logged in or not ?
     *
     * @return bool
     */
    private function isLoggedIn(User $user)
    {
        return is_string($user) ? false : true;
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
     * Update post comment.
     *
     * @EXT\Route("/update/{postId}/comment/{commentId}", name="apiv2_blog_comment_update")
     * @EXT\Method("PUT")
     * @EXT\ParamConverter("post", class="IcapBlogBundle:Post", options={"mapping": {"postId": "uuid"}})
     * @EXT\ParamConverter("comment", class="IcapBlogBundle:Comment", options={"mapping": {"commentId": "uuid"}})
     * @EXT\ParamConverter("user", converter="current_user", options={"allowAnonymous"=true})
     *
     * @param Blog    $blog
     * @param Post    $post
     * @param Comment $comment
     * @param User    $user
     *
     * @return array
     */
    public function updateCommentAction(Request $request, Blog $blog, Post $post, Comment $comment, User $user = null)
    {
        //original author or admin can edit, anon cant edit
        if ($blog->isCommentsAuthorized() && $this->isLoggedIn($user)) {
            if ($user !== $comment->getAuthor()) {
                $this->checkPermission('EDIT', $blog->getResourceNode(), [], true);
            }
            $data = $this->decodeRequest($request)['comment'];
            $comment = $this->postManager->updateComment($blog, $post, $comment, $data);

            return new JsonResponse($this->commentSerializer->serialize($comment));
        } else {
            throw new AccessDeniedException();
        }
    }

    /**
     * Publish post comment.
     *
     * @EXT\Route("/update/{postId}/comment/{commentId}/publish", name="apiv2_blog_comment_publish")
     * @EXT\Method("PUT")
     * @EXT\ParamConverter("post", class="IcapBlogBundle:Post", options={"mapping": {"postId": "uuid"}})
     * @EXT\ParamConverter("comment", class="IcapBlogBundle:Comment", options={"mapping": {"commentId": "uuid"}})
     * @EXT\ParamConverter("user", converter="current_user", options={"allowAnonymous"=false})
     *
     * @param Blog    $blog
     * @param Post    $post
     * @param Comment $comment
     * @param User    $user
     *
     * @return array
     */
    public function publishCommentAction(Request $request, Blog $blog, Post $post, Comment $comment, User $user)
    {
        $this->checkPermission('EDIT', $blog->getResourceNode(), [], true);
        $comment = $this->postManager->publishComment($blog, $post, $comment);

        return new JsonResponse($this->commentSerializer->serialize($comment));
    }

    /**
     * Report post comment.
     *
     * @EXT\Route("/update/{postId}/comment/{commentId}/report", name="apiv2_blog_comment_report")
     * @EXT\Method("PUT")
     * @EXT\ParamConverter("post", class="IcapBlogBundle:Post", options={"mapping": {"postId": "uuid"}})
     * @EXT\ParamConverter("comment", class="IcapBlogBundle:Comment", options={"mapping": {"commentId": "uuid"}})
     * @EXT\ParamConverter("user", converter="current_user", options={"allowAnonymous"=false})
     *
     * @param Blog    $blog
     * @param Post    $post
     * @param Comment $comment
     * @param User    $user
     *
     * @return array
     */
    public function reportCommentAction(Request $request, Blog $blog, Post $post, Comment $comment, User $user)
    {
        $comment = $this->postManager->reportComment($blog, $post, $comment);

        return new JsonResponse($this->commentSerializer->serialize($comment));
    }

    /**
     * unpublish post comment.
     *
     * @EXT\Route("/update/{postId}/comment/{commentId}/unpublish", name="apiv2_blog_comment_unpublish")
     * @EXT\Method("PUT")
     * @EXT\ParamConverter("post", class="IcapBlogBundle:Post", options={"mapping": {"postId": "uuid"}})
     * @EXT\ParamConverter("comment", class="IcapBlogBundle:Comment", options={"mapping": {"commentId": "uuid"}})
     * @EXT\ParamConverter("user", converter="current_user", options={"allowAnonymous"=false})
     *
     * @param Blog    $blog
     * @param Post    $post
     * @param Comment $comment
     * @param User    $user
     *
     * @return array
     */
    public function unpublishCommentAction(Request $request, Blog $blog, Post $post, Comment $comment, User $user)
    {
        $this->checkPermission('EDIT', $blog->getResourceNode(), [], true);
        $comment = $this->postManager->unpublishComment($blog, $post, $comment);

        return new JsonResponse($this->commentSerializer->serialize($comment));
    }

    /**
     * Delete post comment.
     *
     * @EXT\Route("/delete/{postId}/comment/{commentId}", name="apiv2_blog_comment_delete")
     * @EXT\Method("DELETE")
     * @EXT\ParamConverter("post", class="IcapBlogBundle:Post", options={"mapping": {"postId": "uuid"}})
     * @EXT\ParamConverter("comment", class="IcapBlogBundle:Comment", options={"mapping": {"commentId": "uuid"}})
     * @EXT\ParamConverter("user", converter="current_user", options={"allowAnonymous"=false})
     *
     * @param Blog    $blog
     * @param Post    $post
     * @param Comment $comment
     * @param User    $user
     *
     * @return array
     */
    public function deleteCommentAction(Request $request, Blog $blog, Post $post, Comment $comment, User $user)
    {
        //original author or admin can edit, anon cant edit
        if ($blog->isCommentsAuthorized() && $this->isLoggedIn($user)) {
            if ($user !== $comment->getAuthor()) {
                $this->checkPermission('EDIT', $blog->getResourceNode(), [], true);
            }

            $commentId = $this->postManager->deleteComment($blog, $post, $comment);

            return new JsonResponse($commentId);
        } else {
            throw new AccessDeniedException();
        }
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
        $this->checkPermission('EDIT', $blog->getResourceNode(), [], true);

        $this->postManager->switchPublicationState($post);

        return new JsonResponse($this->postSerializer->serialize($post));
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
     * Gets and Deserializes JSON data from Request.
     *
     * @param Request $request
     *
     * @return mixed $data
     */
    protected function decodeRequest(Request $request)
    {
        $decodedRequest = json_decode($request->getContent(), true);

        if (null === $decodedRequest) {
            throw new InvalidDataException('Invalid request content sent.', []);
        }

        return $decodedRequest;
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

    /**
     * Get tags used in posts.
     *
     * @EXT\Route("/tags/get", name="apiv2_blog_tags")
     * @EXT\Method("GET")
     */
    public function getTagsAction(Blog $blog)
    {
        $this->checkPermission('OPEN', $blog->getResourceNode(), [], true);

        $parameters['limit'] = -1;
        $posts = $this->postManager->getPosts(
            $blog->getId(),
            $parameters,
            !$this->authorization->isGranted('EDIT', new ObjectCollection([$blog])),
            true);

        $postsData = [];
        if (!empty($posts)) {
            $postsData = $posts['data'];
        }

        return new JsonResponse($this->postManager->getTags($blog, $postsData));
    }
}
