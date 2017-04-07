<?php

namespace Icap\BlogBundle\Controller;

use Claroline\CoreBundle\Entity\User;
use Icap\BlogBundle\Entity\Blog;
use Icap\BlogBundle\Entity\Post;
use Icap\BlogBundle\Entity\Tag;
use JMS\Serializer\SerializationContext;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Exception\NotValidCurrentPageException;
use Pagerfanta\Pagerfanta;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class BlogController extends BaseController
{
    /**
     * @Route("/{blogId}/{page}", name="icap_blog_view", requirements={"blogId" = "\d+", "page" = "\d+"}, defaults={"page" = 1})
     * @Route("/{blogId}/{filter}/{page}", name="icap_blog_view_filter", requirements={"blogId" = "\d+", "page" = "\d+"}, defaults={"page" = 1})
     * @ParamConverter("blog", class="IcapBlogBundle:Blog", options={"id" = "blogId"})
     * @Template("@IcapBlog/layout.angular.twig")
     */
    public function viewAction(Request $request, Blog $blog, $page, $filter = null)
    {
        $this->checkAccess('OPEN', $blog);

        $user = $this->get('security.token_storage')->getToken()->getUser();
        $userId = is_a($user, 'Claroline\\CoreBundle\\Entity\\User') ? $user->getId() : null;

        $search = $request->get('search');
        if (null !== $search && '' !== $search) {
            return $this->redirect($this->generateUrl('icap_blog_view_search', ['blogId' => $blog->getId(), 'search' => $search]));
        }

        /** @var \Icap\BlogBundle\Repository\PostRepository $postRepository */
        $postRepository = $this->get('icap.blog.post_repository');

        $tag = null;
        $author = null;
        $date = null;

        if (null !== $filter) {
            $tag = $this->get('icap.blog.tag_repository')->findOneBySlug($filter);

            if (null === $tag) {
                $author = $this->getDoctrine()->getRepository('ClarolineCoreBundle:User')->findOneByUsername($filter);

                if (null === $author) {
                    $date = $filter;
                }
            }
        }

        /** @var \Doctrine\ORM\QueryBuilder $query */
        $query = $postRepository
            ->createQueryBuilder('post')
            ->select(['post', 'author'])
            ->join('post.author', 'author')
            ->andWhere('post.blog = :blogId')
        ;

        if (!$this->isUserGranted('EDIT', $blog)) {
            $query = $postRepository->filterByPublishPost($query);
        }

        $criterias = [
            'tag' => $tag,
            'author' => $author,
            'date' => $date,
            'blogId' => $blog->getId(),
        ];

        $query = $postRepository->createCriteriaQueryBuilder($criterias, $query);

        $adapter = new DoctrineORMAdapter($query, false);
        $pager = new PagerFanta($adapter);

        $pager->setMaxPerPage($blog->getOptions()->getPostPerPage());

        try {
            $pager->setCurrentPage($page);
        } catch (NotValidCurrentPageException $exception) {
            throw new NotFoundHttpException();
        }

        $serializer = $this->get('jms_serializer');

        return [
            '_resource' => $blog,
            'bannerForm' => $this->getBannerForm($blog->getOptions()),
            'user' => $userId,
            'pager' => $pager,
            'tag' => $tag,
            'author' => $author,
            'date' => $date,
            'orderPanels' => $this->orderPanels($blog),
            'archiveData' => $this->getArchiveDatas($blog),
            'options' => $serializer->serialize($blog->getOptions(), 'json'),
            'posts' => $serializer->serialize($blog->getPosts(), 'json', SerializationContext::create()->setGroups(['blog_list', 'api_user_min'])),
        ];
    }

    /**
     * @Route("/pdf/{blogId}", name="icap_blog_view_pdf", requirements={"blogId" = "\d+"})
     * @ParamConverter("blog", class="IcapBlogBundle:Blog", options={"id" = "blogId"})     *
     */
    public function viewPdfAction(Blog $blog)
    {
        $this->checkAccess('OPEN', $blog);

        /** @var \Icap\BlogBundle\Repository\PostRepository $postRepository */
        $postRepository = $this->get('icap.blog.post_repository');

        $posts = $postRepository->findAllPublicByBlog($blog);

        $content = $this->renderView('IcapBlogBundle::view.pdf.twig',
            [
                '_resource' => $blog,
                'posts' => $posts,
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
                'Content-Disposition' => 'inline; filename="'.$blog->getResourceNode()->getName(),
            ]
        );
    }

    /**
     * @Route("/rss/{blogId}", name="icap_blog_rss", requirements={"blogId" = "\d+"})
     * @ParamConverter("blog", class="IcapBlogBundle:Blog", options={"id" = "blogId"})
     */
    public function rssAction(Blog $blog)
    {
        $baseUrl = $this->get('request')->getSchemeAndHttpHost();

        $feed = [
            'title' => $blog->getResourceNode()->getName(),
            'description' => $blog->getInfos(),
            'siteUrl' => $baseUrl.$this->generateUrl('icap_blog_view', ['blogId' => $blog->getId()]),
            'feedUrl' => $baseUrl.$this->generateUrl('icap_blog_rss', ['blogId' => $blog->getId()]),
            'lang' => $this->get('claroline.config.platform_config_handler')->getParameter('locale_language'),
        ];

        /** @var \Icap\BlogBundle\Entity\Post[] $posts */
        $posts = $this->getDoctrine()->getRepository('IcapBlogBundle:Post')->findRssDatas($blog);

        $items = [];
        foreach ($posts as $post) {
            $items[] = [
                'title' => $post->getTitle(),
                'url' => $baseUrl.$this->generateUrl('icap_blog_post_view', ['blogId' => $blog->getId(), 'postSlug' => $post->getSlug()]),
                'date' => $post->getPublicationDate()->format('d/m/Y h:i:s'),
                'intro' => $post->getContent(),
                'author' => $post->getAuthor()->getFirstName() - $post->getAuthor()->getLastName(),
            ];
        }

        return new Response($this->renderView('IcapBlogBundle::rss.html.twig', [
            'feed' => $feed,
            'items' => $items,
        ]), 200, [
            'Content-Type' => 'application/rss+xml',
            'charset' => 'utf-8',
        ]);
    }

    /**
     * @Route("/calendar/{blogId}", name="icap_blog_calendar_datas", requirements={"blogId" = "\d+"})
     * @ParamConverter("blog", class="IcapBlogBundle:Blog", options={"id" = "blogId"})
     */
    public function calendarDatas(Request $request, Blog $blog)
    {
        $requestParameters = $request->query->all();
        $startDate = $requestParameters['start'];
        $endDate = $requestParameters['end'];
        $calendarDatas = [];
        $calendarDatasTemp = [];

        /** @var \Icap\BlogBundle\Repository\PostRepository $postRepository */
        $postRepository = $this->getDoctrine()->getManager()->getRepository('IcapBlogBundle:Post');

        $posts = $postRepository->findCalendarDatas($blog, $startDate, $endDate);

        foreach ($posts as $post) {
            $publicationDate = $post->getPublicationDate()->format('Y-m-d');

            if (!isset($calendarDatasTemp[$publicationDate])) {
                $calendarDatasTemp[$publicationDate] = [
                    'id' => '12',
                    'start' => $publicationDate,
                    'title' => '1',
                    'angularParams' => $post->getPublicationDate()->format('Y/m/d'),
                ];
            } else {
                $title = intval($calendarDatasTemp[$publicationDate]['title']);
                ++$title;
                $calendarDatasTemp[$publicationDate]['title'] = "$title";
            }
        }
        foreach ($calendarDatasTemp as $calendarData) {
            $calendarDatas[] = $calendarData;
        }

        $response = new JsonResponse($calendarDatas);

        return $response;
    }

    /**
     * @Route("/configure/{blogId}", name="icap_blog_configure", requirements={"blogId" = "\d+"})
     * @ParamConverter("blog", class="IcapBlogBundle:Blog", options={"id" = "blogId"})
     * @Template()
     */
    public function configureAction(Blog $blog)
    {
        $this->checkAccess('ADMINISTRATE', $blog);

        return $this->redirect($this->generateUrl('icap_blog_view', ['blogId' => $blog->getId()]).'#/configure');
    }
}
