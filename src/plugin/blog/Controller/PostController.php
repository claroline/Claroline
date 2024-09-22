<?php

namespace Icap\BlogBundle\Controller;

use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Security\PermissionCheckerTrait;
use Icap\BlogBundle\Entity\Blog;
use Icap\BlogBundle\Entity\Post;
use Icap\BlogBundle\Manager\PostManager;
use Icap\BlogBundle\Serializer\PostSerializer;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

#[Route(path: 'blog/{blogId}/posts', options: ['expose' => true])]
class PostController
{
    use PermissionCheckerTrait;

    public function __construct(
        private readonly PostSerializer $postSerializer,
        private readonly PostManager $postManager,
        AuthorizationCheckerInterface $authorization
    ) {
        $this->authorization = $authorization;
    }

    /**
     * Get the name of the managed entity.
     *
     * @return string
     */
    public function getName(): string
    {
        return 'post';
    }

    /**
     * Get unpublished blog posts.
     */
    #[Route(path: '/moderation', name: 'apiv2_blog_post_list_unpublished', methods: ['GET'])]
    public function listUnpublishedAction(Request $request, #[MapEntity(mapping: ['blogId' => 'uuid'])] Blog $blog): JsonResponse
    {
        $this->checkPermission('OPEN', $blog->getResourceNode(), [], true);
        if ($this->checkPermission('MODERATE', $blog->getResourceNode())
            || $this->checkPermission('EDIT', $blog->getResourceNode())) {
            $parameters = $request->query->all();

            // if no edit rights, list only published posts
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
     */
    #[Route(path: '', name: 'apiv2_blog_post_list', methods: ['GET'])]
    public function listAction(Request $request, #[MapEntity(mapping: ['blogId' => 'uuid'])] Blog $blog): JsonResponse
    {
        $this->checkPermission('OPEN', $blog->getResourceNode(), [], true);

        $parameters = $request->query->all();

        // if no edit rights, list only published posts
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
     */
    #[Route(path: '/{postId}', name: 'apiv2_blog_post_get', methods: ['GET'])]
    public function getAction(
        #[MapEntity(mapping: ['blogId' => 'uuid'])]
        Blog $blog,
        $postId
    ): JsonResponse {
        $this->checkPermission('OPEN', $blog->getResourceNode(), [], true);
        $post = $this->postManager->getPostByIdOrSlug($blog, $postId);

        if (is_null($post)) {
            throw new NotFoundHttpException('Post not found');
        }

        $this->postManager->updatePostViewCount($post);

        return new JsonResponse($this->postSerializer->serialize($post));
    }

    /**
     * Create blog post.
     */
    #[Route(path: '/new', name: 'apiv2_blog_post_new', methods: ['POST', 'PUT'])]
    public function createPostAction(Request $request, #[MapEntity(mapping: ['blogId' => 'uuid'])] Blog $blog, #[CurrentUser] ?User $user): JsonResponse
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
     */
    #[Route(path: '/update/{postId}', name: 'apiv2_blog_post_update', methods: ['PUT'])]
    public function updatePostAction(Request $request, #[MapEntity(mapping: ['blogId' => 'uuid'])] Blog $blog, #[MapEntity(class: 'Icap\BlogBundle\Entity\Post', mapping: ['postId' => 'uuid'])]
    Post $post, #[CurrentUser] ?User $user): JsonResponse
    {
        $this->checkPermission('EDIT', $blog->getResourceNode(), [], true);

        $data = json_decode($request->getContent(), true);
        $post = $this->postManager->updatePost($blog, $post, $this->postSerializer->deserialize($data, $post), $user);

        return new JsonResponse($this->postSerializer->serialize($post));
    }

    /**
     * Delete blog post.
     */
    #[Route(path: '/delete/{postId}', name: 'apiv2_blog_post_delete', methods: ['DELETE'])]
    public function deletePostAction(#[MapEntity(mapping: ['blogId' => 'uuid'])] Blog $blog, #[MapEntity(class: 'Icap\BlogBundle\Entity\Post', mapping: ['postId' => 'uuid'])]
    Post $post, #[CurrentUser] ?User $user): JsonResponse
    {
        $this->checkPermission('EDIT', $blog->getResourceNode(), [], true);
        $this->postManager->deletePost($blog, $post, $user);

        return new JsonResponse($post->getId());
    }

    /**
     * Switch post publication state.
     */
    #[Route(path: '/publish/{postId}', name: 'apiv2_blog_post_publish', methods: ['PUT'])]
    public function publishPostAction(#[MapEntity(mapping: ['blogId' => 'uuid'])] Blog $blog, #[MapEntity(class: 'Icap\BlogBundle\Entity\Post', mapping: ['postId' => 'uuid'])]
    Post $post): JsonResponse
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
     */
    #[Route(path: '/pin/{postId}', name: 'apiv2_blog_post_pin', methods: ['PUT'])]
    public function pinPostAction(#[MapEntity(mapping: ['blogId' => 'uuid'])] Blog $blog, #[MapEntity(class: 'Icap\BlogBundle\Entity\Post', mapping: ['postId' => 'uuid'])]
    Post $post): JsonResponse
    {
        $this->checkPermission('EDIT', $blog->getResourceNode(), [], true);

        $this->postManager->switchPinState($post);

        return new JsonResponse($this->postSerializer->serialize($post));
    }

    /**
     * Get all authors for a given blog.
     */
    #[Route(path: '/authors/get', name: 'apiv2_blog_post_authors', methods: ['GET'])]
    public function getBlogAuthorsAction(#[MapEntity(mapping: ['blogId' => 'uuid'])] Blog $blog): JsonResponse
    {
        $this->checkPermission('OPEN', $blog->getResourceNode(), [], true);

        return new JsonResponse($this->postManager->getAuthors($blog));
    }
}
