<?php

namespace Icap\BlogBundle\Controller;

use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Security\PermissionCheckerTrait;
use Claroline\CoreBundle\Validator\Exception\InvalidDataException;
use Icap\BlogBundle\Entity\Blog;
use Icap\BlogBundle\Entity\Comment;
use Icap\BlogBundle\Entity\Post;
use Icap\BlogBundle\Manager\CommentManager;
use Icap\BlogBundle\Serializer\CommentSerializer;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

/**
 *
 * @todo use CRUD
 */
#[Route(path: 'blog/{blogId}/comments', options: ['expose' => true])]
class CommentController
{
    use PermissionCheckerTrait;

    /**
     * postController constructor.
     */
    public function __construct(
        private readonly CommentSerializer $commentSerializer,
        private readonly CommentManager $commentManager,
        AuthorizationCheckerInterface $authorization
    ) {
        $this->authorization = $authorization;
    }

    /**
     * Get post comments.
     *
     *
     * @return JsonResponse
     */
    #[Route(path: '/{postId}', name: 'apiv2_blog_comment_list', methods: ['GET'])]
    public function listAction(Request $request, #[MapEntity(mapping: ['blogId' => 'uuid'])] Blog $blog, #[MapEntity(class: 'Icap\BlogBundle\Entity\Post', mapping: ['postId' => 'uuid'])]
    Post $post, #[CurrentUser] ?User $user = null)
    {
        $this->checkPermission('OPEN', $blog->getResourceNode(), [], true);

        $userId = null !== $user ? $user->getId() : null;
        // if no edit rights, list only published comments and current user ones
        $canEdit = $this->authorization->isGranted('EDIT', $blog)
            || $this->authorization->isGranted('MODERATE', $blog);

        $parameters = $request->query->all();
        $comments = $this->commentManager->getComments(
            $blog->getId(),
            $post->getId(),
            $userId,
            $parameters,
            !$canEdit);

        return new JsonResponse($comments);
    }

    /**
     * Get reported comments posts.
     *
     *
     * @return JsonResponse
     */
    #[Route(path: '/moderation/reported', name: 'apiv2_blog_comment_reported', methods: ['GET'])]
    public function listCommentReportedAction(Request $request, #[MapEntity(mapping: ['blogId' => 'uuid'])] Blog $blog)
    {
        $this->checkPermission('OPEN', $blog->getResourceNode(), [], true);
        if ($this->checkPermission('MODERATE', $blog->getResourceNode())
            || $this->checkPermission('EDIT', $blog->getResourceNode())) {
            $parameters = $request->query->all();
            $posts = $this->commentManager->getReportedComments(
                $blog->getId(),
                $parameters);

            return new JsonResponse($posts);
        } else {
            throw new AccessDeniedException();
        }
    }

    /**
     * Get unpublished comments posts.
     *
     *
     * @return JsonResponse
     */
    #[Route(path: '/moderation/unpublished', name: 'apiv2_blog_comment_unpublished', methods: ['GET'])]
    public function listCommentUnpublishedAction(Request $request, #[MapEntity(mapping: ['blogId' => 'uuid'])] Blog $blog)
    {
        $this->checkPermission('OPEN', $blog->getResourceNode(), [], true);
        if ($this->checkPermission('MODERATE', $blog->getResourceNode())
            || $this->checkPermission('EDIT', $blog->getResourceNode())) {
            $parameters = $request->query->all();
            $posts = $this->commentManager->getUnpublishedComments(
                $blog->getId(),
                $parameters);

            return new JsonResponse($posts);
        } else {
            throw new AccessDeniedException();
        }
    }

    /**
     * Create a post comment.
     *
     *
     * @return JsonResponse
     */
    #[Route(path: '/{postId}/new', name: 'apiv2_blog_comment_new', methods: ['POST'])]
    public function createCommentAction(Request $request, #[MapEntity(mapping: ['blogId' => 'uuid'])] Blog $blog, #[MapEntity(class: 'Icap\BlogBundle\Entity\Post', mapping: ['postId' => 'uuid'])]
    Post $post, #[CurrentUser] ?User $user = null)
    {
        $this->checkPermission('OPEN', $blog->getResourceNode(), [], true);
        if ($blog->isCommentsAuthorized() && ($blog->isAuthorizeAnonymousComment() || null !== $user)) {
            $data = [];
            $data['message'] = $this->decodeRequest($request)['comment'];
            $forcePublication = $this->authorization->isGranted('EDIT', $blog)
                || $this->authorization->isGranted('MODERATE', $blog);
            $comment = $this->commentManager->createComment($blog, $post, $this->commentSerializer->deserialize($data, null, $user), $forcePublication);

            return new JsonResponse($this->commentSerializer->serialize($comment));
        } else {
            throw new AccessDeniedException();
        }
    }

    /**
     * Update post comment.
     *
     *
     * @return JsonResponse
     */
    #[Route(path: '/{commentId}/update', name: 'apiv2_blog_comment_update', methods: ['PUT'])]
    public function updateCommentAction(Request $request, #[MapEntity(mapping: ['blogId' => 'uuid'])] Blog $blog, #[MapEntity(class: 'Icap\BlogBundle\Entity\Comment', mapping: ['commentId' => 'uuid'])]
    Comment $comment, #[CurrentUser] ?User $user = null)
    {
        $this->checkPermission('OPEN', $blog->getResourceNode(), [], true);
        // original author or admin can edit, anon cant edit
        if ($blog->isCommentsAuthorized() && $this->isLoggedIn($user)) {
            if ($user !== $comment->getAuthor()) {
                $this->checkPermission('EDIT', $blog->getResourceNode(), [], true);
            }
            $data = $this->decodeRequest($request)['comment'];
            $comment = $this->commentManager->updateComment($blog, $comment, $data);

            return new JsonResponse($this->commentSerializer->serialize($comment));
        } else {
            throw new AccessDeniedException();
        }
    }

    /**
     * Publish post comment.
     *
     *
     * @return JsonResponse
     */
    #[Route(path: '/{commentId}/publish', name: 'apiv2_blog_comment_publish', methods: ['PUT'])]
    public function publishCommentAction(#[MapEntity(mapping: ['blogId' => 'uuid'])] Blog $blog, #[MapEntity(class: 'Icap\BlogBundle\Entity\Comment', mapping: ['commentId' => 'uuid'])]
    Comment $comment)
    {
        $this->checkPermission('EDIT', $blog->getResourceNode(), [], true);
        $comment = $this->commentManager->publishComment($blog, $comment);

        return new JsonResponse($this->commentSerializer->serialize($comment));
    }

    /**
     * Unpublish post comment.
     *
     *
     * @return JsonResponse
     */
    #[Route(path: '/{commentId}/unpublish', name: 'apiv2_blog_comment_unpublish', methods: ['PUT'])]
    public function unpublishCommentAction(#[MapEntity(mapping: ['blogId' => 'uuid'])] Blog $blog, #[MapEntity(class: 'Icap\BlogBundle\Entity\Comment', mapping: ['commentId' => 'uuid'])]
    Comment $comment)
    {
        $this->checkPermission('EDIT', $blog->getResourceNode(), [], true);
        $comment = $this->commentManager->unpublishComment($blog, $comment);

        return new JsonResponse($this->commentSerializer->serialize($comment));
    }

    /**
     * Report post comment.
     *
     *
     * @return JsonResponse
     */
    #[Route(path: '/{commentId}/report', name: 'apiv2_blog_comment_report', methods: ['PUT'])]
    public function reportCommentAction(#[MapEntity(mapping: ['blogId' => 'uuid'])] Blog $blog, #[MapEntity(class: 'Icap\BlogBundle\Entity\Comment', mapping: ['commentId' => 'uuid'])]
    Comment $comment)
    {
        $comment = $this->commentManager->reportComment($blog, $comment);

        return new JsonResponse($this->commentSerializer->serialize($comment));
    }

    /**
     * Delete post comment.
     *
     *
     * @return JsonResponse
     */
    #[Route(path: '/{commentId}/delete', name: 'apiv2_blog_comment_delete', methods: ['DELETE'])]
    public function deleteCommentAction(#[MapEntity(mapping: ['blogId' => 'uuid'])] Blog $blog, #[MapEntity(class: 'Icap\BlogBundle\Entity\Comment', mapping: ['commentId' => 'uuid'])]
    Comment $comment, #[CurrentUser] ?User $user)
    {
        // original author or admin can edit, anon cant edit
        if ($blog->isCommentsAuthorized() && $this->isLoggedIn($user)) {
            if ($user === $comment->getAuthor()
                || $this->checkPermission('EDIT', $blog->getResourceNode())
                || $this->checkPermission('MODERATE', $blog->getResourceNode())) {
                $commentId = $this->commentManager->deleteComment($blog, $comment);

                return new JsonResponse($commentId);
            } else {
                throw new AccessDeniedException();
            }
        } else {
            throw new AccessDeniedException();
        }
    }

    /**
     * Is the user logged in or not ?
     *
     * @return bool
     */
    private function isLoggedIn(?User $user)
    {
        return null === $user ? false : true;
    }

    /**
     * Gets and Deserializes JSON data from Request.
     *
     * @return mixed $data
     *
     * @throws InvalidDataException
     */
    protected function decodeRequest(Request $request)
    {
        $decodedRequest = json_decode($request->getContent(), true);

        if (null === $decodedRequest) {
            throw new InvalidDataException('Invalid request content sent.', []);
        }

        return $decodedRequest;
    }
}
