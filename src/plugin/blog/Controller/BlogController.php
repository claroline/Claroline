<?php

namespace Icap\BlogBundle\Controller;

use Claroline\AppBundle\API\FinderProvider;
use Claroline\CoreBundle\Security\PermissionCheckerTrait;
use Icap\BlogBundle\Entity\Blog;
use Icap\BlogBundle\Manager\BlogManager;
use Icap\BlogBundle\Manager\PostManager;
use Icap\BlogBundle\Serializer\BlogOptionsSerializer;
use Icap\BlogBundle\Serializer\BlogSerializer;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Twig\Environment;

/**
 * @Route("blog/{blogId}", options={"expose"=true})
 * @EXT\ParamConverter("blog", class="IcapBlogBundle:Blog", options={"mapping": {"blogId": "uuid"}})
 */
class BlogController
{
    use PermissionCheckerTrait;

    /** @var FinderProvider */
    private $finder;
    /** @var UrlGeneratorInterface */
    private $router;
    /** @var Environment */
    private $templating;
    /** @var BlogManager */
    private $blogManager;
    /** @var PostManager */
    private $postManager;
    /** @var BlogSerializer */
    private $blogSerializer;
    /** @var BlogOptionsSerializer */
    private $blogOptionsSerializer;

    public function __construct(
        AuthorizationCheckerInterface $authorization,
        UrlGeneratorInterface $router,
        Environment $templating,
        FinderProvider $finder,
        BlogManager $blogManager,
        PostManager $postManager,
        BlogSerializer $blogSerializer,
        BlogOptionsSerializer $blogOptionsSerializer
      ) {
        $this->authorization = $authorization;
        $this->router = $router;
        $this->templating = $templating;
        $this->finder = $finder;
        $this->blogManager = $blogManager;
        $this->postManager = $postManager;
        $this->blogSerializer = $blogSerializer;
        $this->blogOptionsSerializer = $blogOptionsSerializer;
    }

    /**
     * Get the name of the managed entity.
     *
     * @return string
     */
    public function getName()
    {
        return 'blog';
    }

    /**
     * Get blog options.
     *
     * @Route("/options", name="apiv2_blog_options", methods={"GET"})
     */
    public function getOptionsAction(Blog $blog): JsonResponse
    {
        $this->checkPermission('EDIT', $blog->getResourceNode(), [], true);

        return new JsonResponse($this->blogOptionsSerializer->serialize($blog, $blog->getOptions(), $this->blogManager->getPanelInfos()));
    }

    /**
     * Update blog options.
     *
     * @Route("/options", name="apiv2_blog_options_update", methods={"PUT"})
     */
    public function updateOptionsAction(Request $request, Blog $blog): JsonResponse
    {
        $this->checkPermission('EDIT', $blog->getResourceNode(), [], true);
        $data = json_decode($request->getContent(), true);
        $this->blogManager->updateOptions($blog, $this->blogOptionsSerializer->deserialize($data), $data['infos']);

        return new JsonResponse($this->blogOptionsSerializer->serialize($blog, $blog->getOptions()));
    }

    /**
     * Get tag cloud, tags used in blog posts.
     *
     * @Route("/tags", name="apiv2_blog_tags", methods={"GET"})
     */
    public function getTagsAction(Blog $blog): JsonResponse
    {
        $this->checkPermission('OPEN', $blog->getResourceNode(), [], true);

        $parameters['limit'] = -1;
        $posts = $this->postManager->getPosts(
            $blog->getId(),
            $parameters,
            $this->checkPermission('ADMINISTRATE', $blog->getResourceNode())
            || $this->checkPermission('EDIT', $blog->getResourceNode())
            || $this->checkPermission('MODERATE', $blog->getResourceNode())
                ? PostManager::GET_ALL_POSTS
                : PostManager::GET_PUBLISHED_POSTS,
            true);

        $postsData = [];
        if (!empty($posts)) {
            $postsData = $posts['data'];
        }

        return new JsonResponse($this->blogManager->getTags($blog, $postsData));
    }


    /**
     * @Route("/rss", name="icap_blog_rss", methods={"GET"})
     */
    public function rssAction(Blog $blog, Request $request): Response
    {
        $node = $blog->getResourceNode();
        $workspace = $node->getWorkspace();

        $this->checkPermission('OPEN', $node, [], true);

        $feed = [
            'title' => $blog->getResourceNode()->getName(),
            'description' => $blog->getInfos(),
            'siteUrl' => $this->router->generate('claro_index', [], UrlGeneratorInterface::ABSOLUTE_URL).'#/desktop/workspaces/open/'.$workspace->getSlug().'/resources/'.$node->getSlug(),
            'feedUrl' => $this->router->generate('icap_blog_rss', ['blogId' => $blog->getId()], UrlGeneratorInterface::ABSOLUTE_URL),
            'lang' => $request->getLocale(),
        ];

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
     * @Route("/pdf", name="icap_blog_pdf", methods={"GET"})
     */
    public function viewPdfAction(Blog $blog): JsonResponse
    {
        $this->checkPermission('OPEN', $blog->getResourceNode(), [], true);

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
