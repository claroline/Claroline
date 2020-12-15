<?php

namespace Icap\BlogBundle\Controller\Resource;

use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Claroline\CoreBundle\Security\PermissionCheckerTrait;
use Icap\BlogBundle\Entity\Blog;
use Icap\BlogBundle\Manager\BlogManager;
use Icap\BlogBundle\Manager\PostManager;
use Icap\BlogBundle\Serializer\BlogOptionsSerializer;
use Icap\BlogBundle\Serializer\BlogSerializer;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Twig\Environment;

/**
 * @Route("/blog", options={"expose"=true})
 */
class BlogController
{
    use PermissionCheckerTrait;

    private $blogSerializer;
    private $blogOptionsSerializer;
    private $blogManager;
    private $postManager;
    private $router;
    private $configHandler;
    private $tokenStorage;

    /**
     * BlogController constructor.
     */
    public function __construct(
        BlogSerializer $blogSerializer,
        BlogOptionsSerializer $blogOptionsSerializer,
        BlogManager $blogManager,
        PostManager $postManager,
        UrlGeneratorInterface $router,
        PlatformConfigurationHandler $configHandler,
        TokenStorageInterface $tokenStorage,
        Environment $templating,
        AuthorizationCheckerInterface $authorization
      ) {
        $this->blogSerializer = $blogSerializer;
        $this->blogOptionsSerializer = $blogOptionsSerializer;
        $this->blogManager = $blogManager;
        $this->postManager = $postManager;
        $this->router = $router;
        $this->configHandler = $configHandler;
        $this->tokenStorage = $tokenStorage;
        $this->templating = $templating;
        $this->authorization = $authorization;
    }

    /**
     * @Route("/rss/{blogId}", name="icap_blog_rss")
     */
    public function rssAction($blogId)
    {
        //for backwards compatibility with older url using id and not uuid
        $blog = $this->blogManager->getBlogByIdOrUuid($blogId);
        $node = $blog->getResourceNode();
        $workspace = $node->getWorkspace();
        $this->checkPermission('OPEN', $node, [], true);

        if (is_null($blog)) {
            throw new NotFoundHttpException();
        }
        $feed = [
            'title' => $blog->getResourceNode()->getName(),
            'description' => $blog->getInfos(),
            'siteUrl' => $this->router->generate('claro_index', [], UrlGeneratorInterface::ABSOLUTE_URL).'#/desktop/workspaces/open/'.$workspace->getSlug().'/resources/'.$node->getSlug(),
            'feedUrl' => $this->router->generate('icap_blog_rss', ['blogId' => $blog->getId()], UrlGeneratorInterface::ABSOLUTE_URL),
            'lang' => $this->configHandler->getParameter('locale_language'),
        ];

        /** @var \Icap\BlogBundle\Entity\Post[] $posts */
        $posts = $this->postManager->getPosts(
            $blog->getId(),
            [],
            $this->checkPermission('ADMINISTRATE', $blog->getResourceNode())
            || $this->checkPermission('EDIT', $blog->getResourceNode())
            || $this->checkPermission('MODERATE', $blog->getResourceNode())
                ? PostManager::GET_ALL_POSTS
                : PostManager::GET_PUBLISHED_POSTS,
            false
        );

        $items = [];
        if (isset($posts)) {
            foreach ($posts['data'] as $post) {
                $items[] = [
                    'title' => $post['title'],
                    'url' => $this->router->generate('apiv2_blog_post_get', ['blogId' => $blog->getId(), 'postId' => $post['slug']], UrlGeneratorInterface::ABSOLUTE_URL),
                    'date' => date('d/m/Y h:i:s', strtotime($post['publicationDate'])),
                    'intro' => $post['content'],
                    'author' => $post['authorName'],
                ];
            }
        }

        return new Response($this->templating->render('@IcapBlog/blog/rss/rss.html.twig', [
            'feed' => $feed,
            'items' => $items,
        ]), 200, [
            'Content-Type' => 'application/rss+xml',
            'charset' => 'utf-8',
        ]);
    }

    /**
     * @Route("/pdf/{blogId}", name="icap_blog_pdf")
     */
    public function viewPdfAction($blogId)
    {
        //for backwards compatibility with older url using id and not uuid
        $blog = $this->blogManager->getBlogByIdOrUuid($blogId);
        $this->checkPermission('OPEN', $blog->getResourceNode(), [], true);

        /** @var \Icap\BlogBundle\Entity\Post[] $posts */
        $posts = $this->postManager->getPosts(
            $blog->getId(),
            [],
            $this->checkPermission('ADMINISTRATE', $blog->getResourceNode())
            || $this->checkPermission('EDIT', $blog->getResourceNode())
            || $this->checkPermission('MODERATE', $blog->getResourceNode())
                ? PostManager::GET_ALL_POSTS
                : PostManager::GET_PUBLISHED_POSTS,
            false);
        $items = [];
        if (isset($posts)) {
            foreach ($posts['data'] as $post) {
                $items[] = [
                    'title' => $post['title'],
                    'content' => $post['content'],
                    'publicationDate' => $post['publicationDate'] ? $post['publicationDate'] : $post['creationDate'],
                    'author' => $post['authorName'],
                ];
            }
        }

        $content = $this->templating->render(
            '@IcapBlog/blog/pdf/view.pdf.twig',
            ['_resource' => $blog, 'posts' => $items]
        );

        return new JsonResponse([
          'name' => $blog->getResourceNode()->getSlug(),
          'content' => $content,
        ]);
    }
}
