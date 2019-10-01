<?php

namespace Icap\BlogBundle\Controller\Resource;

use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Claroline\CoreBundle\Security\PermissionCheckerTrait;
use Icap\BlogBundle\Entity\Blog;
use Icap\BlogBundle\Manager\BlogManager;
use Icap\BlogBundle\Manager\PostManager;
use Icap\BlogBundle\Serializer\BlogOptionsSerializer;
use Icap\BlogBundle\Serializer\BlogSerializer;
use JMS\DiExtraBundle\Annotation as DI;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * @EXT\Route("/blog", options={"expose"=true})
 */
class BlogController extends Controller
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
     *
     * @DI\InjectParams({
     *     "blogSerializer"        = @DI\Inject("Icap\BlogBundle\Serializer\BlogSerializer"),
     *     "blogOptionsSerializer" = @DI\Inject("Icap\BlogBundle\Serializer\BlogOptionsSerializer"),
     *     "blogManager"           = @DI\Inject("Icap\BlogBundle\Manager\BlogManager"),
     *     "postManager"           = @DI\Inject("Icap\BlogBundle\Manager\PostManager"),
     *     "router"                = @DI\Inject("router"),
     *     "configHandler"         = @DI\Inject("claroline.config.platform_config_handler"),
     *     "tokenStorage"          = @DI\Inject("security.token_storage")
     * })
     *
     * @param BlogSerializer               $blogSerializer
     * @param BlogOptionsSerializer        $blogOptionsSerializer
     * @param BlogManager                  $blogManager
     * @param PostManager                  $postManager
     * @param UrlGeneratorInterface        $router
     * @param PlatformConfigurationHandler $configHandler
     * @param TokenStorageInterface        $tokenStorage
     */
    public function __construct(
        BlogSerializer $blogSerializer,
        BlogOptionsSerializer $blogOptionsSerializer,
        BlogManager $blogManager,
        PostManager $postManager,
        UrlGeneratorInterface $router,
        PlatformConfigurationHandler $configHandler,
        TokenStorageInterface $tokenStorage
      ) {
        $this->blogSerializer = $blogSerializer;
        $this->blogOptionsSerializer = $blogOptionsSerializer;
        $this->blogManager = $blogManager;
        $this->postManager = $postManager;
        $this->router = $router;
        $this->configHandler = $configHandler;
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * @EXT\Route("/rss/{blogId}", name="icap_blog_rss")
     */
    public function rssAction($blogId)
    {
        //for backwards compatibility with older url using id and not uuid
        $blog = $this->blogManager->getBlogByIdOrUuid($blogId);
        $this->checkPermission('OPEN', $blog->getResourceNode(), [], true);

        if (is_null($blog)) {
            throw new NotFoundHttpException();
        }
        $feed = [
            'title' => $blog->getResourceNode()->getName(),
            'description' => $blog->getInfos(),
            'siteUrl' => $this->generateUrl('icap_blog_open', ['blogId' => $blog->getId()], UrlGeneratorInterface::ABSOLUTE_URL),
            'feedUrl' => $this->generateUrl('icap_blog_rss', ['blogId' => $blog->getId()], UrlGeneratorInterface::ABSOLUTE_URL),
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
                    'url' => $this->generateUrl('apiv2_blog_post_get', ['blogId' => $blog->getId(), 'postId' => $post['slug']], UrlGeneratorInterface::ABSOLUTE_URL),
                    'date' => date('d/m/Y h:i:s', strtotime($post['publicationDate'])),
                    'intro' => $post['content'],
                    'author' => $post['authorName'],
                ];
            }
        }

        return new Response($this->renderView('IcapBlogBundle:blog/rss:rss.html.twig', [
            'feed' => $feed,
            'items' => $items,
        ]), 200, [
            'Content-Type' => 'application/rss+xml',
            'charset' => 'utf-8',
        ]);
    }

    /**
     * @EXT\Route("/pdf/{blogId}", name="icap_blog_pdf")
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

        $content = $this->renderView('IcapBlogBundle:blog/pdf:view.pdf.twig',
            [
                '_resource' => $blog,
                'posts' => $items,
            ]
            );

        return new Response(
            $this->get('knp_snappy.pdf')->getOutputFromHtml(
                $content,
                [
                    'outline' => true,
                    'footer-right' => '[page]/[toPage]',
                    'footer-spacing' => 3,
                    'footer-font-size' => 8,
                ],
                true
                ),
            200,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="'.$blog->getResourceNode()->getName().'.pdf"',
            ]
            );
    }
}
